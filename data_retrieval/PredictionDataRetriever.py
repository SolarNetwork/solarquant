from urllib2 import Request, urlopen
import xml.etree.ElementTree as xmlParse
import traceback as tb
import datetime as dt
import os
import mysql.connector
import datetime
import json
import logging
# gets the directory of this file
directory = os.path.dirname(__file__)

weatherFolder = os.path.join(directory, "weather/")
weatherFile = weatherFolder + '/weather_future.xml'
logger = logging.getLogger('prediction_data_retriever')
# db connection
cnx = mysql.connector.connect(user='solarquant', password='solarquant',
                              host='localhost',
                              database='solarquant')
cursor = cnx.cursor()

PREFIX = "https://data.solarnetwork.net"
chunksFolder = os.path.join(directory, "chunks/")
import calendar
from datetime import timedelta

def utc_to_local(utc_dt):
    # get integer timestamp to avoid precision lost
    timestamp = calendar.timegm(utc_dt.timetuple())
    local_dt = dt.datetime.fromtimestamp(timestamp)
    assert utc_dt.resolution >= timedelta(microseconds=1)
    return local_dt.replace(microsecond=utc_dt.microsecond)

def update_weather():
    # fills in all 30 minute intervals with data.
    def interpolate(data):
        out = []
        for i in range(len(data) - 1):
            diff = data[i + 1][0] - data[i][0]
            mins = divmod(diff.total_seconds(), 60 * 30)

            for j in range(int(mins[0])):
                out.append([data[i][0] + datetime.timedelta(minutes=30 * j)] + data[i][1:])
        return out

    def get_weather_data():
        request = Request("http://api.met.no/weatherapi/locationforecast/1.9/?lat=-36.855401;lon=174.745393")
        try:
            response = urlopen(request)
            data = xmlParse.parse(response)
            data.write(weatherFile)
        except:
            print("Failed")

    date_format = "%Y-%m-%dT%H:%M:%SZ"

    def add_to_database():
        logger.info("Adding new weather to database")
        xml = xmlParse.parse(weatherFile)
        data = []
        for i in xml.iter(tag="time"):
            temp_arr = []
            to_date = dt.datetime.strptime(i.get("to"), date_format)
            from_date = dt.datetime.strptime(i.get("from"), date_format)

            if to_date == from_date:

                temp_arr.append(to_date)
                for j in i:
                    try:
                        # pulls values out of XML and puts into array
                        temp = float(j.find("temperature").get("value"))
                        wind_dir = float(j.find("windDirection").get("deg"))
                        wind_speed = float(j.find("windSpeed").get("mps"))
                        humidity = float(j.find("humidity").get("value"))
                        pressure = float(j.find("pressure").get("value"))
                        cloudy = float(j.find("cloudiness").get("percent"))
                        # concatentates array - adds onto end
                        temp_arr = temp_arr + [temp, wind_dir, wind_speed, humidity, pressure, cloudy]
                    except Exception as e:
                        #tb.print_exc(e)
                        pass
            if len(temp_arr) > 4:
                # if data exists, put into array
                data.append(temp_arr)

        data = interpolate(data)
        # removes old weather, populates with new
        logger.info("deleting old weather prediction data")
        query = "DELETE FROM yr_weather WHERE 1"
        cnx.commit()
        cursor.execute(query)
        logger.info("Adding new weather prediction data")
        query = "INSERT INTO yr_weather VALUES (%s, %s, %s, %s, %s, %s, %s)"
        try:
            cursor.executemany(query, data)
        except:
            pass

        cnx.commit()

    get_weather_data()
    add_to_database()


# replaces all datum between now and 14 days ago - fills in missing
def update_datum(node_id, src_id, end_date=datetime.datetime.utcnow(), start_date=datetime.datetime.utcnow()
                                                                                 - datetime.timedelta(days=14)):
    query = ("DELETE FROM node_datum WHERE NODE_ID = " + node_id + " AND SOURCE_ID = '" + src_id
             + "' AND DATE_CREATED BETWEEN '{}' AND '{}'").format(start_date,end_date)

    cursor.execute(query)
    cnx.commit()

    # cannot download all in one file - needs chunks
    def get_chunk_end_date(current, chunk_end_date, min_interval):
        out = current + datetime.timedelta(minutes=500 * min_interval)
        if out > chunk_end_date:
            return chunk_end_date
        else:
            return out

    # retrieves solar query node data and  puts into database
    def get_node_data():
        chunk_start = start_date

        while chunk_start < end_date:
            chunk_end = get_chunk_end_date(chunk_start, end_date, 20)

            start_string = datetime.datetime.strftime(chunk_start, "%Y-%m-%dT12%%3A00")

            end_string = datetime.datetime.strftime(chunk_end, "%Y-%m-%dT12%%3A00")

            # define api call
            request = Request(PREFIX + "/solarquery/api/v1/pub/datum/"
                                       "list?nodeId=" + node_id + "&aggregation=ThirtyMinute&startDate=" +
                           start_string + "&endDate=" + end_string + "&sourceIds=" + src_id + "&max=5000000")
            try:
                # open API url
                response = urlopen(request)

                data = json.loads(response.read())

                node_query = "INSERT INTO node_datum VALUES (%s, %s, %s, %s)"

                prev_date = datetime.datetime.strptime("1000", "%Y")
                dat = []
                for i in data['data']['results']:
                    try:
                        c_date = datetime.datetime.strptime(i['created'], "%Y-%m-%d %H:%M:%S.%fZ")
                        # makes sure no duplicates - will trigger primary key
                        if (c_date > start_date) & (prev_date < c_date):
                            data_temp = [(node_id, src_id, c_date, i['wattHours'])]
                            dat = dat + data_temp
                            prev_date = c_date

                    except:
                        pass
                try:
                    cursor.executemany(node_query, dat)
                except Exception as e:
                    tb.print_exc(e)
            except:
                print("Failed")

            chunk_start = chunk_end

            cnx.commit()

    get_node_data()


# adds the datum + weather to the prediction input table
def add_prediction_input(node_id, src_id):
    data = []
    start_date = datetime.datetime.utcnow() - datetime.timedelta(days=7)
    end_date = datetime.datetime.utcnow()

    # sorts so that you get select each hour of each day of the week, going backwards a week at a time.
    # ie. you get 2017-08-01T10:00:00, and then 2017-01-01T10:00:00 - goes backwards 7 days at a time, so you
    # get the same time of day on the same day of the week, the week previous.
    query_data = "SELECT DATE_CREATED, WATT_HOURS FROM node_datum WHERE NODE_ID = {} AND SOURCE_ID " \
                 "= \'{}\' AND DATE_CREATED BETWEEN '{}' and '{}'".format(node_id,src_id,start_date,end_date)
    try:
        cursor.execute(query_data)
        data = data + cursor.fetchall()
    except:
        pass

    # removes outdated prediction data
    logger.info("deleting old predictions")
    deletequery = "DELETE FROM prediction_input WHERE NODE_ID = %s AND SOURCE_ID = %s"
    print(node_id, src_id)
    cursor.execute(deletequery, (node_id, src_id))

    cnx.commit()

    # returns the weather at a datetime that is specified in the input
    def get_weather_for_date(date):
        query_weather = "SELECT * FROM yr_weather WHERE PREDICTION_DATE = '{}'".format(date)
        cursor.execute(query_weather)
        data_w = cursor.fetchall()
        return data_w

    # gets the datum for the a single week, the number of weeks previous determined by num_prev

    def get_prev_datum_for_date(date,num_prev):
        date = date - datetime.timedelta(weeks=num_prev)
        query_prev = "SELECT WATT_HOURS FROM node_datum WHERE NODE_ID = {} AND SOURCE_ID = '{}' " \
                     "AND DATE_CREATED = '{}'".format(node_id,src_id,date)
        cursor.execute(query_prev)
        data_prev = cursor.fetchall()
        return data_prev


    # inserts the prediction data into the formatted prediction input table.
    logger.info("Inserting prediction input into table...")
    prediction_input = []
    for i in range(len(data) - 2):
        if (data[i][0] > start_date):
            # if the next item from database is exactly 7 days previous then continue, otherwise it is missing a datum
            # predicting a week ahead.
            prediction_date = data[i][0] + datetime.timedelta(days=7)
            weather_data = get_weather_for_date(prediction_date)
            prev_week = get_prev_datum_for_date(data[i][0],1)
            try:
                prediction_input = prediction_input + [(node_id,
                                                        src_id,
                                                        utc_to_local(prediction_date),
                                                        datetime.datetime.utcnow(),
                                                        data[i][1],
                                                        prev_week[0][0],
                                                        float(weather_data[0][5]),
                                                        float(weather_data[0][4]),
                                                        float(weather_data[0][1]) + 273.15,
                                                        float(weather_data[0][6]),
                                                        float(weather_data[0][3]),
                                                        float(weather_data[0][2]))]
            except Exception as e:
                pass

    query4 = ("INSERT INTO prediction_input "
              "VALUES (%s,%s,%s, %s, %s, %s, %s, %s,%s,%s,%s,%s)")
    try:
        cursor.executemany(query4, prediction_input)
    except Exception as e:
        tb.print_exc(e)
    cnx.commit()
