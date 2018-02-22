from V2Authorization import V2Authorization



httpHeaders = {"host": {"solarnet"}}
parameters = {}
signedHeaderNames = {}

signingKey = ""
token = ""

auth = V2Authorization(token,"GET","https://data.solarnetwork.net/solaruser/api/v1/sec/nodes/meta/321",parameters,
                        httpHeaders, signedHeaderNames)

print(auth.build(signingKey))
