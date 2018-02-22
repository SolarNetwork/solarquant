import os
from urllib2 import Request, urlopen, URLError
import datetime as dt
import csv
import mysql.connector
import json
import pytz


cnx = mysql.connector.connect(user='solarquant', password='solarquant',
                              host='localhost',
                              database='solarquant')
cursor = cnx.cursor()

def getPredictionRequestInfo(requestId):
    query = ("SELECT NODE_ID, SOURCE_ID, DATE_REQUESTED FROM prediction_requests "
             "WHERE REQUEST_ID = {}")

    query = query.format(requestId)

    cursor.execute(query)

    data = cursor.fetchall()[0]
    nodeId = data[0]
    sourceId = data[1]
    dateRequested = data[2]

    return nodeId, sourceId, dateRequested


def update_predictions(request_id):
    [nodeId, srcId, dateRequested] = getPredictionRequestInfo(request_id)
    endDate = dateRequested + dt.timedelta(days=7)
    endString = endDate.strftime("%Y-%m-%dT12%%3A00")

    startString = dateRequested.strftime("%Y-%m-%dT12%%3A00")

    query = "UPDATE prediction_output SET WATT_HOURS = %s WHERE REQUEST_ID = %s AND DATE = %s"
    request = "https://data.solarnetwork.net/solarquery/api/v1/pub/datum/list?nodeId=" + str(nodeId) + "&aggregation=ThirtyMinute&startDate=" + \
                  startString + "&endDate=" + endString + "&sourceIds=" + srcId + "&max=5000000"
    response = urlopen(request)

    data = json.loads(response.read())

    inp = [(line["wattHours"], request_id, dt.datetime.strptime(line["created"], "%Y-%m-%d %H:%M:%S.%fZ") + dt.timedelta(hours = 13)) for line in data["data"]["results"]]
    cursor.executemany(query,(inp))
    cnx.commit()

query = "SELECT REQUEST_ID FROM prediction_requests"
cursor.execute(query)
request_ids = cursor.fetchall()

for i in request_ids:
    update_predictions(i[-1])
    try:

        nodeId, srcId, dateRequested = getPredictionRequestInfo(i)
        dateRequested = dateRequested - dt.timedelta(days=1)
        startString = dateRequested.strftime("%Y-%m-%dT12%%3A00")
        endDate = dateRequested + dt.timedelta(days=7)
        endString = endDate.strftime("%Y-%m-%dT12%%3A00")

        request = "https://data.solarnetwork.net/solarquery/api/v1/pub/datum/list?nodeId=" + str(nodeId) + "&aggregation=ThirtyMinute&startDate=" + \
                  startString + "&endDate=" + endString + "&sourceIds=" + srcId + "&max=5000000"
        response = urlopen(request)
        with open(str(i) + '_real.csv', 'w') as outfile:
            outfile.write("created,wattHours\n")
            try:

                data = json.loads(response.read())
                for line in data["data"]["results"]:
                    date = dt.datetime.strptime(line["created"], "%Y-%m-%d %H:%M:%S.%fZ")
                    date = date.strftime("%Y-%m-%dT%H:%M:%S.00Z")
                    string = str(date) + "," + str(line["wattHours"]) + "\n"
                    outfile.write(string)

            except Exception as e:
                pass
    except Exception as e:
        pass

