"""This is the entry point for a module that takes a request ID and a date range, and fills the database with data
within the date range for the node described by the request ID table.
"""

import mysql.connector
import os
import argparse
import PredictionDataRetriever as pr
import datetime as dt

directory = os.path.dirname(__file__)
argParser = argparse.ArgumentParser()
argParser.add_argument("-r", "--reqid", dest="reqId", help="ID for request",
                       metavar="ID", required=True)

argParser.add_argument("-s", "--startdate", dest="startDate", help="start date",
                       metavar="start")

argParser.add_argument("-e", "--enddate", dest="endDate", help="end date",
                       metavar="end")

args = argParser.parse_args()
def log_end_time():
    ctime = dt.datetime.now()
    query = ("UPDATE training_state_time SET COMPLETION_DATE=%s WHERE REQUEST_ID=%s AND STATE=%s")
    cursor.execute(query, (ctime, args.reqId, 2))
    cnx.commit()

# clears all json files in the chunks folder
chunksFolder = os.path.join(directory, "chunks/")
weatherFolder = os.path.join(directory, "weather/")
for the_file in os.listdir(chunksFolder):
    file_path = os.path.join(chunksFolder, the_file)
    try:
        if os.path.isfile(file_path):
            os.unlink(file_path)
    except Exception as e:
        print(e)


cnx = mysql.connector.connect(user='solarquant', password='solarquant',
                              host='localhost',
                              database='solarquant')
cursor = cnx.cursor()

infoQuery = "SELECT NODE_ID, SOURCE_ID FROM prediction_requests WHERE REQUEST_ID = " + args.reqId


def log_error(messg):
    f = "../logs/data_retrieval.txt"
    filename = os.path.join(dir, f)
    with open(filename, "a") as myfile:
        myfile.write("\nERROR:" + messg)

# grabs metadata about request
cursor.execute(infoQuery)
data = cursor.fetchall()
for row in data:
    nodeId = str(row[0])
    srcId = str(row[1])

# downloads latest weather data (if applicable)
pr.update_weather()

# if start data / end date are not set, use a default.
if ((args.startDate == None) & (args.endDate == None)):
    # stores the raw node datum
    pr.update_datum(nodeId, srcId)
    # creates a prediction input for the request ID
    pr.add_prediction_input(nodeId, srcId)
else:

    start = dt.datetime.strptime(args.startDate, "%Y-%m-%d:%H")
    end = dt.datetime.strptime(args.endDate, "%Y-%m-%d:%H")

    pr.update_datum(nodeId, srcId, end, start)
    pr.add_prediction_input(nodeId, srcId)
log_end_time()