
"""Runs training on a dataset for a specific Solar Node source.
Then proceeds to evaluate the model and store evaluation values in database.
Stores trained model in the trained_models folder."""

from com.model import SolarQuantModel, LSTM2
import os
from keras.callbacks import CSVLogger
import mysql.connector
from keras.callbacks import EarlyStopping, ModelCheckpoint
import com.data.DataRetriever as dataRetriever
import com.data.IntermediateProcess as ip
import logging
import numpy as np
import datetime

logger = logging.getLogger('pylog1')
cnx = mysql.connector.connect(user='solarquant', password='solarquant',
                              host='localhost',
                              database='solarquant')
cursor = cnx.cursor()
# stores accuracy and error in database
def store_evaluation_values(request_id, loss, acc):

    data = (request_id, float(loss), float(acc))
    query = "INSERT INTO training_evaluation VALUES (%s, %s, %s)"

    cursor.execute(query, data)
    cnx.commit()

def store_correlation(request_id, watt_hours, predicted_watt_hours, date):
    data = [(int(request_id), float(watt_hours[i][-1]), float(predicted_watt_hours[i][-1]), date[i]) for i in range(len(watt_hours))]
    query = "INSERT INTO training_correlation VALUES (%s, %s, %s,%s)"
    cursor.executemany(query, data)
    cnx.commit()

def store_weights_location(node, src,filename):
    data = (int(node),src, filename, datetime.datetime.now())
    query = "INSERT INTO trained_models VALUES (%s, %s, %s,%s)"
    print(len(filename))
    cursor.execute(query, data)
    cnx.commit()



def get_parameters(request_id):
    query = ("SELECT MAX_EPOCHS, BATCH_SIZE FROM training_parameters WHERE REQUEST_ID = {} ".format(request_id))
    cursor.execute(query)
    data = cursor.fetchall()
    data = data[0]

    return data[0], data[1]

def train(request_id):
    directory = os.path.dirname(__file__)
    node_id, src_id = dataRetriever.get_request_info(request_id)

    filename = os.path.join(directory, '../../trained_models/' + str(node_id) + '_' + str(src_id) + '_model.h5')
    try:
        #os.unlink(filename)
        pass
    except:
        pass


    # not implemented yet, need to make the web interface add them to database first
    try:
        [epochs, batch_size] = get_parameters(request_id)
        if(epochs < 1):
            epochs = 200
        if( batch_size < 1):
            batch_size = 100
    except:
        epochs = 200
        batch_size = 100
        pass

    # define a checkpoint - a trigger that saves the model under certain conditions ever so often
    check = ModelCheckpoint(filename, monitor='val_loss', verbose=0, save_best_only=True, save_weights_only=True,
                            mode='auto', period=1)
    csv_filename = os.path.join(directory,
                            '../../../../../logs/progress_logs/{}_log.csv'.format(request_id))
    csv_logger = CSVLogger(csv_filename, append=True, separator=',')

    # define early stopping - if the model is not improving / getting worse, stop the training to save time.
    stop = EarlyStopping(monitor='val_loss', min_delta=0, patience=100, verbose=0, mode='auto')

    logger.info("Building model")
    model = SolarQuantModel.build_model()

    [x_train, y_train, x_test, y_test, dates_train,dates_test] = dataRetriever.get_data(request_id)

    # train!!!
    logger.info("Fitting model")
    model.fit(
        x_train,
        y_train,
        batch_size=batch_size,
        nb_epoch=epochs,
        validation_data = [x_test, y_test],
        verbose=0, callbacks=[check, stop,csv_logger])

    logger.info("Completed fitting")
    request_id = str(request_id).strip()
    model.load_weights(filename)

    # evaluate the model just trained, and store evaluation data
    logger.info("Evaluating model")
    [loss, acc] = model.evaluate(x_test, y_test, verbose=0, batch_size=1)
    try:
        store_evaluation_values(request_id, acc, loss)
    except:
        pass
    prediction = model.predict(x_test, verbose=0, batch_size=1)

    store_weights_location(node_id,src_id,filename)
    logger.info("Storing correlation")

    # store correlation graph data as CSV
    store_correlation(request_id,y_test,prediction,dates_test)

