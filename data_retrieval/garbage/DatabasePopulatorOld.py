
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

def logError(messg):
    f = "../logs/data_retrieval.txt"
    filename = os.path.join(dir, f)
    with open(filename, "a") as myfile:
        myfile.write("\nERROR:"+messg)

logError("\nstarted!!!!!!!!!!!")


def errorState():
    query = ("UPDATE training_requests SET STATUS = 5 "
             "WHERE REQUEST_ID = " + args.reqId)
    print(query)
    cursor.execute(query)
    cnx.commit()

def populate():

    chunksFolder = os.path.join(dir, "chunks/")
    weatherFolder = os.path.join(dir, "weather/")
    #print(chunksFolder)
    for the_file in os.listdir(chunksFolder):
        file_path = os.path.join(chunksFolder, the_file)
        try:
            if os.path.isfile(file_path):
                1
                #os.unlink(file_path)
            #elif os.path.isdir(file_path): shutil.rmtree(file_path)
        except Exception as e:
            print(e)


    for the_file in os.listdir(weatherFolder):
        file_path = os.path.join(weatherFolder, the_file)
        try:
            if os.path.isfile(file_path):
                1
                #os.unlink(file_path)

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
        #dr.getWeatherData()
        #dr.getNodeData()

    except Exception as e:
        tb.print_exc(e)
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
    print(len(dataSetTime))
    for i in range(len(dataSetTime)):
        if(dataSetTime[i - 168] == dataSetTime[i] - datetime.timedelta(days=7)):
            if(dataSetTime[i - 168*2] == dataSetTime[i] - datetime.timedelta(days=14)):

                previous1Watts.append(dataSetWattage[i-168])
                previous2Watts.append(dataSetWattage[i - 168*2])
                watts.append(dataSetWattage[i])
                adjustedTimeList.append(dataSetTime[i])
                adjustedTempList.append(dataSetTemp[i])
                adjustedSkyList.append(dataSetSky[i])

    data = ""
    if(len(adjustedSkyList) == 0):
        print("fuck")
        errorState()


    else:
        data = [(nodeId,srcId,adjustedTimeList[i], datetime.datetime.today(),
             watts[i],previous1Watts[i],adjustedTempList[i],
             previous2Watts[i], adjustedSkyList[i]) for i in range(len(adjustedTimeList))]
    logError("executing query")
    try:
        cursor.executemany(query, data)
        cnx.commit()
    except Exception as e:
        print(e)
        errorState()



    for the_file in os.listdir(chunksFolder):
        file_path = os.path.join(chunksFolder, the_file)
        try:
            if os.path.isfile(file_path):
                1
                #os.unlink(file_path)
        except Exception as e:
            print(e)
            logError(str(e))


    for the_file in os.listdir(weatherFolder):
        file_path = os.path.join(weatherFolder, the_file)

        try:

            if os.path.isfile(file_path):
                1
                #os.unlink(file_path)
                # elif os.path.isdir(file_path): shutil.rmtree(file_path)
        except Exception as e:
            print(e)
            logError(str(e))

populate()







