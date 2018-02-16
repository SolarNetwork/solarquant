import numpy as np
import matplotlib.pyplot as plt
import datetime
import os
import csv
directory = os.path.dirname(__file__)

def setupTrainingOutput(reqId):
    file = os.path.join(directory, "../test_out/out_{}.csv".format(reqId))

    dates = []
    predictions = []
    real = []
    with open(file) as f:
        reader = csv.DictReader(f, delimiter='	')
        for row in reader:
            predictions.append(row['Output_act_0'])
            dates.append(row['trial_name'])
            real.append(row['Output_targ_0'])


    datesFormatted = []
    for i in dates:
        date = datetime.datetime.strptime(i, "%Y-%m-%d %H:%M:%S")
        datesFormatted.append(date.strftime("%Y-%m-%dT%H:%M:%S.000Z"))

    filename = os.path.join(directory, '../../../prediction_output/correlations/{}_correlation.csv'.format(reqId))
    with open(filename, 'w') as fp:
        fp.write("wattHours,predictedWattHours,created\n")

        for i in range(len(predictions)):
            fp.write(str(real[i]) + "," + str(predictions[i]) + "," + datesFormatted[i] + "\n")

def setupPredictionOutput(reqId):
    file = os.path.join(directory, "../prediction_out/out_{}.csv".format(reqId))

    dates = []
    predictions = []
    real = []
    with open(file) as f:
        reader = csv.DictReader(f, delimiter='	')
        for row in reader:
            predictions.append(row['Output_act_0'])
            dates.append(row['trial_name'])


    datesFormatted = []
    for i in dates:
        date = datetime.datetime.strptime(i, "%Y-%m-%d %H:%M:%S")
        datesFormatted.append(date.strftime("%Y-%m-%dT%H:%M:%S.000Z"))

    filename = os.path.join(directory, '../../../prediction_output/predictions/{}_prediction.csv'.format(reqId))

    with open(filename, 'w') as fp:
        fp.write("predictedWattHours,created\n")
        for i in range(len(predictions)):
            fp.write(str(predictions[i]) + "," + datesFormatted[i] + "\n")

    filename = os.path.join(directory, '../../../prediction_output/predictions/{}_real.csv'.format(reqId))
    with open(filename, 'w') as fp:
        fp.write("wattHours,created\n")