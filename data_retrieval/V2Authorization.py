from datetime import datetime

import urllib
import base64
import hmac
import hashlib

class V2Authorization:

    # httpHeaders must be a dictionary, signedHeaderNames must be too, parameters must be dictionary
    def __init__(self, tokenId,httpMethod,requestPath,parameters,
                 httpHeaders, contentSHA256,
                 signedHeaderNames = None, date = datetime.now()):

        self.tokenId = tokenId
        self.httpMethod = httpMethod
        self.requestPath = requestPath
        self.parameters = parameters
        self.httpHeaders = httpHeaders
        self.signedHeaderNames = signedHeaderNames
        self.contentSHA256 = contentSHA256
        self.date = date


    def sorted_header_names(self):

        header_string = []
        header_string.append("Host")

        if "X-SN-Date" in self.httpHeaders:
            header_string.append("X-SN-Date")
        else:
            header_string.append("Date")

        if "Content-MD5" in self.httpHeaders:
            header_string.append("Content-MD5")

        if "Content-Type" in self.httpHeaders:
            header_string.append("Content-Type")

        if "Digest" in self.httpHeaders:
            header_string.append("Digest")

        if self.signedHeaderNames != None:
            for i in self.signedHeaderNames:
                header_string.append(i)

        for i,j in enumerate(header_string):
            header_string[i] = header_string[i].lower()

        header_string.sort()

        return header_string

    def encode_URI_component(self,val):
        return urllib.quote(val.encode("utf-8"))

    def query_parameters(self):
        result = []
        first = True

        params = dict(self.parameters)
        keys = params.keys()

        for key in keys:
            vals = params[key]
            for val in vals:
                if first:
                    first = False
                else:
                    result.append("&")
                result.append(self.encode_URI_component(key))
                result.append("=")
                result.append(self.encode_URI_component(val))
        return "".join(result)


    def canonical_headers(self, header_names):
        result = []
        for header_name in header_names:
            if "date" == header_name or "x-sn-date" == header_name:
                header_value = self.date.strftime("%Y%m%d")
            else:

                header_value = dict(self.httpHeaders)
                header_value = header_value.get(header_name).pop()

                #header_value = header_value.get(header_name)
            result.append(header_name)
            result.append(":")
            result.append(str(header_value).strip())
            result.append("\n")

        return "".join(result)

    def canonical_signed_header_names(self, sorted_header_names):
        return ";".join(sorted_header_names)

    def canonical_content_SHA(self):
        return str(self.contentSHA256).encode('hex')


    def compute_canonical_request_data(self,header_names):
        out_string = []
        out_string.append(self.httpMethod)
        out_string.append("\n")
        out_string.append(self.requestPath)
        out_string.append("\n")
        out_string.append(self.query_parameters())
        out_string.append("\n")
        out_string.append(self.canonical_headers(header_names))
        out_string.append(self.canonical_signed_header_names(header_names))
        out_string.append("\n")
        out_string.append(self.canonical_content_SHA())

        return "".join(out_string)


    def compute_signature_data(self, canonical_request):
        return  "SNWS2-HMAC-SHA256\n" +datetime.utcnow().isoformat() + "\n" + hashlib.sha256(canonical_request).hexdigest()




    def build(self,signing_key):

        header_names = self.sorted_header_names()
        canonical_req = self.compute_canonical_request_data(header_names)
        signature_data = self.compute_signature_data(canonical_req)
        signature = base64.b64encode(hmac.new(signing_key, signature_data, digestmod=hashlib.sha256).digest())
        signature = signature.encode('hex')
        out_string = []
        out_string.append(' ')
        out_string.append("SNWS2 Credential=")
        out_string.append(self.tokenId)
        out_string.append(",SignedHeaders=")
        out_string.append(";".join(header_names))
        out_string.append(",Signature=")
        out_string.append(signature)

        out_string = ''.join(out_string)

        return out_string



