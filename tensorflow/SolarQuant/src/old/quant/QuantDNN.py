from urllib2 import Request, urlopen, URLError
import json
import tensorflow as tf
import numpy as np

from DataRetriever import DataRetriever
from matplotlib import pyplot
import datetime
import os
from tensorflow.contrib import rnn


numHidden = 20
numHidden2 = 20
numInputs = 9
numOutputs = 1

def run(X):
    b3 = tf.Variable(tf.truncated_normal([numOutputs]))

    w3 = tf.Variable(tf.truncated_normal([numHidden, numOutputs]))

    b2 = tf.Variable(tf.truncated_normal([numHidden]))

    w2 = tf.Variable(tf.truncated_normal([numHidden, numHidden]))

    w = tf.Variable(tf.truncated_normal([numInputs, numHidden]))

    b = tf.Variable(tf.truncated_normal([numHidden]))

    hidden = tf.nn.sigmoid(tf.matmul(X, w) + b)

    hidden2 = tf.nn.sigmoid(tf.matmul(hidden, w2) + b2)

    output = tf.nn.sigmoid(tf.matmul(hidden2, w3) + b3)

    return output

X = tf.placeholder(tf.float32, [None, numInputs])
Y = tf.placeholder(tf.float32, [None])

pred = run(X)
error = tf.sqrt(tf.reduce_mean(tf.square(tf.subtract(Y, pred))))
starter_learning_rate = 0.01
global_step = tf.Variable(1, trainable=False)
lr = tf.train.exponential_decay(starter_learning_rate, global_step,
                                         1000, 0.999, staircase=True)
opt = tf.train.AdamOptimizer(lr)

optimize = opt.minimize(error,global_step)


def loadModel(sess):
    saver = tf.train.Saver()

    saver.restore(sess, "../../../trained_models/solar_model_3DNN.ckpt")
    return sess

def saveModel(sess):
    saver = tf.train.Saver()
    timestamp = datetime.datetime.today().strftime("%d")
    save_path = saver.save(sess, "../../../trained_models/solar_model_3DNN.ckpt")
    #print("Model saved in file: %s" % save_path)
def saveBestModel(sess):
    saver = tf.train.Saver()
    timestamp = datetime.datetime.today().strftime("%d")
    save_path = saver.save(sess, "../../../trained_models/solar_model_bestDNN.ckpt")

def loadBestModel(sess):
    saver = tf.train.Saver()
    saver.restore(sess, "../../../trained_models/solar_model_bestDNN.ckpt")
    return sess