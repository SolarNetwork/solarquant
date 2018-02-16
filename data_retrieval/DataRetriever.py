from urllib2 import Request, urlopen
import json
import datetime
import os

directory = os.path.dirname(__file__)


# Class deals with API calls
class DataRetriever:
    defaultStartDate = datetime.datetime.strptime("2015-01-01 00:00", "%Y-%m-%d %M:%S")
    formatTime = datetime.datetime.strptime

    PREFIX = "https://data.solarnetwork.net"

    def __init__(self, node_id, src_id, start_date, end_date):

        self.nodeId = node_id
        self.srcId = src_id
        self.chunksFolder = os.path.join(directory, "chunks/")
        self.weatherFolder = os.path.join(directory, "weather/")
        start_date_i, end_date_i = self.get_interval()

        # if the difference between the last datum and the current date is too large, fail.
        if abs((datetime.datetime.strptime(end_date_i, "%Y-%m-%d %M:%S") - datetime.datetime.utcnow()).days) > 15:
            raise Exception

        # if no date range is defined, use default
        if (start_date is None) | (end_date is None):

            if (self.formatTime(start_date_i, "%Y-%m-%d %M:%S") < self.defaultStartDate):
                self.startDate = self.defaultStartDate
            else:
                self.startDate = self.formatTime(start_date_i, "%Y-%m-%d %M:%S")
            self.endDate = self.formatTime(end_date_i, "%Y-%m-%d %M:%S")

        else:
            self.startDate = self.formatTime(start_date, "%Y-%m-%d:%H:%M")
            self.endDate = self.formatTime(end_date, "%Y-%m-%d:%H:%M")

    @staticmethod
    def get_chunk_end_date(current, end_date, min_interval):

        out = current + datetime.timedelta(minutes=500 * min_interval)
        if out > end_date:
            return end_date
        else:
            return out

    # downloads all node datum within a range as JSON files
    def get_node_data(self):

        chunk_start = self.startDate
        while chunk_start < self.endDate:

            chunk_end = self.get_chunk_end_date(chunk_start, self.endDate, 20)
            start_string = datetime.datetime.strftime(chunk_start, "%Y-%m-%dT12%%3A00")
            end_string = datetime.datetime.strftime(chunk_end, "%Y-%m-%dT12%%3A00")

            request = Request(self.PREFIX + "/solarquery/api/v1/pub/datum/"
                                            "list?nodeId=" + self.nodeId + "&aggregation=ThirtyMinute&startDate=" +
                              start_string + "&endDate=" + end_string + "&sourceIds=" + self.srcId + "&max=5000000")

            try:
                response = urlopen(request)
                data = json.loads(response.read())

                # writes each request into json file with start date as string
                with open(self.chunksFolder + 'chunk_' + start_string + '.json', 'w') as outfile:

                    outfile.write(json.dumps(data, indent=4))

            except:
                print("Failed")
            chunk_start = chunk_end

    # downloads all weather datum within a range as JSON files
    def get_weather_data(self):

        chunk_start = self.startDate

        while chunk_start < self.endDate:
            # moves sliding window of time over chunks
            chunk_end = self.get_chunk_end_date(chunk_start, self.endDate, 30)
            start_string = datetime.datetime.strftime(chunk_start, "%Y-%m-%d")
            end_string = datetime.datetime.strftime(chunk_end, "%Y-%m-%d")

            request = Request(self.PREFIX + "/solarquery/api/v1/pub/location/datum/list?locationId=301025&sourceIds="
                                            "NZ%20MetService&offset=0&"
                                            "startDate=" + start_string + "&endDate=" + end_string)
            try:
                response = urlopen(request)
                data = json.loads(response.read())
                with open(self.weatherFolder + '/weather_' + start_string + '.json', 'w') as outfile:
                    outfile.write(json.dumps(data, indent=4))
            except:
                print("Failed")
            chunk_start = chunk_end

    # gets the range of datum for the node, from start date to end date.
    def get_interval(self):
        request = Request(self.PREFIX + "/solarquery/api/v1/pub/range/interval?nodeId={}".format(self.nodeId))
        data = ""
        try:
            response = urlopen(request)
            data = json.loads(response.read())

        except:
            print("Failed")

        return data['data']['startDate'], data['data']['endDate']
