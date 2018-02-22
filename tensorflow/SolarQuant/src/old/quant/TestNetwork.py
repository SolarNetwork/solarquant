import tensorflow as tf
import numpy as np
from tensorflow.contrib import rnn



X = tf.placeholder(tf.float32, [1,25])
Y = tf.placeholder(tf.float32, [1,1])

w = tf.Variable(tf.truncated_normal([25, 25]))

w2 = tf.Variable(tf.truncated_normal([25, 1]))

b = tf.Variable(tf.random_normal([25]))

b2 = tf.Variable(tf.random_normal([1]))

hidden = tf.sigmoid(tf.matmul(X,w) + b);

out = tf.sigmoid(tf.matmul(hidden,w2) + b2);

error = tf.reduce_mean(tf.square(out-Y));

opt = tf.train.AdagradOptimizer(1)

optimize = opt.minimize(error)

with tf.Session() as sess:

    sess.run(tf.initialize_all_variables())
    vert = [[1,0,0,0,0],
            [1,0,0,0,0],
            [1,0,0,0,0],
            [1,0,0,0,0],
            [1,0,0,0,0]];

    horiz = [[1,1,1,1,1],
             [0,0,0,0,0],
             [0,0,0,0,0],
             [0,0,0,0,0],
             [0,0,0,0,0]];

    diag = [[1,0,0,0,0],
            [0,1,0,0,0],
            [0,0,1,0,0],
            [0,0,0,1,0],
            [0,0,0,0,1]];

    inps = []
    inps.append(np.reshape(vert,[1,25]).tolist())
    inps.append(np.reshape(horiz,[1,25]).tolist())
    inps.append(np.reshape(diag,[1,25]).tolist())
    targs = [[0/3],[1.0/3],[2.0/3]]

    print(inps[0])


    for j in range(2000):
        x = 0
        for i in inps:
            [_out,_error,_] = sess.run([out,error,optimize], feed_dict={X: i, Y: [targs[x]]})
            print(_error)
            #print(x)
            #print(targs[x])
            x+=1

    for i in inps:
        _out = sess.run([out], feed_dict={X: i})
        for j in _out:
            print(j*3 + 1)
            print("")

