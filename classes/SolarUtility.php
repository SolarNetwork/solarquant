<?php
class SolarUtility {

    var $dbHost = "127.0.0.1";
    var $dbName = "solarquant";
    var $dbUser = "solarquant";
    var $dbPassword = "solarquant";
    var $localAbsolutePath = "/var/www/html/solarquant/";
    var $tempWriteablePath = "/var/www/html/solarquant/ini/";
    var $generateNewPatternChockFile = "/var/www/html/solarquant/ini/generateNewPatternChockFile";
    var $createTrainingFileChockFile = "/var/www/html/solarquant/ini/createTrainingFileChockFile";
    var $createEmergentScriptChockFile = "/var/www/html/solarquant/ini/createEmergentScriptChockFile";
    var $runEmergentScriptChockFile = "/var/www/html/solarquant/ini/runEmergentScriptChockFile";
    var $checkEmergentChockFile = "/var/www/html/solarquant/ini/checkEmergentChockFile";
    var $refreshForecastsChockFile = "/var/www/html/solarquant/ini/refreshForecastsChockFile";
    var $dbLink;
    
    function connectToDB()
    {
    	$theUtility = new SolarUtility;
    	$this->dbLink = new mysqli($this->dbHost,$this->dbUser,$this->dbPassword,$this->dbPassword);
    }
    
	function trace($varName,$varValue)
	{
	
		echo($varName .":".$varValue."<br>");
	
	}

	
function cleanQuotes( $value )
{
    if( get_magic_quotes_gpc() )
    {
          $value = stripslashes( $value );
    }
    //check if this function exists
    if( function_exists( "mysqli_real_escape_string" ) )
    {
          //$value = mysql_real_escape_string( $value );
          $value = $this->dbLink->real_escape_string($value);
    }
    //for PHP version < 4.3.0 use addslashes
    else
    {
          $value = addslashes( $value );
    }
    return $value;
}
//returns a two item array
function getSolarNetAuthentication($nodeId)
{
	
	echo "in getSolarNetAuthentication nodeId :".$nodeId."<br />";
	
	$nodeAuthentication = array();
	$nodeIdMarker = 0;
	
	// Parse with sections
	$authenticationArray = parse_ini_file($this->localAbsolutePath."ini/SolarNet.ini", true);

	
	foreach ($authenticationArray as $itemName => $itemValue)
	{
		
		//echo "for each itemName :".$itemName."<br />"; 
		
		//print_r($itemValue);
			
		//echo "<br> sizeof itemValue:".sizeof($itemValue)."<br />"; 
			
		if ($itemName == "nodes")
		{
			
			foreach ($itemValue as $nodeName => $nodesArray)
			{
				//$nodesArray = $itemValue;		
				$sizeOfNodesArray = sizeof($nodesArray);
			
				//echo "nodeName :".$nodeName."<br />";
				//echo "nodesArray :".$nodesArray."<br />";
				//$sizeOfNodeValue = sizeof($nodeValue);
				
				//echo "<br> sizeOfNodesArray:".$sizeOfNodesArray."<br />"; 
				
				$n = 0;
				$foundNodeId = false;
				
				foreach ($nodesArray as $nodeNum => $thisNodeId)
				{
					
					echo "thisNodeId :".$thisNodeId."<br />";
					echo "nodeId :".$nodeId."<br />";
					
					
					if ($thisNodeId == $nodeId)
					{
						//this n is the one to save
						$nodeIdMarker = $n;
						$foundNodeId = true;
						
						echo "found the nodeId at :".$n."<br />";
							
					}
					else
					{
						echo "NOT found the nodeId at :".$n."<br />";
					}
					
					//increment
					$n++;
					
					/*
					
					$n = 0;
					$foundNodeId = false;
					
					while ($n < $sizeOfNodesArray) & ($foundNodeId == false)
					{
						if ($nodesArray[$n] == $nodeId)
						{
							//this n is the one to save
							$nodeIdMarker = $n;
							$foundNodeId = true;
							
						}
						//increment
						$n++
					}
					
			
					//increment
					$n++
					
					*/
						
				}
		
			}
			//echo "itemValue 0 0 :".$itemValue[0][0]."<br />"; 
			//echo "itemName :".$itemName."<br />";
			//echo "itemValue :".$itemValue."<br />"; 
			//echo "itemValue[0] :".$itemValue[0]."<br />";
			//echo "node[0] :".$node[0]."<br />";
			//echo "nodes[0] :".$nodes[0]."<br />";
			
			//echo "is_array nodesArray:".is_array($nodesArray)."<br />"; 
			//echo "sizeOfNodesArray :".$sizeOfNodesArray."<br />"; 
			
			/*
			
			
			 */
		}
		elseif ($itemName == "tokens")
		{
			
			//echo "now in item tokens <br />";
			
			foreach ($itemValue as $tokenName => $tokensArray)
			{
				foreach ($tokensArray as $tokenNum => $thisToken)
				{
					
					//echo "thisToken :".$thisToken."<br />";
					
				}
				
			}

			//push the right token
			$nodeAuthentication[] = $tokensArray[$nodeIdMarker];
		}
		elseif ($itemName == "secrets")
		{
			echo "now in item secrets <br />";
			
			foreach ($itemValue as $secretName => $secretsArray)
			{
				foreach ($secretsArray as $secretNum => $thisSecret)
				{
					
					//echo "thisSecret :".$thisSecret."<br />";
					
				}
				
			}

			//push the right token
			$nodeAuthentication[] = $secretsArray[$nodeIdMarker];		
		}		
		
		
	} //end foreach
	
	echo "nodeAuthentication[0] :".$nodeAuthentication[0]."<br />";
	echo "nodeAuthentication[1] :".$nodeAuthentication[1]."<br />";
	
	
	//hand back a two item array
	return $nodeAuthentication;

}

	//uses the input curl arguments to return an text string
	function cURLdownload($url, $headerArray) 
	{ 
		
	echo "in cURLdownload url :".$url."<br />";
	echo "in cURLdownload headerArray :".$headerArray[0]."<br />"; 
	echo "in cURLdownload headerArray :".$headerArray[1]."<br />"; 
	echo "in cURLdownload file :".$file."<br />"; 
	
	 //ob_start();
	 
	  echo "after ob_start <br />"; 
	
	//initialize curl
  	 $ch = curl_init(); 
 
  	 echo "after curl_init :".$ch."<br />"; 
    
  	 	 
  	 	 if( !curl_setopt($ch, CURLOPT_URL, $url) ) 
  	 	 { 
  	 	 	 //fclose($fp); // to match fopen() 
  	 	 	 curl_close($ch); // to match curl_init() 
  	 	 	 return "FAIL: curl_setopt(CURLOPT_URL)<br>"; 
  	 	 } 
  	 	 
  	 	 
  	 	 //try to set the curl file
  	 	 //if( !curl_setopt($ch, CURLOPT_FILE, $fp) ) return "FAIL: curl_setopt(CURLOPT_FILE)"; 
  	 	 
  	 	 //echo "after CURLOPT_FILE <br />"; 
  	 	 

  	 	 
  	 	 //try to set the output
  	 	  if( !curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ) return "FAIL: curl_setopt(CURLOPT_RETURNTRANSFER)"; 
  	 	 
  	 	 
  	 	 
  	 	 
  	 	 //if headerArray is submitted
  	 	 if ( strlen(trim($headerArray)) > 0)
  	 	 {
  	 	 
  	 	 	  //try to set the header output flag
  	 	 	  if( !curl_setopt($ch, CURLOPT_HEADER, 0) ) return "FAIL: curl_setopt(CURLOPT_HEADER)"; 
  	 	 
  	 	 	 echo "before CURLOPT_HEADER <br />"; 
  	 	 	 
  	 	 	 //try to write the header of the message
  	 	 	 if( !curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray ) ) return "FAIL: curl_setopt(CURLOPT_HTTPHEADER)"; 
  	 	 
  	 	 	 echo "after CURLOPT_HTTPHEADER <br />"; 
  	 	 
  	 	 }
  	 	 else
  	 	 {
  	 	 	echo "did not set headerArray <br />";  
  	 	 }
  	 	 
  	 	 //execute the curl call
  	 	 $curlOutput = curl_exec($ch);
  	 	   	 	  	 	 
   	 	 
  	 	 // echo "after curlOutput :".$curlOutput."<br />"; 
  	 	  
  	 	 //ob_end_clean(); 
  	 	 
  	 	 //close everything up
  	 	 curl_close($ch); 
  	 	 //fclose($fp); 
  	 	 return $curlOutput; 
  	 	 
 	 
 	} 
 	
 	function read_file($file, $lines) {
    //global $fsize;
    $handle = fopen($file, "r");
    $linecounter = $lines;
    $pos = -2;
    $beginning = false;
    $text = array();
    while ($linecounter > 0) {
        $t = " ";
        while ($t != "\n") {
            if(fseek($handle, $pos, SEEK_END) == -1) {
                $beginning = true; 
                break; 
            }
            $t = fgetc($handle);
            $pos --;
        }
        $linecounter --;
        if ($beginning) {
            rewind($handle);
        }
        $text[$lines-$linecounter-1] = fgets($handle);
        if ($beginning) break;
    }
    fclose ($handle);
    return array_reverse($text);
}
 	
function getGenerateNewPatternSetStatus()
{
	
	//default is running
	$theStatus = "running";
	
	//check the file system for the chock file
	$fp3 = fopen($this->generateNewPatternChockFile, 'r');
	
	//if it does exist yet
	if ($fp3 == true)
	{
		$theStatus = "stopped";
	}
	
	return $theStatus;
	
	
}

function getCreateTrainingFilesStatus()
{
	
	//default is running
	$theStatus = "running";
	
	//check the file system for the chock file
	$fp3 = fopen($this->createTrainingFileChockFile, 'r');
	
	//if it does exist yet
	if ($fp3 == true)
	{
		$theStatus = "stopped";
	}
	
	return $theStatus;
	
	
}

function getCreateEmergentScriptChockFileStatus()
{
	
	//default is running
	$theStatus = "running";
	
	//check the file system for the chock file
	$fp3 = fopen($this->createEmergentScriptChockFile, 'r');
	
	//if it does exist yet
	if ($fp3 == true)
	{
		$theStatus = "stopped";
	}
	
	return $theStatus;
	
	
}

function getRunEmergentScriptChockFileStatus()
{
	
	//default is running
	$theStatus = "running";
	
	//check the file system for the chock file
	$fp3 = fopen($this->runEmergentScriptChockFile, 'r');
	
	//if it does exist yet
	if ($fp3 == true)
	{
		$theStatus = "stopped";
	}
	
	return $theStatus;
	
	
}

function getCheckEmergentChockFileStatus()
{
	
	//default is running
	$theStatus = "running";
	
	//check the file system for the chock file
	$checkEmergentChockFileFilePointer = fopen($this->checkEmergentChockFile, 'r');
	
	//if it does exist yet
	if ($checkEmergentChockFileFilePointer == true)
	{
		$theStatus = "stopped";
	}
	
	//release this file pointer
	fclose($checkEmergentChockFileFilePointer);
		
	return $theStatus;
	
	
}

function getRefreshForecastsChockFileStatus()
{
		//default is running
	$theStatus = "running";
	
	//check the file system for the chock file
	$refreshForecastsChockFileFilePointer = fopen($this->refreshForecastsChockFile, 'r');
	
	//if it does exist yet
	if ($refreshForecastsChockFileFilePointer == true)
	{
		$theStatus = "stopped";
	}
	
	//release this file pointer
	fclose($refreshForecastsChockFileFilePointer);
		
	return $theStatus;
	
	
	
}

//FUNCTIONS FOR FUTURE WEATHER DATA
function populateWeatherDatum($aWeatherDatum, $forecastSet)
{
	//map each of the values to a weatherDatum property
	$aWeatherDatum->temperatureCelsius = $forecastSet['temperature'];
	$aWeatherDatum->humidity = $forecastSet['humidity'];
	$aWeatherDatum->whenLogged =  $forecastSet['from'];	
	$aWeatherDatum->statusId = 1;
	$theWeatherCondition = $forecastSet['symbol'];
	
	
	//skyConditions needs to be selected from known values based on symbol and precipitation
	if (trim(strtoupper($theWeatherCondition)) == "PARTLYCLOUD")
	{
		$aWeatherDatum->weatherCondition = "PARTLY CLOUDY";
	}
	elseif (trim(strtoupper($theWeatherCondition)) == "CLOUD")
	{
		$aWeatherDatum->weatherCondition = "CLOUDY";
	}

	
	elseif (trim(strtoupper($theWeatherCondition)) == "DRIZZLE")
	{
		$aWeatherDatum->weatherCondition = "DRIZZLE";
	}
	elseif (trim(strtoupper($theWeatherCondition)) == "LIGHTRAIN")
	{
		$aWeatherDatum->weatherCondition = "LIGHT RAIN";
	}
	elseif (trim(strtoupper($theWeatherCondition)) == "LIGHTRAINSUN")
	{
		$aWeatherDatum->weatherCondition = "LIGHT RAIN";//TODO see if a batter mapping
	}
	elseif (trim(strtoupper($theWeatherCondition)) == "DRIZZLESUN")
	{
		$aWeatherDatum->weatherCondition = "DRIZZLE";//TODO see if a batter mapping
	}
	elseif (trim(strtoupper($theWeatherCondition)) == "SUN")
	{
		$aWeatherDatum->weatherCondition = "SUNNY";
	}	
	elseif (trim(strtoupper($theWeatherCondition)) == "LIGHTCLOUD")
	{
		$aWeatherDatum->weatherCondition = "PARTLY CLOUDY";
	}
	elseif (trim(strtoupper($theWeatherCondition)) == "RAIN")
	{
		$aWeatherDatum->weatherCondition = "RAIN";
	}
	elseif (trim(strtoupper($theWeatherCondition)) == "RAINSUN")
	{
		$aWeatherDatum->weatherCondition = "RAIN SHOWER";
	}
	else
	{
		//log an logentry
		$theError = new SolarError;
		$theError->module = "cron_generateEnergyForecasts";
		$theError->details = "new weatherCondition: ".$theWeatherCondition;
		$theError->add();
	}
	
	/*
	//LightRainSun

	*/
	
	
	//for now set this to the same value TODO differentiate        
	$aWeatherDatum->skyConditions = $aWeatherDatum->weatherCondition;
	
	
	
	//return
	return $aWeatherDatum;
}

function search_array($arrayToSearch, $field, $value)
{
	$matchDiscovered = false;
	foreach ($arrayToSearch as $key => $row)
	{	
		if ($row[$field] == $value)
		{
			echo "MATCH DISCOVERED FOR: ".$row[$field]."<br>";
			return $matchDiscovered = true;
		}
		else
		{
			$matchDiscovered = false;
		}	  	
	}		
	return $matchDiscovered;
}

function array_sort($array, $on, $order=SORT_ASC)
{
	echo "entering array sort";
	//var_dump($array);
	
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                        }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }

        //var_dump($sortable_array);
        
        foreach ($sortable_array as $k => $v) {
        	//echo "k:".$k."<br>";
        	//echo "v:".$v."<br>";
            $new_array[] = $array[$k];
            
           // echo "new_array k:". var_dump($new_array[$k])."<br>";
        }       
    }

  // echo "out array_sort<br>";
   // var_dump($new_array);
    echo "exiting array sort";
    return $new_array;

}



//function servername()
//{
//		$this->dbServerName = "127.0.0.1";
//		return $servername;
//}

}
?>