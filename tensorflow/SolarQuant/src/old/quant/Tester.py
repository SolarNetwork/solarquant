
import numpy as np
import datetime as dt
resultWeatherSet = ["Fine","Fine","Fine","Showers"]

weatherInput = []

weatherWords = ["Cloudy", "Partly cloudy", "Fine", "Few showers", "Showers", "Windy"]
print(weatherInput)

print(float(dt.datetime.today().weekday())/7)

for item in resultWeatherSet:
    for x in range(1):
        temp = []
        for word in weatherWords:
            print(word)
            if (item == word):
                temp = np.append(temp, 1)
            else:
                temp = np.append(temp,0)

        weatherInput= np.append(weatherInput,temp)

weatherInput = np.reshape(weatherInput, (-1,6))
print(weatherInput)
