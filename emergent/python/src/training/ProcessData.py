import os
import mysql.connector
import datetime as dt
import numpy as np
import math

cnx = mysql.connector.connect(user='solarquant', password='solarquant',
                              host='localhost',
                              database='solarquant')
cursor = cnx.cursor()


def getTrainingData(reqId):
    def getRequestParameters():
        query = ("SELECT NODE_ID, SOURCE_ID FROM training_requests WHERE REQUEST_ID = %s")
        cursor.execute(query, (reqId,))
        out = cursor.fetchall()[0]
        return out[0], out[1]

    nodeId, srcId = getRequestParameters()

    def getTrainingData():
        startDate = dt.datetime.utcnow() - dt.timedelta(weeks=24)
        endDate = dt.datetime.utcnow()
        query = "SELECT * FROM training_input WHERE NODE_ID = %s AND SOURCE_ID = %s AND DATE_CREATED BETWEEN %s and %s"
        cursor.execute(query, (nodeId, srcId, startDate, endDate))
        return cursor.fetchall()

    data = getTrainingData()

    outputs = []
    inputs = []
    mins = np.array([(float(i[2].hour) * 60 + float(i[2].minute)) for i in data])

    hourSin = [(math.sin(2 * math.pi * (i) / 1410) + 1) / 2 for i in mins]
    hourCos = [(math.cos(2 * math.pi * (i) / 1410) + 1) / 2 for i in mins]

    days = np.array([(float(i[2].timetuple().tm_yday)) for i in data])
    daySin = [(math.sin(2 * math.pi * (i) / 365) + 1) / 2 for i in days]
    dayCos = [(math.cos(2 * math.pi * (i) / 365) + 1) / 2 for i in days]

    dates = [i[2] for i in data]

    prev1Wattages = np.array([i[4] for i in data])
    prev2Wattages = np.array([i[5] for i in data])
    pressures = np.array([i[6] for i in data])
    humidities = np.array([i[7] for i in data])
    temps = np.array([i[8] for i in data])
    cloudy = np.array([float(i[9]) / 100 for i in data])
    windspeeds = np.array([i[10] for i in data])
    winddirs = np.array([i[11] for i in data])
    wattages = np.array([i[12] for i in data])

    def getMinMax(column):
        datestart = dt.datetime.today() - dt.timedelta(weeks=24)
        dateEnd = dt.datetime.today()
        query = ("SELECT MAX(" + column + "),MIN(" + column + ") FROM training_input "
                                                              "WHERE NODE_ID = %s AND SOURCE_ID = %s AND DATE_CREATED BETWEEN %s AND %s")

        cursor.execute(query, (nodeId, srcId, datestart, dateEnd))
        data = cursor.fetchall()[0]

        maxi = data[0]
        mini = data[1]
        return maxi, mini

    [maxi, mini] = getMinMax("TEMP")

    temps = [((i - mini) / (maxi - mini)) for i in temps]

    [maxi, mini] = getMinMax("PRESSURE")

    pressures = [((i - mini) / (maxi - mini)) for i in pressures]

    [maxi, mini] = getMinMax("HUMIDITY")
    humidities = [((i - mini) / (maxi - mini)) for i in humidities]

    [maxi, mini] = getMinMax("WATT_HOURS")

    prev1Wattages = [((i - mini) / (maxi - mini)) for i in prev1Wattages]
    prev2Wattages = [((i - mini) / (maxi - mini)) for i in prev2Wattages]
    wattages = [((i - mini) / (maxi - mini)) for i in wattages]
    [maxi, mini] = getMinMax("WIND_SPEED")
    windspeeds = [((i - mini) / (maxi - mini)) for i in windspeeds]

    inputs = [hourSin, hourCos, daySin, dayCos, prev1Wattages, prev2Wattages, pressures, humidities, temps, cloudy,
              windspeeds]
    outputs = [wattages]
    inputs = np.transpose(inputs, [1, 0])
    outputs = np.transpose(outputs, [1, 0])
    return inputs, outputs, dates
