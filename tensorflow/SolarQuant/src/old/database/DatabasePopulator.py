
import mysql.connector
import os
import json
import datetime
import argparse

from old.quant.DataRetriever import DataRetriever





argParser = argparse.ArgumentParser()
argParser.add_argument("-n", "--nodeid", dest="nodeId", help="ID for request",
                  metavar = "ID", required = True)

argParser.add_argument("-i", "--srcid", dest="srcId", help="ID for request",
                  metavar = "ID", required = True)

argParser.add_argument("-s", "--startdate", dest="startDate", help="start date",
                  metavar = "start", required = True)

argParser.add_argument("-e", "--enddate", dest="endDate", help="end date",
                  metavar = "end", required = True)

args = argParser.parse_args()




folder = '../chunks/'
for the_file in os.listdir(folder):
    file_path = os.path.join(folder, the_file)
    try:
        if os.path.isfile(file_path):
            os.unlink(file_path)
        #elif os.path.isdir(file_path): shutil.rmtree(file_path)
    except Exception as e:
        print(e)

folder = '../weather/'
for the_file in os.listdir(folder):
    file_path = os.path.join(folder, the_file)
    try:
        if os.path.isfile(file_path):
            os.unlink(file_path)
            # elif os.path.isdir(file_path): shutil.rmtree(file_path)
    except Exception as e:
        print(e)



global folder

dr = DataRetriever(args.nodeId,args.srcId,args.startDate, args.endDate)

dr.getNodeData()
dr.getWeatherData()

cnx = mysql.connector.connect(user='solarquant', password='solarquant',
                              host='localhost',
                              database='solarquant')
cursor = cnx.cursor()



query = ("INSERT INTO tensorflow_training_input "
         "VALUES (%s,%s, %s, %s, %s, %s, %s, %s)")

resultSet = []
for fname in sorted(os.listdir("../chunks")):
    dataFile = open("../chunks/" + fname, 'r').read()
    resultSet.append(json.loads(dataFile))
resultWeatherSet = []
for fname in sorted(os.listdir("../weather")):
    dataFile = open("../weather/" + fname, 'r').read()
    resultWeatherSet.append(json.loads(dataFile))

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
        #print(c)
        dataSetTemp.append(baseWeather[c]['temp'])
        dataSetWattage.append(item['wattHours'])
        dat = datetime.datetime.strptime(item['created'], "%Y-%m-%d %H:%M:%S.%fZ")
        dataSetTime.append(dat)
        dataSetDay.append(float(dat.timetuple().tm_yday))
        dataSetSky.append(baseWeather[c]['sky'])
        c+=2
print(dataSetTemp)
previous1Watts = []
previous2Watts = []
watts = []
adjustedTimeList = []
adjustedTempList = []
adjustedSkyList = []
for i in range(len(dataSetTime)):
    if(dataSetTime[i - 168] == dataSetTime[i] - datetime.timedelta(days=7)):
        if(dataSetTime[i - 168*2] == dataSetTime[i] - datetime.timedelta(days=14)):
            previous1Watts.append(dataSetWattage[i-168])
            previous2Watts.append(dataSetWattage[i - 168*2])
            watts.append(dataSetWattage[i])
            adjustedTimeList.append(dataSetTime[i])
            adjustedTempList.append(dataSetTemp[i])
            adjustedSkyList.append(dataSetSky[i])

#print(watts)
data = [(205,"House",adjustedTimeList[i],watts[i],previous1Watts[i],adjustedTempList[i], previous2Watts[i], adjustedSkyList[i]) for i in range(len(adjustedTimeList))]

cursor.executemany(query, data)

cnx.commit()


for the_file in os.listdir(folder):
    file_path = os.path.join(folder, the_file)
    try:
        if os.path.isfile(file_path):
            os.unlink(file_path)
        #elif os.path.isdir(file_path): shutil.rmtree(file_path)
    except Exception as e:
        print(e)

folder = '../weather/'
for the_file in os.listdir(folder):
    file_path = os.path.join(folder, the_file)
    try:
        if os.path.isfile(file_path):
            os.unlink(file_path)
            # elif os.path.isdir(file_path): shutil.rmtree(file_path)
    except Exception as e:
        print(e)