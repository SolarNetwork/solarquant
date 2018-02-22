import mysql
import os
import csv
cnx = mysql.connector.connect(user='solarquant', password='solarquant',
                              host='localhost',
                              database='solarquant')
cursor = cnx.cursor()
directory = os.path.dirname(__file__)

def store_correlation(request_id):

    file = os.path.join(directory, "../../prediction_out/out_{}.csv".format(request_id))

    dates = []
    predictions = []

    with open(file, 'r') as f:
        reader = csv.DictReader(f, delimiter='	')
        for row in reader:
            predictions.append(row['Output_act_0'])
            dates.append(row['trial_name'])

    data = [(int(request_id), float(predictions[i]), None, dates[i]) for i in range(len(dates))]
    query = "INSERT INTO prediction_output VALUES (%s, %s, %s,%s)"
    cursor.executemany(query, data)
    cnx.commit()