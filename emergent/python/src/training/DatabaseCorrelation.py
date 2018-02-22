import mysql
import os
import csv
cnx = mysql.connector.connect(user='solarquant', password='solarquant',
                              host='localhost',
                              database='solarquant')
cursor = cnx.cursor()
directory = os.path.dirname(__file__)

def store_correlation(request_id):

    file = os.path.join(directory, "../../test_out/out_{}.csv".format(request_id))

    dates = []
    predictions = []
    real = []
    with open(file, 'r') as f:
        reader = csv.DictReader(f, delimiter='	')
        for row in reader:
            predictions.append(row['Output_act_0'])
            dates.append(row['trial_name'])
            real.append(row['Output_targ_0'])

    data = [(int(request_id), float(real[i]), float(predictions[i]), dates[i]) for i in range(len(dates))]
    query = "INSERT INTO training_correlation VALUES (%s, %s, %s,%s)"
    cursor.executemany(query, data)
    cnx.commit()