import time
from keras.layers.core import Dense
import numpy as np
from keras.layers import TimeDistributed
from keras.layers.recurrent import LSTM,GRU
from keras.models import Sequential
import tensorflow as tf

import keras

def cVar(y_true, y_pred):
	return tf.sqrt(tf.reduce_sum(tf.square(y_true-y_pred)))


def build_model():
    model = Sequential()
    model.add(LSTM(1024, return_sequences=True, activation="relu",
                   input_dim=13))  # returns a sequence of vectors of dimension 32
    model.add(LSTM(364, activation="relu", return_sequences=True))  # returns a sequence of vectors of dimension 32  # return a single vector of dimension 32
    model.add(TimeDistributed(Dense(1, activation='linear')))

    model.compile(loss='mse',
                  optimizer='adam',
                  metrics=['accuracy'])
    return model
