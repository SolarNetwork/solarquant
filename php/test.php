<?php
$file = './log.txt';
file_put_contents($file, "meme", FILE_APPEND);
		$theUri = "/solarquery/api/v1/pub/location/datum/list?locationId=301025&sourceIds=NZ%20MetService&offset=0&max=10&startDate=".$chunkStartDate."&endDate=".$chunkEndDate;

		//start with a blank messageDigest
		$messageDigest = "";
		
		//now create the message contents
		$messageDigest .= "GET\n";
		$messageDigest .= "\n";
		$messageDigest .= "\n";
		$messageDigest .= $gmRightNow."\n";
		$messageDigest .= $theUri;
		
		#hash the message content
		$hashedContent = base64_encode(hash_hmac('sha1', $messageDigest, $theSecret, true));

		//create the header array for the CURL call
		$headerArrayElement1 = "X-SN-Date: ".$gmRightNow;
		
		//dynamic token Works 
		$headerArrayElement2 = "Authorization: SolarNetworkWS $theToken:".$hashedContent;
	

		//$headerArrayElement2 = "Authorization: SolarNetworkWS ".$theToken.":".$hashedContent;
		$headerArray = array($headerArrayElement1,$headerArrayElement2);
		
		$theProtocol = "HTTP";
		$theHost = "solarnet.org/np/";
		//create the URL to use in the header of the curl call
		$theUrl = $theProtocol.$theHost.$theUri;
		
		//create the raw cURL command line
		$theCurlCall = "curl -H '".$headerArray[0]."' -H '".$headerArray[1]."' '".$theUrl."'";
		file_put_contents($file, "$theCurlCall", FILE_APPEND);
		
?>
