from urllib2 import Request, urlopen, URLError
import json
import tensorflow as tf
import numpy as np
from DataRetriever import DataRetriever
from matplotlib import pyplot
import datetime
import os
from tensorflow.contrib import rnn

numHidden = 50
numHidden2 = 50
numInputs = 2
numOutputs = 1

timesteps = 1000

resultWattSet = []
for fname in sorted(os.listdir("./chunks")):
    dataFile = open("./chunks/"+fname, 'r').read()
    resultWattSet.append(json.loads(dataFile))

resultWeatherSet = []
for fname in sorted(os.listdir("./weather")):
    dataFile = open("./weather/"+fname, 'r').read()
    resultWeatherSet.append(json.loads(dataFile))

def LSTM(X):


    lstm = rnn.LSTMCell(numHidden,state_is_tuple=True)
    lstm2 = rnn.LSTMCell(numHidden2,state_is_tuple=True)
    cell = rnn.MultiRNNCell([lstm,lstm2])

    output, state = tf.nn.dynamic_rnn(cell, X, dtype=tf.float32)

    w = tf.Variable(tf.truncated_normal([numHidden2, numOutputs]))

    b = tf.Variable(tf.random_normal([numOutputs]))

    out = tf.sigmoid(tf.matmul(output[-1],w) + b)

    return out
X = tf.placeholder(tf.float32, [None,timesteps,numInputs])
pred = LSTM(X)
Y = tf.placeholder(tf.float32, [None,numOutputs])
error = tf.reduce_mean(tf.square(pred-Y));
starter_learning_rate = 0.01

global_step = tf.Variable(1, trainable=False)
lr = tf.train.exponential_decay(starter_learning_rate, global_step,
                                        100, 0.999, staircase=True)
lr = 0.01

opt = tf.train.RMSPropOptimizer(lr)

optimize = opt.minimize(error,global_step)

with tf.Session() as sess:
    sess.run(tf.initialize_all_variables())
    dataSetWattage = []
    dataSetTime = []
    dataSetTemp = []
    for i in resultWattSet:
        #print(i['data']['results'][0]['created'])
        base = i['data']['results']
        for item in base:
            dataSetWattage.append(item['watts'])
            dataSetTime.append(datetime.datetime.strptime(item['created'], "%Y-%m-%d %H:%M:%S.%fZ"))

    for i in resultWeatherSet:
        base = i['data']['results']
        for item in base:
            #print(item['created'])
            #print(item['temp'])
            for l in range(30):
                dataSetTemp.append(item['temp'])

    dataSetTemp = (dataSetTemp - np.min(dataSetTemp))/(np.max(dataSetTemp) - np.min(dataSetTemp))

    lengthTo = 1000

    inTimes = dataSetTime[0:lengthTo]
    inTemps = dataSetTemp[0:lengthTo]

    trainingTimes = []
    trainingTemps = []

    date = []
    for i in inTimes:
        trainingTimes.append(float(i.hour * 60 + i.minute))

    for i in inTemps:
        trainingTemps.append(float(i))

    trainingTimes = (trainingTimes - np.min(trainingTimes)) / (np.max(trainingTimes) - np.min(trainingTimes))

    trainingDataX = [trainingTemps, trainingTimes]

    trainingDataY = dataSetWattage[0:lengthTo]

    trainingDataX = np.reshape(trainingDataX, (-1, 1, numInputs))
    trainingDataY = np.reshape(np.array(trainingDataY), (-1, numOutputs))

    pyplot.ion()
    fig = pyplot.figure()
    ax = fig.add_subplot(111)
    errSum = 0
    c = 0
    outp = []
    print("here")
    for i in range(100):
        for j in trainingDataX:
            inx = np.reshape(j, (1, 1, 2))
            # inx = [[[1,2]]]
            print(inx)
            iny = trainingDataY[c]
            iny = np.reshape(iny, (1, 1))
            [_out, _error, _] = sess.run([pred, error, optimize], feed_dict={X: inx, Y: iny})
            print("nice")
            errSum += _error
            c += 1
            # print(_out)
            np.append(outp,_out)

        outp = np.reshape(_out, (-1))
        fig.clear()

        pyplot.plot(dataSetTime[0:lengthTo], trainingDataY)
        pyplot.plot(dataSetTime[0:lengthTo], outp)
        # pyplot.draw()
        fig.canvas.draw()
        print(errSum)
        errSum = 0









