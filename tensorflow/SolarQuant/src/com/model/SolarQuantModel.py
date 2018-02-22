
"""This network is a basic 3 layer RELU network with dropout and linear activation at the end.
This has proven to be the most effective network model, as it converges fastest and does not overfit.

"""

from keras.layers.core import Dense, Dropout
from keras.models import Sequential
import keras
import tensorflow as tf


def cVar(y_true, y_pred):
	return tf.sqrt(tf.reduce_sum(tf.square(y_true-y_pred)))

def build_model():

    # create and fit the network
    model = Sequential()
    model.add(Dense(1024, activation=keras.activations.relu, input_dim=10))
    model.add(Dropout(0.5))

    model.add(Dense(624, activation=keras.activations.relu))
    model.add(Dropout(0.5))
    model.add(Dense(1, activation="linear"))

    model.compile(loss="mae", optimizer="adam", metrics=["acc"])
    return model
