from urllib2 import Request, urlopen, URLError
import json
import tensorflow as tf
import numpy as np

from DataRetriever import DataRetriever
from matplotlib import pyplot
import datetime
import os

import QuantForward as qf


#TODO !!!! FIX THE RETRIEVER FOR PREVIOUS WEEK DATA - SOME DATA POINTS MISSING!

def getParams(nodeId, srcId, start, end):

    dr = DataRetriever(nodeId, srcId,start,end)
    #DONT USE HALF HOUR DATA!!!!!!!!!
    #dr.getWeatherData()
    #dr.getNodeData()

    resultSet = []
    for fname in sorted(os.listdir("./chunks")):
        dataFile = open("./chunks/"+fname, 'r').read()
        resultSet.append(json.loads(dataFile))

    resultWeatherSet = []
    for fname in sorted(os.listdir("./weather")):
        dataFile = open("./weather/" + fname, 'r').read()
        resultWeatherSet.append(json.loads(dataFile))

    dataSetTemp = []
    dataSetWattage = []
    dataSetTime = []
    dataSetDay = []
    for i in resultSet:
        base = i['data']['results']
        for item in base:
            dataSetWattage.append(item['wattHours'])
            dat = datetime.datetime.strptime(item['created'], "%Y-%m-%d %H:%M:%S.%fZ")
            dataSetTime.append(dat)
            dataSetDay.append(float(dat.timetuple().tm_yday))

    for i in range(len(dataSetTime)//168):
        print(dataSetTime[i*168])
            
    dataSetDay = (dataSetDay-np.min(dataSetDay))/(np.max(dataSetDay)-np.min(dataSetDay))
    print(np.size(dataSetTime))
    fine = []
    cloudy = []
    partlyCloudy = []
    showers = []
    fewShowers = []
    windy = []
    weatherInput = []

    weatherWords = ["Cloudy", "Partly cloudy", "Fine", "Few showers", "Showers", "Windy", "Rain", "Thunder"]
    x = 0
    numWords = len(weatherWords)
    for i in resultWeatherSet:
        base = i['data']['results']
        for item in base:
            if(x%2 == 0):
                dataSetTemp.append(item['temp'])
                temp = []
                for word in weatherWords:
                    try:
                        if (item['sky'] == word):
                            temp = np.append(temp, 1)
                        else:
                            temp = np.append(temp, 0)
                    except:
                        temp = np.append(temp, 0)
                weatherInput = np.append(weatherInput, temp)
            x += 1

    weatherInput = np.reshape(weatherInput, (-1,numWords))

    endLen = min(len(dataSetTemp), len(dataSetTime))
    dataSetTemp = (dataSetTemp-np.min(dataSetTemp))/(np.max(dataSetTemp)-np.min(dataSetTemp))
    lengthTo = endLen - 200
    trainingDataX = []

    dataSetWattage = (dataSetWattage-np.min(dataSetWattage))/(np.max(dataSetWattage)-np.min(dataSetWattage))

    inSet = dataSetTime[0:lengthTo]
    date = []
    weekdays = []
    for i in inSet:
        trainingDataX.append(float(i.hour)/23)
        temp = []
        for day in range(7):
            if(day == i.weekday()):
                temp.append(1)
            else:
                temp.append(0)
        weekdays.append(temp)

    testDataX = []
    testInSet = dataSetTime[lengthTo:endLen]

    weekdaysTest = []

    for i in testInSet:
        testDataX.append(float(i.hour)/23)
        temp = []
        for day in range(7):
            if(day == i.weekday()):
                temp.append(1)
            else:
                temp.append(0)
        weekdaysTest.append(temp)

    wattageInp = []
    wattageTestInp = []

    wattageInp = list(dataSetWattage[0:endLen-168])

    lengthTo = lengthTo - 168

   # print(np.shape(wattageInp))



   # trainingDataX = [[trainingDataX, dataSetDay[0:lengthTo], dataSetTemp[0:lengthTo],wattageInp[0],wattageInp[1]]]


  #  for i in trainingDataX:
       # print i

    dataSetWattage = dataSetWattage[168:endLen]

    trainingDataX = [wattageInp[0:lengthTo]]


    testDataX = [wattageInp[lengthTo:endLen]]

    trainingDataY = dataSetWattage[0:lengthTo]
    print(np.size(trainingDataX))
    print(np.size(trainingDataY))
    testDataY = dataSetWattage[lengthTo:endLen]
    testDataY = np.array(testDataY, dtype=float)

    trainingDataY = np.array(trainingDataY, dtype=float)

    temp = []

    for i in range(len(trainingDataX)):
        temp.append(np.append(trainingDataX[i][0], weekdays[i]))

    timeSteps = 5

    trainingDataX = np.reshape(trainingDataX, (-1,1,1))
    tot = []
    for x in range(len(trainingDataX)):
        temp = []
        for i in range(5):
            if(x-i < 0):
                temp.append([0]*10)
            else:
                temp.append(trainingDataX[x - i])

        tot.append(list(reversed(temp)))

    #trainingDataX = tot
    #testDataX = np.transpose(testDataX, (2, 0, 1))

    trainingWeather = weatherInput[lengthTo:endLen]
    temp = []
#    for i in range(len(testDataX)):
#        temp.append(np.append(testDataX[i][0], weekdaysTest[i]))

    #testDataX = temp

    testDataX = np.reshape(testDataX, (-1,1,1))
    tot = []
    for x in range(len(testDataX)):
        temp = []
        for i in range(5):
            if(x-i < 0):
                temp.append([0]*10)
            else:
                temp.append(testDataX[x - i])

        tot.append(list(reversed(temp)))

    #testDataX = tot

    trainingDataY =np.reshape(np.array(trainingDataY), (-1,qf.numOutputs))
    testDataY = np.reshape(np.array(testDataY), (-1,qf.numOutputs))
    #print(np.shape(trainingDataY))
    #print(np.shape(trainingDataX))
    return dataSetTime[(168):], dataSetTemp[(168):], trainingDataX, trainingDataY, testDataX, testDataY

