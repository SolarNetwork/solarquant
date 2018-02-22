from urllib2 import Request, urlopen, URLError
import json

import tensorflow as tf
import numpy as np

import datetime


class DataRetriever():
    PREFIX = "https://data.solarnetwork.net"
    def __init__(self, nodeId, srcId, startDate, endDate):
        self.nodeId = nodeId
        self.srcId = srcId
        self.startDate = datetime.datetime.strptime(startDate,"%Y-%m-%d")
        self.endDate = datetime.datetime.strptime(endDate,"%Y-%m-%d")
    def getChunkEndDate(self, current, endDate, minInterval):
        out = current + datetime.timedelta(minutes=500*minInterval)
        if(out > endDate):
            return endDate
        else:
            return out

    def getNodeData(self):

        chunkStart = self.startDate
        while (chunkStart < self.endDate):

            chunkEnd = self.getChunkEndDate(chunkStart,self.endDate, 10)

            startString = datetime.datetime.strftime(chunkStart,"%Y-%m-%dT12%%3A00")

            endString = datetime.datetime.strftime(chunkEnd, "%Y-%m-%dT12%%3A00")
            print(startString)
            print(endString)

            request = Request(self.PREFIX + "/solarquery/api/v1/pub/datum/"
                              "list?nodeId="+self.nodeId+"&aggregation=Hour&startDate="+
                              startString+"&endDate="+endString+"&sourceIds="+self.srcId+"&max=5000000")
            data = ""

            try:
                response = urlopen(request)
                data = json.loads(response.read())

                with open('../chunks/chunk_'+ startString+'.json', 'w') as outfile:
                    outfile.write(json.dumps(data, indent=4))
            except:
                print("Failed")
            chunkStart = chunkEnd



    def getWeatherData(self):

        chunkStart = self.startDate
        while (chunkStart < self.endDate):

            chunkEnd = self.getChunkEndDate(chunkStart, self.endDate, 30)

            startString = datetime.datetime.strftime(chunkStart,"%Y-%m-%d")
            endString = datetime.datetime.strftime(chunkEnd, "%Y-%m-%d")
            print(startString)
            print(endString)
           # print()
            request = Request(self.PREFIX+"/solarquery/api/v1/pub/location/datum/list?locationId=301025&sourceIds="
                              "NZ%20MetService&offset=0&"
                              "startDate=" + startString + "&endDate=" + endString)
            data = ""
            try:
                response = urlopen(request)
                data = json.loads(response.read())
                with open('../weather/weather_'+ startString+'.json', 'w') as outfile:
                    outfile.write(json.dumps(data, indent=4))
            except:
                print("Failed")
            chunkStart = chunkEnd
