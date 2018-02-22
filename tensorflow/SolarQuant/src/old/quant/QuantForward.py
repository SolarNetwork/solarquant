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

numHidden = 1000
numHidden2 = 1000

numInputs = 2
numOutputs = 1
timesteps = 1

resultSet = []

w = tf.Variable(tf.truncated_normal([numHidden2, numOutputs]))

b = tf.Variable(tf.random_normal([numOutputs]))

lstm = rnn.LSTMCell(numHidden, state_is_tuple=True)
lstm2 = rnn.LSTMCell(numHidden2, state_is_tuple=True)

lstm3 = rnn.LSTMCell(numHidden2, state_is_tuple=True)
cell = rnn.MultiRNNCell([lstm, lstm2])
def LSTM(X):

    output, state = tf.nn.dynamic_rnn(cell, X, dtype=tf.float32)


    output = tf.transpose(output, (1, 0, 2))

    out = tf.tanh(tf.matmul(output[-1],w) + b)


    return out


X = tf.placeholder(tf.float32, [None,timesteps,numInputs])
Y = tf.placeholder(tf.float32, [None,numOutputs])
pred = LSTM(X)

error = tf.reduce_mean(tf.square(pred-Y))
#error =

starter_learning_rate = 0.0001
global_step = tf.Variable(1, trainable=False)
lr = tf.train.exponential_decay(starter_learning_rate, global_step,
                                         100, 0.99, staircase=True)
optimize = tf.train.AdamOptimizer(lr).minimize(error,
                                                   global_step=global_step)

def loadModel(sess):
    saver = tf.train.Saver()

    saver.restore(sess, "../../../trained_models/solar_model_3.ckpt")
    return sess

def saveModel(sess):
    saver = tf.train.Saver()
    timestamp = datetime.datetime.today().strftime("%d")
    save_path = saver.save(sess, "../../../trained_models/solar_model_3.ckpt")
    #print("Model saved in file: %s" % save_path)
def saveBestModel(sess):
    saver = tf.train.Saver()
    timestamp = datetime.datetime.today().strftime("%d")
    save_path = saver.save(sess, "../../../trained_models/solar_model_best.ckpt")

def loadBestModel(sess):
    saver = tf.train.Saver()
    saver.restore(sess, "../../../trained_models/solar_model_best.ckpt")
    return sess
