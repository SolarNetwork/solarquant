
""" Compiles neural network model for prediction, and runs a single feed forward prediction using the network.

"""

from com.model import SolarQuantModel
import numpy as np
import os
import com.data.DataRetriever as dR
import mysql
import logging

logger = logging.getLogger('pylog1')

directory = os.path.dirname(__file__)

cnx = mysql.connector.connect(user='solarquant', password='solarquant',
                              host='localhost',
                              database='solarquant')
cursor = cnx.cursor()

def store_prediction(request_id, predicted_watt_hours, date):
    query = "DELETE FROM prediction_output WHERE REQUEST_ID = {}".format(request_id)

    cursor.execute(query)
    cnx.commit()

    data = [(int(request_id), float(predicted_watt_hours[i][-1]), None,date[i]) for i in range(len(predicted_watt_hours))]
    logger.info("Storing prediction info in database!")
    query = "INSERT INTO prediction_output VALUES ( %s,%s, %s,%s)"
    cursor.executemany(query, data)
    cnx.commit()

def predict(request_id):

    # builds/compiles model
    logger.info("Building model")
    model = SolarQuantModel.build_model()

    # gets formatted input data
    logger.info("Retrieving prediction input")
    [prediction_input, dates] = dR.get_prediction_data(request_id)
    node_id, src_id,_ = dR.get_prediction_request_info(request_id)

    filename = os.path.join(directory, '../../trained_models/{}_{}_model.h5'.format(str(node_id),src_id))

    # loads a pretrained model's weights

    model.load_weights(filename)

    logger.info("Running prediction")
    prediction = model.predict(prediction_input, verbose=0, batch_size=100)
    prediction = np.array(prediction)

    logger.info("Storing prediction output")
    store_prediction(request_id,prediction,dates)
