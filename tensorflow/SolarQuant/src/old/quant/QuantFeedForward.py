from urllib2 import Request, urlopen, URLError
import json
import tensorflow as tf
import numpy as np
from DataRetriever import DataRetriever
from matplotlib import pyplot
import datetime
import os

from tensorflow.contrib import rnn


numHidden = 100
dataR = DataRetriever()


dataR.getNodeData(startDate, endDate)


resultSet = []
for fname in sorted(os.listdir("./chunks")):
    dataFile = open("./chunks/"+fname, 'r').read()
    resultSet.append(json.loads(dataFile))

#x = np.array([datetime.datetime.strptime(i['created'], "%Y-%m-%d %H:%M:%S.%fZ") for i in resultSet])
#y = np.array([i['watts'] for i in resultSet])





#lstm = rnn.LSTMCell(numHidden)

#output, state = tf.nn.dynamic_rnn(lstm, X, dtype=tf.float32)

b2 = tf.Variable(tf.truncated_normal([1]))

w2 = tf.Variable(tf.truncated_normal([numHidden,1]))

w = tf.Variable(tf.truncated_normal([1,numHidden]))
#output = tf.transpose(output, [1, 0, 2])

b = tf.Variable(tf.truncated_normal([numHidden]))


hidden = tf.nn.sigmoid(tf.matmul(X, w) + b)

output = tf.nn.sigmoid(tf.matmul(hidden,w2) + b2)

error = tf.reduce_mean(tf.square(output - Y));
opt = tf.train.AdagradOptimizer(0.3)

optimize = opt.minimize(error)


with tf.Session() as sess:



    sess.run(tf.initialize_all_variables())

    lengthTo = 20
    dataSetWattage = []
    dataSetTime = []

    for i in resultSet:
        print(i['data']['results'][0]['created'])
        base = i['data']['results']
        for item in base:
            dataSetWattage.append(item['watts'])
            dataSetTime.append(datetime.datetime.strptime(item['created'], "%Y-%m-%d %H:%M:%S.%fZ"))

    trainingDataX = []
    inSet = dataSetTime[50:lengthTo+50]

    for i in inSet:
        date = float(i.hour*60 + i.minute)
        trainingDataX.append([date])

    #date = (date-np.min(date)) / np.max(date)


    trainingDataY = dataSetWattage[50:lengthTo+50]

    maxi = np.max(trainingDataY)
    mini = np.min(trainingDataY)

    print(trainingDataY)
    print(maxi)

    trainingDataY = np.array(trainingDataY, dtype=float)
    trainingDataY = (trainingDataY-mini)/abs(maxi)

    #print(trainingDataY)

    testDataX = []

    testSet = dataSetTime[lengthTo:]
    for i in testSet:
        date = float(i.minute)
        testDataX.append([date])

    for i in range(500):
        errSum = 0
        c = 0
        for i in trainingDataX:
            inx = np.reshape(i, (-1,1))

            iny = np.reshape(np.array(trainingDataY[c]), (1))
            [_out, _error, _] = sess.run([output,error,optimize], feed_dict={X:inx, Y:iny})
            errSum+= _error
            #print(_out)
            #print(iny)
            c+=1
        print(errSum)
        #print(errSum/20)
            #print(_out[0,0])
        #print(_out)
        #print(iny)

        #print(trainingDataX)
        #print((_out*maxi +mini) - (trainingDataY*maxi + mini))
        #print(trainingDataY*maxi + mini)

    #pyplot.plot(trainingDataX,trainingDataY)

    #pyplot.plot(dataSetTime, dataSetWattage)
    _outy = []
    for i in trainingDataX:
        i = np.reshape(i, (-1, 1))
        [outp] = sess.run([output], feed_dict={X:i})
        #print(outp[0,0])
        _outy.append(outp[0,0])

    _outy = np.array(_outy)*abs(maxi) + mini
    print(np.shape(_outy))



    pyplot.plot(dataSetTime[50:lengthTo+50], dataSetWattage[50:lengthTo+50])
    pyplot.plot(dataSetTime[50:lengthTo+50], _outy)
    pyplot.show()
