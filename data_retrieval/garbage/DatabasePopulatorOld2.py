
import mysql.connector
import os
import json
import datetime
from optparse import OptionParser
import argparse
import traceback as tb

from DataRetriever import DataRetriever

dir = os.path.dirname(__file__)

argParser = argparse.ArgumentParser()
argParser.add_argument("-r", "--reqid", dest="reqId", help="ID for request",
                  metavar = "ID", required = True)


argParser.add_argument("-s", "--startdate", dest="startDate", help="start date",
                  metavar = "start")

argParser.add_argument("-e", "--enddate", dest="endDate", help="end date",
                  metavar = "end")

args = argParser.parse_args()

cnx = mysql.connector.connect(user='solarquant', password='solarquant',
                              host='localhost',
                              database='solarquant')
cursor = cnx.cursor()



#
#
#
#FOR FUTURE - FILL EMPTY VALUES IN WITH 00000000
#
#
#

def errorState():
    query = ("UPDATE training_requests SET STATUS = 5 "
             "WHERE REQUEST_ID = " + args.reqId)
    print(query)
    cursor.execute(query)
    cnx.commit()


def logError(messg):
    f = "../logs/data_retrieval.txt"
    filename = os.path.join(dir, f)
    with open(filename, "a") as myfile:
        myfile.write("\nERROR:"+messg)

logError("\nstarted!!!!!!!!!!!")

def populate():

    chunksFolder = os.path.join(dir, "chunks/")
    weatherFolder = os.path.join(dir, "weather/")
    #print(chunksFolder)
    for the_file in os.listdir(chunksFolder):
        file_path = os.path.join(chunksFolder, the_file)
        try:
            if os.path.isfile(file_path):
                1
            #elif os.path.isdir(file_path): shutil.rmtree(file_path)
        except Exception as e:
            print(e)


    for the_file in os.listdir(weatherFolder):
        file_path = os.path.join(weatherFolder, the_file)
        try:
            if os.path.isfile(file_path):
                1


                # elif os.path.isdir(file_path): shutil.rmtree(file_path)
        except Exception as e:
            print(e)

    startDate = args.startDate
    endDate = args.endDate

    infoQuery = "SELECT NODE_ID, SOURCE_ID FROM training_requests WHERE REQUEST_ID = "+args.reqId

    cursor.execute(infoQuery)
    data = cursor.fetchall()
    for row in data:
        nodeId = str(row[0])
        srcId = str(row[1])

    dr = DataRetriever(nodeId,srcId,startDate, endDate)
    try:
        1

        #dr.getNodeData()
       # dr.getWeatherData()
    except:

        query = ("UPDATE training_requests SET STATUS = 5 "
                 "WHERE REQUEST_ID = " + args.reqId)
        print(query)
        cursor.execute(query)
        cnx.commit()

    query = ("INSERT INTO tensorflow_training_input "
             "VALUES (%s,%s,%s, %s, %s, %s, %s, %s, %s)")

    resultSet = []
    for fname in sorted(os.listdir(chunksFolder)):
        dataFile = open(chunksFolder + fname, 'r').read()
        resultSet.append(json.loads(dataFile))
    resultWeatherSet = []
    for fname in sorted(os.listdir(weatherFolder)):
        dataFile = open(weatherFolder + fname, 'r').read()
        resultWeatherSet.append(json.loads(dataFile))

    logError("created weather sets")

    dataSetTemp = []
    dataSetWattage = []
    dataSetTime = []
    dataSetDay = []
    dataSetSky = []
    minHour = 1000
    maxHour = 0
    try:
        for i in resultSet:
            c = 0
            base = i['data']['results']
            baseWeather = resultWeatherSet[0]['data']['results']

            for item in base:
                try:
                    dataSetTemp.append(baseWeather[c]['temp'])
                    dat = datetime.datetime.strptime(item['created'], "%Y-%m-%d %H:%M:%S.%fZ")
                    dataSetTime.append(dat)
                    dataSetWattage.append(item['wattHours'])

                    if(float(dat.hour) > maxHour):
                        maxHour = int(dat.hour)
                    if (float(dat.hour) < minHour):
                        minHour = int(dat.hour)
                    dataSetDay.append(float(dat.timetuple().tm_yday))

                    dataSetSky.append(baseWeather[c].get('sky'))

                    c+=2
                except:
                    1
        previous1Watts = []
        previous2Watts = []
        watts = []
        adjustedTimeList = []
        adjustedTempList = []
        adjustedSkyList = []

        hourRange = maxHour-minHour + 1

        for i in range(len(dataSetTime)):
            print(dataSetTime[i])
            print(dataSetTime[i - hourRange*7])
            print(dataSetTime[i] - datetime.timedelta(days=7))
            print()
            # ASSUMES THAT NODE IS ON FOR 24 HOURS - BAD
            if(dataSetTime[i - hourRange*7] == dataSetTime[i] - datetime.timedelta(days=7)):
                if(dataSetTime[i - hourRange*7*2] == dataSetTime[i] - datetime.timedelta(days=14)):
                    previous1Watts.append(dataSetWattage[i-hourRange*7])
                    previous2Watts.append(dataSetWattage[i - hourRange*7*2])
                    watts.append(dataSetWattage[i])
                    adjustedTimeList.append(dataSetTime[i])
                    adjustedTempList.append(dataSetTemp[i])
                    adjustedSkyList.append(dataSetSky[i])
        data = ""
        if(len(adjustedSkyList) == 0):
            errorState()
        else:
            data = [(nodeId,srcId,adjustedTimeList[i], datetime.datetime.today(),
                 watts[i],previous1Watts[i],adjustedTempList[i],
                 previous2Watts[i], adjustedSkyList[i]) for i in range(len(adjustedTimeList))]

        logError("executing query")
        cursor.executemany(query, data)


        cnx.commit()
    except Exception as e:
        logError(str(e))
        errorState()


    for the_file in os.listdir(chunksFolder):
        file_path = os.path.join(chunksFolder, the_file)
        try:
            if os.path.isfile(file_path):
                1

            #elif os.path.isdir(file_path): shutil.rmtree(file_path)
        except Exception as e:
            print(e)


    for the_file in os.listdir(weatherFolder):
        file_path = os.path.join(weatherFolder, the_file)
        try:
            if os.path.isfile(file_path):
                1

                # elif os.path.isdir(file_path): shutil.rmtree(file_path)
        except Exception as e:
            print(e)

populate()







