'''Entry point for running SolarQuant training/prediction module.

Has request ID input, and flag for whether is predicting or training.
'''


from optparse import OptionParser
import argparse
import com.run.RunTraining as rt
import com.run.RunPrediction as rp
import mysql
import logging
import datetime as dt
logger = logging.getLogger('pylog1')
logger.setLevel(logging.DEBUG)

fh = logging.FileHandler('/var/www/html/solarquant/logs/python_logs/quantlog')
fh.setLevel(logging.DEBUG)

formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
fh.setFormatter(formatter)

logger.addHandler(fh)

logger.info("Started QuantExecutor!")

def log_end_time(type):
    ctime = dt.datetime.now()
    query = ("UPDATE {}_state_time SET COMPLETION_DATE=%s WHERE REQUEST_ID=%s AND STATE=%s".format(type))
    cursor.execute(query, (ctime, args.reqId, 3))
    cnx.commit()


# create connection to database
cnx = mysql.connector.connect(user='solarquant', password='solarquant',
                              host='localhost',
                              database='solarquant')

# retrieve cursor module to perform database actions with.
cursor = cnx.cursor()

# retrieve arguments passed in terminal call.
parser = OptionParser()
argParser = argparse.ArgumentParser()
argParser.add_argument("-r", "--reqid", dest="reqId", help="ID for request",
                  metavar = "ID", required = True)

argParser.add_argument("-t", "--train", action = 'store_true', dest="mode", help="MODE for request - train(t) or predict", default=True)

argParser.add_argument("-p", "--predict", action = 'store_false', dest="mode", help="MODE for request - train or predict")



args = argParser.parse_args()

# start training or prediction job.
if(args.mode):
    logger.info("Running training...")
    rt.train(args.reqId)
    log_end_time("training")
else:
    logger.info("Running prediction...")
    rp.predict(args.reqId)
    log_end_time("prediction")


