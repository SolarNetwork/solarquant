import DataRetriever as dr
import numpy as np


def get_data(reqId):
    lookback = 3
    [x_train, y_train, x_test, y_test, dates_train,dates_test] = dr.get_data(reqId)

    y_train = np.array(y_train)
    x_train = np.array(x_train)

    num_inp = x_train.shape[1]

    new_start = x_train.shape[0]%lookback
    x_train_new = np.reshape(x_train[new_start:], [-1,lookback,num_inp])
    y_train_new = np.reshape(y_train[new_start:],[-1,lookback,1]);

    x_test = np.array(x_test)
    y_test = np.array(y_test)

    new_start = x_test.shape[0] % lookback
    x_test_new = np.reshape(x_test[new_start:], [-1, lookback, num_inp])
    y_test_new = np.reshape(y_test[new_start:], [-1, lookback, 1]);

    return x_train_new, y_train_new, x_test_new, y_test_new, dates_train,dates_test

get_data(175)