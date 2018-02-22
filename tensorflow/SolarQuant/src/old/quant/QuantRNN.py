from urllib2 import Request, urlopen, URLError
import json
import tensorflow as tf
import numpy as np

from DataRetriever import DataRetriever
from matplotlib import pyplot
import datetime
import os
from tensorflow.contrib import rnn
from tensorflow.contrib import nn

numHidden = 1024
numHidden2 = 512
numHidden3 = 256
numInputs = 9
numOutputs = 1
timesteps = 2

resultSet = []
w = tf.Variable(tf.truncated_normal([numHidden3, numOutputs]))

b = tf.Variable(tf.random_normal([numOutputs]))
lstm = rnn.LSTMCell(numHidden, state_is_tuple=True)
lstm2 = rnn.LSTMCell(numHidden2, state_is_tuple=True)

lstm3 = rnn.LSTMCell(numHidden3, state_is_tuple=True)
cell = rnn.MultiRNNCell([lstm, lstm2, lstm3])
def LSTM(X):

    output, state = tf.nn.dynamic_rnn(lstm3, X, dtype=tf.float32)


    output = tf.transpose(output, (1, 0, 2))

    out = tf.sigmoid(tf.matmul(output[-1],w) + b)

    return out