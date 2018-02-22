from optparse import OptionParser
import argparse
import mysql.connector
from subprocess import call
import logging
import os
from training import GenerateTrainingFile as gen
from prediction import GeneratePredictionData as genP
import PlotData as plt
import traceback as tb
import training.DatabaseCorrelation as dc
import prediction.DatabasePrediction as dp
import datetime
dir = os.path.dirname(__file__)

logger = logging.getLogger('emergentlogger')
logger.setLevel(logging.DEBUG)

fh = logging.FileHandler('../../logs/python_logs/emergentlog')
fh.setLevel(logging.DEBUG)

formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
fh.setFormatter(formatter)

logger.addHandler(fh)

logger.info("Running emergent!")

cnx = mysql.connector.connect(user='solarquant', password='solarquant',
                              host='localhost',
                              database='solarquant')
cursor = cnx.cursor()

parser = OptionParser()
argParser = argparse.ArgumentParser()
argParser.add_argument("-r", "--reqid", dest="reqId", help="ID for request",
                  metavar = "ID", required = True)

argParser.add_argument("-t", "--train", action = 'store_true', dest="mode", help="MODE for request - train(t) or predict", default=True)

argParser.add_argument("-p", "--predict", action = 'store_false', dest="mode", help="MODE for request - train or predict")

args = argParser.parse_args()
file = os.path.join(dir, 'example.log'.format(args.reqId))


def getRequestParameters(type):
    query = ("SELECT NODE_ID, SOURCE_ID FROM {}_requests WHERE REQUEST_ID = %s".format(type))
    cursor.execute(query, (args.reqId,))
    out = cursor.fetchall()[0]
    return out[0], out[1]

def log_end_time(type):
    ctime = datetime.datetime.now()
    query = ("UPDATE {}_state_time SET COMPLETION_DATE=%s WHERE REQUEST_ID=%s AND STATE=%s".format(type))
    cursor.execute(query, (ctime, args.reqId, 3))
    cnx.commit()


emergent_log_file = '../../logs/emergent_logs/runlog'
try:
    if(args.mode):
        logger.info("Started training job")
        logger.info("Getting metadata")
        nodeId, srcId = getRequestParameters("training")


        file = os.path.join(dir, "../run.sh")
        logger.info("creating training input file")
        gen.generate(args.reqId)

        logger.info("training begun")
        call([file,args.reqId, str(nodeId), srcId])

        file = os.path.join(dir, "../test.sh")
        logger.info("evaluation begun")
        call([file, args.reqId, str(nodeId), srcId, emergent_log_file])

        plt.setupTrainingOutput(args.reqId)
        logger.info("Storing output")
        dc.store_correlation(request_id=args.reqId)
        logger.info("Finished.")
        log_end_time("training")

    else:
        logger.info("Started prediction job")
        logger.info("Getting metadata")
        nodeId, srcId = getRequestParameters("prediction")

        file = os.path.join(dir, "../predict.sh")
        logger.info("creating prediction input file")
        genP.generate(args.reqId)
        logger.info("prediction begun")
        call([file, args.reqId, str(nodeId), str(srcId), emergent_log_file])
        plt.setupPredictionOutput(args.reqId)
        logger.info("storing output")
        dp.store_correlation(request_id=args.reqId)
        logger.info("Finished.")
        log_end_time("prediction")

except Exception as e:
    logging.error(str(e))
    tb.print_exc(e)

