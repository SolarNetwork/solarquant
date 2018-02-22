"""This is the entry point for a module that takes a request ID and a date range, and fills the database with data
within the date range for the node described by the request ID table.
"""

import mysql.connector
import os
import argparse
import PredictionDataRetriever as pr
import datetime as dt
import logging
logger = logging.getLogger('prediction_data_retriever')
logger.setLevel(logging.DEBUG)

fh = logging.FileHandler('/var/www/html/solarquant/logs/python_logs/data_retrieval_log')
fh.setLevel(logging.DEBUG)

formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
fh.setFormatter(formatter)

logger.addHandler(fh)



directory = os.path.dirname(__file__)
argParser = argparse.ArgumentParser()
argParser.add_argument("-r", "--reqid", dest="reqId", help="ID for request",
                       metavar="ID", required=True)

argParser.add_argument("-s", "--startdate", dest="startDate", help="start date",
                       metavar="start")

argParser.add_argument("-e", "--enddate", dest="endDate", help="end date",
                       metavar="end")

logger.info("Started data retrieval for prediction job!")
args = argParser.parse_args()
def log_end_time():
    ctime = dt.datetime.now()
    query = ("UPDATE training_state_time SET COMPLETION_DATE=%s WHERE REQUEST_ID=%s AND STATE=%s")
    cursor.execute(query, (ctime, args.reqId, 2))
    cnx.commit()

# clears all json files in the chunks folder
logger.info("Clearing out old chunks JSON")
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


# grabs metadata about request
logger.info("Retrieving request metadata")
cursor.execute(infoQuery)
data = cursor.fetchall()
for row in data:
    nodeId = str(row[0])
    srcId = str(row[1])

# downloads latest weather data (if applicable)
logger.info("Downloading new weather data (if applicable)")
pr.update_weather()

# if start data / end date are not set, use a default.
if ((args.startDate == None) & (args.endDate == None)):
    # stores the raw node datum
    logger.info("using default start/end date to insert new prediction input")
    pr.update_datum(nodeId, srcId)
    # creates a prediction input for the request ID
    logger.info("Adding prediction input to database")
    pr.add_prediction_input(nodeId, srcId)
else:
    logger.info("using defined start/end date to insert new prediction input")
    start = dt.datetime.strptime(args.startDate, "%Y-%m-%d:%H")
    end = dt.datetime.strptime(args.endDate, "%Y-%m-%d:%H")

    pr.update_datum(nodeId, srcId, end, start)
    pr.add_prediction_input(nodeId, srcId)

logger.info("Finished.")
log_end_time()