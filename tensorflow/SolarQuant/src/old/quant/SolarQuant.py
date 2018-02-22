from optparse import OptionParser
import argparse
from NetworkTrainer import NetworkTrainer


parser = OptionParser()
argParser = argparse.ArgumentParser()
argParser.add_argument("-n", "--node", dest="nodeId", help="input solarnode ID",
                  metavar = "ID")

argParser.add_argument("-s", "--source", dest="srcId", help="input solarnode SOURCEID",
                    metavar = "SOURCEID")

argParser.add_argument("--start",dest="startDate", help="input start date")

argParser.add_argument("--end",dest="endDate", help="input end date")

argParser.add_argument("-t", action="store_true", dest="training", default = True)
argParser.add_argument("-p", action="store_false", dest="training")

args = argParser.parse_args()

if(args.training == True):
    nt = NetworkTrainer()
    nt.train(args.nodeId, args.srcId, args.startDate, args.endDate)


