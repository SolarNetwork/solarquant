import os
import mysql.connector
import datetime as dt
import ProcessData as process
dir = os.path.dirname(__file__)
print(dir)

cnx = mysql.connector.connect(user='solarquant', password='solarquant',
                              host='localhost',
                              database='solarquant')
cursor = cnx.cursor()

inpFormat = "\t%Input[2:{},0]"
outpFormat = "\t%Output[2:{},0]"

firstFormat = "<2:{},1>"

def generate(reqId):

    def getRequestParameters():
        query = ("SELECT NODE_ID, SOURCE_ID FROM training_requests WHERE REQUEST_ID = %s")
        cursor.execute(query,(reqId,))
        out = cursor.fetchall()[0]
        return out[0], out[1]

    nodeId, srcId = getRequestParameters()



    input, output,dates = process.getTrainingData(reqId)


    numInputs = len(input[0])
    numOutputs = len(output[0])
    file = os.path.join(dir, "./inputs/input_{}".format(reqId))


    def writeHeader():
        with open(file, 'w') as f:
            f.write("_H:\t$Name")

            for i in range(numInputs):
                if (i == 0):
                    f.write(inpFormat.format( i) + firstFormat.format(numInputs))
                else:
                    f.write(inpFormat.format(i))
            for i in range(numOutputs):
                if (i == 0):
                    f.write(outpFormat.format(i) + firstFormat.format(numOutputs))
                else:
                    f.write(outpFormat.format(i))

    def writeBody():
        with open(file, 'a') as f:
            for i in range(len(input)):
                f.write("\n")
                f.write("_D:\t")
                f.write("{}\t".format(dates[i]))
                for j in input[i]:
                    f.write(str(j)+"\t")
                for j in output[i]:
                    f.write(str(j))



    def addToFile():
        writeHeader()
        writeBody()



    addToFile()