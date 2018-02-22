import tensorflow as tf
import numpy as np
import DataProcessor as dp
from matplotlib import pyplot


nodeId = 205
source = "House"
startDate = "2016-01-12"
endDate = "2016-01-12"

feature_columns = [tf.feature_column.numeric_column("x", shape=[9])]

[dataSetTime,dataSetTemp,trainingDataX,trainingDataY,testDataX,testDataY]=dp.getParams(nodeId,source,startDate,endDate)

estimator = tf.estimator.DNNRegressor(
    feature_columns=feature_columns,
    hidden_units=[30, 30, 30],
    optimizer=tf.train.ProximalAdagradOptimizer(
      learning_rate=10,
      l1_regularization_strength=0.001
))
cX = 0
inp = trainingDataX
batchSize = 500
numBatches = len(trainingDataX)//batchSize

def input_fn():

    global cX
    if(cX > len(trainingDataX)):
        cX = 0
    batchX = trainingDataX[cX:cX + batchSize]
    batchY = trainingDataY[cX:cX + batchSize]
    print(cX)
    train_input_fn = tf.estimator.inputs.numpy_input_fn(
        x={"x": batchX},
        y=batchY,
        num_epochs=None,
        shuffle=True)
    cX += 1
    return train_input_fn
batches = []
for i in range(numBatches):
    batches.append(input_fn())

print(np.shape(trainingDataY))
print(np.shape(trainingDataX))
test_input_fn = tf.estimator.inputs.numpy_input_fn(
    x={"x": trainingDataX},
    y=trainingDataY,
    num_epochs=1,
    shuffle=False)

endLen = min(len(dataSetTemp), len(dataSetTime))
lengthTo = endLen - 400

pyplot.ion()
fig = pyplot.figure()
ax = fig.add_subplot(111)
for i in range(10000):
    fig.clear()
    estimator.train(input_fn=batches, steps=1)
    if(i%50 == 0):
        pyplot.plot(dataSetTime[:lengthTo], trainingDataY)
        predictions = list(estimator.predict(input_fn=test_input_fn))
        predictions = np.reshape(predictions, -1)
        out = []
        c = 0
        for i in predictions:
            # if(c < 6465):
            out.append(i['predictions'][:])
        pyplot.plot(dataSetTime[:lengthTo], out)
        fig.canvas.draw()


print("done")
accuracy_score = estimator.evaluate(input_fn=test_input_fn)
predictions = list(estimator.predict(input_fn=test_input_fn))


#print(predictions)
print(accuracy_score)

predictions = np.reshape(predictions,-1)
out = []
c = 0
for i in predictions:
    #if(c < 6465):
    out.append(i['predictions'][:])
    #c+=1
#print(out)
#out = list(reversed(out))

#print (out)


pyplot.plot(dataSetTime[:lengthTo], trainingDataY)
pyplot.plot(dataSetTime[:lengthTo], out)
pyplot.show()


