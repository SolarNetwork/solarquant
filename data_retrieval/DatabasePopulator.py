""" Handles the insertion of datum into the database for training data. Downloads the relevant historical weather
and node datum to train on, fills the raw datum stores, and then fills the formatted training input table
using this data.
"""

import mysql.connector
import os
import json
import datetime
import argparse
import traceback as tb
from DataRetriever import DataRetriever

directory = os.path.dirname(__file__)

argParser = argparse.ArgumentParser()
argParser.add_argument("-r", "--reqid", dest="reqId", help="ID for request",
                       metavar="ID", required=True)

argParser.add_argument("-s", "--startdate", dest="startDate", help="start date",
                       metavar="start")

argParser.add_argument("-e", "--enddate", dest="endDate", help="end date",
                       metavar="end")

args = argParser.parse_args()

cnx = mysql.connector.connect(user='solarquant', password='solarquant',
                              host='localhost',
                              database='solarquant')
cursor = cnx.cursor()

#
#
#
# FOR FUTURE - FILL EMPTY VALUES IN WITH 00000000
#
#
#

# if there is an error with inputting data into database, transition request to error state
def error_state():
    query = ("UPDATE training_requests SET STATUS = 5 "
             "WHERE REQUEST_ID = " + args.reqId)
    print(query)
    cursor.execute(query)
    cnx.commit()
    exit(1)

def log_end_time():
    ctime = datetime.datetime.now()
    query = ("UPDATE training_state_time SET COMPLETION_DATE=%s WHERE REQUEST_ID=%s AND STATE=%s")
    cursor.execute(query, (ctime, args.reqId, 2))
    cnx.commit()


def log_error(messg):
    f = "../logs/data_retrieval.txt"
    filename = os.path.join(directory, f)
    with open(filename, "a") as myfile:
        myfile.write("\nERROR:" + messg)

# weather words are seperated into equivalence classes,
# semi-ordinal weather values are converted into a continuous value between 0 and 1
def get_weather_value(word):
    weather_words = ["Fine", "Drizzle", "Partly cloudy",
                     "Cloudy", "Windy", "Fog",
                     "Few showers", "Showers", "Rain",
                     "Hail", "Thunder"]
    for i in range(len(weather_words)):
        if weather_words[i] == word:
            return (i % 3) / 2



def populate():
    chunks_folder = os.path.join(directory, "chunks/")
    weather_folder = os.path.join(directory, "weather/")

    # clear weather + datum chunk folders of old data
    for the_file in os.listdir(chunks_folder):
        file_path = os.path.join(chunks_folder, the_file)
        try:
            if os.path.isfile(file_path):
                os.unlink(file_path)
        except Exception as E:
            print(E)

    for the_file in os.listdir(weather_folder):
        file_path = os.path.join(weather_folder, the_file)
        try:
            if os.path.isfile(file_path):
                os.unlink(file_path)

        except Exception as E:
            print(E)

    start_date = args.startDate
    end_date = args.endDate

    info_query = "SELECT NODE_ID, SOURCE_ID, START_DATE FROM training_requests WHERE REQUEST_ID = " + args.reqId

    cursor.execute(info_query)
    data = cursor.fetchall()
    node_id = 0
    src_id = ''
    training_start_date = datetime.datetime.fromtimestamp(1)
    for row in data:
        node_id = str(row[0])
        src_id = str(row[1])
        training_start_date = row[2]
    start_date_dt = datetime.datetime.fromtimestamp(1)
    # tells the dataretriever class to download API data into chunks folders
    try:
        dr = DataRetriever(node_id, src_id, start_date, end_date)
        start_date_dt = dr.startDate
        dr.get_node_data()
        dr.get_weather_data()
    except Exception as E:
        tb.print_exc(E)
        log_error(str(E))
        error_state()

    result_set = []

    # reads all json chunks into memory

    for fname in sorted(os.listdir(chunks_folder)):
        data_file = open(chunks_folder + fname, 'r').read()
        result_set.append(json.loads(data_file))

    result_weather_set = []
    for fname in sorted(os.listdir(weather_folder)):
        data_file = open(weather_folder + fname, 'r').read()
        result_weather_set.append(json.loads(data_file))

    query2 = "INSERT INTO weather_data VALUES (%s, %s, %s, %s, %s)"
    dat = []

    # inserts weather datum into weather table
    for i in result_weather_set:
        try:
            for j in i['data']['results']:
                c_date = datetime.datetime.strptime(j['created'], "%Y-%m-%d %H:%M:%S.%fZ")

                if c_date > start_date_dt:
                    data_temp = [(c_date, j['sky'], j['temp'], j['humidity'], j['atm'])]
                    dat = dat + data_temp
        except Exception as E:
            pass
            log_error(str(E))

    try:
        cursor.executemany(query2, dat)
    except Exception as E:
        log_error(str(E))

    cnx.commit()

    query2 = "INSERT INTO node_datum VALUES (%s, %s, %s, %s)"

    # updates node datum in the raw node data table
    prev_date = datetime.datetime.strptime("1000", "%Y")
    dat = []

    for i in result_set:

        try:
            for j in i['data']['results']:
                c_date = datetime.datetime.strptime(j['created'], "%Y-%m-%d %H:%M:%S.%fZ")
                # checks if date is within correct range and is after previous

                if (c_date > start_date_dt) & (prev_date < c_date):

                    data_temp = [(node_id, src_id, c_date, j['wattHours'])]

                    dat = dat + data_temp
                    prev_date = c_date
        except Exception as E:
            log_error(str(E))
    try:
        cursor.executemany(query2, dat)
    except Exception as E:
        log_error(str(E))
        tb.print_exc(E)

    cnx.commit()
    data = []
    # selecting target wattages
    for j in range(2):
        for i in range(7):
            i = i + 1
            minute = j * 30
            query_data = "SELECT DATE_CREATED, WATT_HOURS FROM node_datum WHERE NODE_ID = {0} AND SOURCE_ID " \
                         "= \'{1}\' AND MINUTE(DATE_CREATED) = {2} AND DAYOFWEEK(DATE_CREATED) = {3} ORDER BY " \
                         "HOUR(DATE_CREATED), DATE_CREATED desc".format(node_id, src_id, str(minute), str(i))
            try:
                cursor.execute(query_data)
                data = data + cursor.fetchall()
            except Exception as E:
                log_error(str(E))
    queryremove = "DELETE FROM training_input WHERE NODE_ID = %s AND SOURCE_ID = %s"
    cursor.execute(queryremove, (node_id, src_id))
    cnx.commit()

    training_input = []

    def get_weather_for_date(date):
        query_weather = "SELECT DATE_CREATED, TEMP, HUMIDITY, PRESSURE, CLOUDINESS, WIND_SPEED, WIND_DIRECTION FROM " \
                        "owm_data WHERE DATE_CREATED = \'{0}\'".format(str(date))

        cursor.execute(query_weather)
        data_w = cursor.fetchall()
        return data_w

    #
    # IMPORTANT! : Inserts the formatted training datum into the training data table.
    # Only will input into training table if there is weather data at the same time
    #
    for i in range(len(data) - 2):
        if data[i][0] > training_start_date and data[i + 1][0] == data[i][0] - datetime.timedelta(days=7):

            if data[i + 2][0] == data[i][0] - datetime.timedelta(days=14):
                weather_data = get_weather_for_date(data[i][0])
                try:
                    training_input = training_input + [(node_id,
                                                        src_id,
                                                        data[i][0],
                                                        datetime.datetime.utcnow(),
                                                        data[i + 1][1],
                                                        data[i + 2][1],
                                                        weather_data[0][3],
                                                        weather_data[0][2],
                                                        weather_data[0][1],
                                                        weather_data[0][4],
                                                        weather_data[0][5],
                                                        weather_data[0][6],
                                                        data[i][1])]
                except:
                    pass
    query4 = ("INSERT INTO training_input "
              "VALUES (%s,%s,%s, %s, %s, %s, %s, %s, %s,%s,%s, %s, %s)")
    try:
        cursor.executemany(query4, training_input)
    except Exception as E:
        log_error(str(E))
        tb.print_exc(E)


    cnx.commit()

    # deletes json files after data is input into database
    for the_file in os.listdir(chunks_folder):
        file_path = os.path.join(chunks_folder, the_file)
        try:
            if os.path.isfile(file_path):
                pass
                os.unlink(file_path)
        except Exception as E:
            log_error(str(E))

    for the_file in os.listdir(weather_folder):
        file_path = os.path.join(weather_folder, the_file)

        try:
            if os.path.isfile(file_path):
                pass
                os.unlink(file_path)
        except Exception as E:
            log_error(str(E))


try:
    populate()
except Exception as e:
    log_error(str(e))
    tb.print_exc(e)
    error_state()


log_end_time()