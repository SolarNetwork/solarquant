"""Data retrieval module grabs formatted prediction/training data from the database, normalizes it using min/max norm
and then returns the data.


"""

import mysql.connector
import datetime
import numpy as np
import math
import logging

logger = logging.getLogger('pylog1')
cnx = mysql.connector.connect(user='solarquant', password='solarquant',
                              host='localhost',
                              database='solarquant')
cursor = cnx.cursor()

# retrieve training request metadata
def get_request_info(request_id):
    query = ("SELECT NODE_ID, SOURCE_ID FROM {} "
             "WHERE REQUEST_ID = {}")

    cursor.execute(query.format("training_requests", request_id))

    try:
        data = cursor.fetchall()
        data = data[0]
    except:
        cursor.execute(query.format("prediction_requests", request_id))
        data = cursor.fetchall()[0]
    node_id = data[0]
    source_id = data[1]
    return node_id, source_id

def get_date_range(id):
    query = ("SELECT START_DATE,END_DATE FROM {} "
             "WHERE REQUEST_ID = {}")

    cursor.execute(query.format("training_requests", id))

    try:
        data = cursor.fetchall()
        data = data[0]
    except:
        cursor.execute(query.format("prediction_requests", id))
        data = cursor.fetchall()[0]
    start = data[0]
    end = data[1]
    return start, end

def is_dynamic(request_id):
    query = ("SELECT DYNAMIC FROM training_requests WHERE REQUEST_ID = {}")
    cursor.execute(query.format(request_id))
    data = cursor.fetchall()[0]
    return data[0] == 1

def get_min_max(request_id, column):
    node_id, src_id = get_request_info(request_id)
    date_start = datetime.datetime.today() - datetime.timedelta(weeks=24)
    date_end = datetime.datetime.today()
    query = ("SELECT MAX(" + column + "),MIN(" + column + ") FROM {} "
                                                          "WHERE NODE_ID = %s AND SOURCE_ID = %s AND DATE_CREATED "
                                                          "BETWEEN %s AND %s")

    cursor.execute(query.format('training_input'), (node_id, src_id, date_start, date_end))
    try:
        data = cursor.fetchall()[0]
    except:
        cursor.execute(query.format('prediction_input'), (node_id, src_id, date_start, date_end))
        data = cursor.fetchall()[0]

    maxi = data[0]
    mini = data[1]
    return maxi, mini

# retrieve prediction request metadata
def get_prediction_request_info(request_id):
    query = ("SELECT NODE_ID, SOURCE_ID, DATE_REQUESTED FROM prediction_requests "
             "WHERE REQUEST_ID = {}")

    query = query.format(request_id)

    cursor.execute(query)

    data = cursor.fetchall()[0]

    node_id = data[0]
    source_id = data[1]
    date_requested = data[2]
    return node_id, source_id,date_requested

# normalizes data and arranges in a numpy array
def process_data(data, request_id):
    logger.info("Started normalization of data")
    dates = [i[2] for i in data]
    mins = np.array([(float(i[2].hour) * 60 + float(i[2].minute)) for i in data])

    # gets cos and sin components of hour of the day
    hour_sin = [math.sin(2 * math.pi * i / 1410) for i in mins]
    hour_cos = [math.cos(2 * math.pi * i / 1410) for i in mins]

    # gets cos and sin components of day of the year
    days = np.array([(float(i[2].timetuple().tm_yday)) for i in data])
    day_sin = [math.sin(2 * math.pi * i / 365) for i in days]
    day_cos = [math.cos(2 * math.pi * i / 365) for i in days]

    # gets cos and sin components of day of the week
    day_of_week_norm = np.array([(float(i[2].isoweekday())) for i in data])
    day_of_week_cos = [math.cos(2 * math.pi * i / 6 - 1) for i in day_of_week_norm]
    day_of_week_sin = [math.sin(2 * math.pi * i / 6 - 1) for i in day_of_week_norm]

    # pulling remaining data out of select

    prev1_wattages = np.array([i[4] for i in data])
    prev2_wattages = np.array([i[5] for i in data])
    pressures = np.array([i[6] for i in data])
    humidities = np.array([i[7] for i in data])
    temps = np.array([i[8] for i in data])
    windspeeds = np.array([i[10] for i in data])
    winddirs = np.array([i[11] for i in data])

    # normalizing data
    cloudy = np.array([2 * float(i[9]) / 100 - 1 for i in data])

    prev1_wattages = [float(i) for i in prev1_wattages]
    prev2_wattages = [float(i) for i in prev2_wattages]
    temps = [float(i) for i in temps]
    pressures = [float(i) for i in pressures]
    humidities = [float(i) for i in humidities]

    [maxi, mini] = get_min_max(request_id, "TEMP")
    temps = [2 * ((i - mini) / (maxi - mini)) - 1 for i in temps]

    [maxi, mini] = get_min_max(request_id, "WATT_HOURS")
    prev1_wattages = [2 * ((i - mini) / (maxi - mini)) - 1 for i in prev1_wattages]
    prev2_wattages = [2 * ((i - mini) / (maxi - mini)) - 1 for i in prev2_wattages]

    [maxi, mini] = get_min_max(request_id, "PRESSURE")
    pressures = [2 * ((i - mini) / (maxi - mini)) - 1 for i in pressures]

    [maxi, mini] = get_min_max(request_id, "HUMIDITY")
    humidities = [2 * ((i - mini) / (maxi - mini)) - 1 for i in humidities]

    [maxi, mini] = get_min_max(request_id, "WIND_SPEED")
    windspeeds = [2 * ((i - mini) / (maxi - mini)) - 1 for i in windspeeds]

    [maxi, mini] = get_min_max(request_id, "WIND_DIR");
    winddirs = [(i - mini) / (maxi - mini) for i in winddirs]

    wind_sin = [math.sin(i) for i in winddirs]
    wind_cos = [math.cos(i) for i in winddirs]

    inputs = [[hour_cos, hour_sin, day_of_week_cos, day_of_week_sin, temps, cloudy,humidities, windspeeds, wind_sin, wind_cos]]#, temps, humidities, windspeeds, wind_sin, wind_cos
                  #, cloudy, temps, pressures, humidities]]

    inputs = np.transpose(inputs, [2, 0, 1])

    try:
        wattages = np.array([i[12] for i in data])
        wattages = np.transpose([wattages], [1, 0])
    except:
        wattages = []

    return inputs, dates, wattages


# gets the data for training, normalizes it, and returns in correct format
def get_data(request_id):
    node_id, source_id = get_request_info(request_id)

    if is_dynamic(request_id):
        start_date,end_date = get_date_range(request_id)
    else:
        start_date, _ = get_date_range(request_id)
        end_date = datetime.datetime.today()
    logger.info("Retrieving training input")
    # grabs from database
    query = ("SELECT * FROM training_input "
             "WHERE NODE_ID = %s AND SOURCE_ID = %s AND DATE_CREATED BETWEEN %s AND %s")

    cursor.execute(query, (node_id, source_id, start_date, end_date))
    data = cursor.fetchall()

    inputs, dates, wattages = process_data(data, request_id)

    x = np.transpose(inputs,[1,0,2])[0]
    lengthTo = len(x) - 1000

    x_train = x[:lengthTo]
    y_train = wattages[:lengthTo]
    x_test = x[lengthTo:]
    y_test = wattages[lengthTo:]

    dates_train = dates[:lengthTo]
    dates_test = dates[lengthTo:]
    return x_train, y_train, x_test, y_test, dates_train,dates_test

# gets prediction data from database, normalizes it and returns in a correct format.
def get_prediction_data(request_id):
    node_id, source_id, date_requested = get_prediction_request_info(request_id)
    query = ("SELECT * FROM prediction_input "
             "WHERE NODE_ID = %s AND SOURCE_ID = %s AND DATE_PREDICTING BETWEEN %s AND %s")
    start_date =datetime.datetime.today()
    end_date = start_date + datetime.timedelta(weeks=1)
    cursor.execute(query, (node_id, source_id, start_date, end_date))
    data = cursor.fetchall()
    [inputs, dates, _] = process_data(data, request_id)
    inputs = inputs.transpose([1,0,2])[0]
    return inputs, dates



