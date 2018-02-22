import tensorflow as tf
import numpy as np

from matplotlib import pyplot
import old.database.DataRetriever as dp
import QuantForward as qf

class NetworkTrainer():
    best = 10
    epochs = 1000
    sets = 1
    def train(self, nodeId, source,startDate, endDate):

        def plotTest():
            [outp, errTest] = sess.run([qf.pred, qf.error], feed_dict={qf.X: testDataX, qf.Y: testDataY})
            [_out] = sess.run([qf.pred], feed_dict={qf.X: trainingDataX})
            #print("test error = ")
            #print(np.mean(errTest))
            #outp = 2 * (outp - min(_out)) / (max(_out) - min(_out)) - 1
            #outp = 2 * (outp - min(outp)) / (max(outp) - min(outp)) - 1
            pyplot.plot(dataSetTime[lengthTo:endLen], testDataY)
            pyplot.plot(dataSetTime[lengthTo:endLen], outp)

            #if (np.mean(errTest)< self.best):
                #qf.saveBestModel(sess)
                #self.best = np.mean(errTest)

        def plotTrained():
            [_out, _error2] = sess.run([qf.pred, qf.error], feed_dict={qf.X: trainingDataX, qf.Y: trainingDataY})
            [outp] = sess.run([qf.pred], feed_dict={qf.X: testDataX})

            print("training error = ")
            print(np.mean(_error2))

            if(_error2 < self.best):
                self.best = _error2
                print("best yet!")
                qf.saveBestModel(sess)
            _out = np.reshape(_out, (-1))
            #print("test error = ")
            #print(np.mean(errTest))
            #_out = 2 * (_out - min(_out)) / (max(_out) - min(_out)) - 1
            #_out = 2*(_out - min(_out))/(max(_out) - min(_out)) -1
            pyplot.plot(dataSetTime[0:lengthTo], trainingDataY)
            pyplot.plot(dataSetTime[0:lengthTo], _out)

        with tf.Session() as sess:

            #sess= qf.loadBestModel(sess)
            #sess = qf.loadModel(sess)
            pyplot.ion()
            fig = pyplot.figure()
            ax = fig.add_subplot(111)


            [dataSetTime,trainingDataX, trainingDataY] \
                = dp.getData(nodeId,startDate,endDate)
            dataSetTemp = dataSetTime

            for i in trainingDataY:
                print(i)



            endLen = len(trainingDataX)
            lengthTo = endLen - 200

            testDataX = trainingDataX[lengthTo:endLen]
            testDataY = trainingDataY[lengthTo:endLen]
            trainingDataX = trainingDataX[0:lengthTo]
            trainingDataY = trainingDataY[0:lengthTo]
            batchSize = 1
            numBatches = len(trainingDataX)//batchSize

            errSum = 0.0
            for set in range(self.sets):
                sess.run(tf.global_variables_initializer())
                for i in range(self.epochs):
                    cX = 0
                    for j in range(numBatches):
                        #print("hi")
                        batchX = trainingDataX[cX:cX+batchSize]
                        batchY = trainingDataY[cX:cX+batchSize]
                        cX += batchSize

                        [_out,_error,_] = sess.run([qf.pred,qf.error,qf.optimize],
                                                 feed_dict={qf.X:batchX, qf.Y:batchY})
                       # if(endLen - cX < 500):
                            #for x in range(1):
                                    #nice = sess.run([qf.optimize],
                                      #  feed_dict={qf.X: batchX, qf.Y: batchY})

                        if(j%100 == 0):
                            [errTest] = sess.run([qf.error], feed_dict={qf.X: testDataX,
                                 qf.Y: testDataY})
                            fig.clear()
                            plotTest()
                            plotTrained()
                            fig.canvas.draw()
                           # if(np.mean(errTest) < self.best):
                                #self.best = np.mean(errTest)
                               # print("new best=")
                               # print(self.best)



                        errSum+= _error

                    if (i % 10 == 0):
                        #fig.clear()
                       # plotTest()
                       # plotTrained()
                        #fig.canvas.draw()
                        qf.saveModel(sess)

            #sess = qf.loadBestModel(sess)
            print(np.shape(testDataX))
            print(np.shape(testDataY))
            [outp,errTest] = sess.run([qf.pred, qf.error], feed_dict={qf.X: testDataX, qf.Y:testDataY})
            outp = np.reshape(outp, (-1))

            [_out, _error2] = sess.run([qf.pred, qf.error], feed_dict={qf.X: trainingDataX, qf.Y: trainingDataY})
            _out = np.reshape(_out, (-1))

            #outp = 2 * (outp - min(_out)) / (max(_out) - min(_out)) - 1
            #outp = 2 * (outp - min(outp)) / (max(outp) - min(outp)) - 1

           # _out = 2*(_out - min( _out))/(max( _out) - min( _out)) -1
            pyplot.plot(dataSetTime[0:lengthTo], trainingDataY)
            pyplot.plot(dataSetTime[0:lengthTo], _out)

            pyplot.plot(dataSetTime[lengthTo:endLen], testDataY)
            pyplot.plot(dataSetTime[lengthTo:endLen], outp)

            print(outp.sum())
            print((testDataY).sum())
            pyplot.show(block=True)







