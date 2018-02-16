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
dir = os.path.dirname(__file__)

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
logging.basicConfig(filename=file, filemode='w', level=logging.DEBUG)


def getRequestParameters(type):
    query = ("SELECT NODE_ID, SOURCE_ID FROM {}_requests WHERE REQUEST_ID = %s".format(type))
    cursor.execute(query, (args.reqId,))
    out = cursor.fetchall()[0]
    return out[0], out[1]


try:
    if(args.mode):
        nodeId, srcId = getRequestParameters("training")
        file = os.path.join(dir, "../run.sh")

        gen.generate(args.reqId)
        print('done')
        call([file,args.reqId, str(nodeId), srcId])

        file = os.path.join(dir, "../test.sh")
        call([file, args.reqId, str(nodeId), srcId])

        plt.setupTrainingOutput(args.reqId)

    else:
        nodeId, srcId = getRequestParameters("prediction")
        file = os.path.join(dir, "../predict.sh")
        genP.generate(args.reqId)
        call([file, args.reqId, str(nodeId), srcId])
        plt.setupPredictionOutput(args.reqId)

except Exception as e:
    logging.info(str(e))
    tb.print_exc(e)

