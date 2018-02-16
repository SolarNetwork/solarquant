"""Imports historic data from OpenWeatherMap for training
"""
import os
import datetime as dt
import mysql.connector
import json
import traceback as tb

dir = os.path.dirname(__file__)
chunksFolder = os.path.join(dir, "chunks/")

dataFile = open(chunksFolder + "/akWeather.json", 'r').read()
data = json.loads(dataFile)
cnx = mysql.connector.connect(user='solarquant', password='solarquant',
                              host='localhost',
                              database='solarquant')
cursor = cnx.cursor()

def updateWeather():

    #fills in all 30 minute intervals with data.
    def interpolate(data):
        out = []
        for i in range(len(data)-1) :
            diff = data[i+1][0] - data[i][0]
            mins = divmod(diff.total_seconds(),60*30)

            for j in range(int(mins[0])):
                out.append([data[i][0]  + dt.timedelta(minutes=30*j)] + data[i][1:])

        return out

    def addToDatabase():
        prevDate = dt.datetime.fromtimestamp(5)
        inp = []
        for i in data:
            temp = []

            main = i["main"]
            wind = i["wind"]
            date = dt.datetime.strptime(i["dt_iso"],"%Y-%m-%d %H:%M:%S +0000 UTC")

            # grabs all useful data from JSON
            temp.append(date)
            temp.append(main["temp"])
            temp.append(wind["deg"])
            temp.append(wind["speed"])
            temp.append(i["clouds"]["all"])
            temp.append(main["pressure"])
            temp.append(main["humidity"])

            if(date > prevDate):
                inp.append(temp)
                prevDate = date
        inp = interpolate(inp)

        query = "INSERT INTO owm_data VALUES (%s, %s, %s, %s, %s, %s,%s)"
        try:
            cursor.executemany(query, inp)
        except Exception as e:
            1
            tb.print_exc(e)

        cnx.commit()
    addToDatabase()
updateWeather()









