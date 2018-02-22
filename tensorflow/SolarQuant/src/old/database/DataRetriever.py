import mysql.connector
import datetime
import numpy as np

cnx = mysql.connector.connect(user='solarquant', password='solarquant',
                              host='localhost',
                              database='solarquant')
cursor = cnx.cursor()

def getData(nodeId, startDate, endDate):
    query = ("SELECT * FROM tensorflow_training_input "
             "WHERE NODE_ID = %s AND DATE_CREATED BETWEEN %s AND %s")

    startDate = datetime.datetime.strptime(startDate,"%Y-%m-%d")
    endDate = datetime.datetime.strptime(endDate,"%Y-%m-%d")
    cursor.execute(query, (nodeId,startDate, endDate))
    data = cursor.fetchall()
    dates = np.array([i[1] for i in data])

    datesNorm = np.array([float(i[1].hour) for i in data])
    daysNorm = np.array([float(i[1].timetuple().tm_yday) for i in data])
    #print(daysNorm)
    wattages = np.array([i[2] for i in data])
    prev1Wattages = np.array([i[3] for i in data])
    temps = np.array([i[4] for i in data])
    prev2Wattages = np.array([i[5] for i in data])
    sky = np.array([i[6] for i in data])
    #print(sky)



    weatherInput = []
    weatherWords = ["Cloudy", "Partly cloudy", "Fine", "Few showers", "Showers", "Windy", "Rain", "Thunder"]
    for i in range(len(sky)):
        temp = []
        for j in range(len(weatherWords)):
            if(weatherWords[j] == sky[i]):
                temp.append(1)
            else:
                temp.append(0)
        weatherInput.append(temp)

    #print(weatherInput)


  #  prev1Wattages = (prev1Wattages - np.min(wattages)) / (np.max(wattages) - np.min(wattages))
    #prev2Wattages = (prev2Wattages - np.min(wattages)) / (np.max(wattages) - np.min(wattages))
   # wattages = 2*((wattages - np.min(wattages)) /(np.max(wattages) - np.min(wattages)))-1
   # temps = (temps - np.min(temps)) / (np.max(temps) - np.min(temps))
   # wattages = np.transpose([wattages],[1,0])



    inputs = [datesNorm,daysNorm,prev1Wattages,prev2Wattages,temps]
    weatherInput = np.transpose(weatherInput,[1,0])
    #inputs.append(weatherInput)
    inputs = [np.concatenate([inputs,weatherInput],0)]

    inputs = np.transpose(inputs, [2,0,1])


    inputs = [[prev1Wattages,temps]]
    inputs = np.transpose(inputs, [2, 0, 1])
    return  dates, inputs, wattages


