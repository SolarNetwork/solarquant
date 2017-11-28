<?php

//echo("in node.php<br>");

//require_once "/var/www/html/solarquant/classes/yo.inc";

//echo("after yo import<br>");

//imports
#require_once "/var/www/html/solarquant/classes/WeatherDatum.php";
#require_once "/var/www/html/solarquant/classes/SolarUtility.php";

require_once "/var/www/html/solarquant/classes/WeatherDatum.php";
require_once "/var/www/html/solarquant/classes/SolarUtility.php";
require_once "/var/www/html/solarquant/classes/SolarError.php";

//echo("in node.php after imports 2<br>");

class Node
{

    var $id;
    var $location;
    var $wcIdentifier;
    var $timeZone;
    var $latitude;
    var $longitude;
    var $totalWatts;
    var $scalingFactor;
    var $isSubscribedForTraining;
    var $subscribedSourceIds;
    var $weatherNodeId;
    var $maxAmps;
    var $dbLink;
    
    function connectToDB()
    {
    	$theUtility = new SolarUtility;
    	$this->dbLink = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
    }
    
    function constructFromRow($row)
    {
    	$this->id = $row["node_id"];
    	$this->location = $row["location"];
		$this->wcIdentifier = $row["wc_identifier"];
        $this->timeZone = $row["time_zone"];
        $this->latitude = $row["latitude"];
        $this->longitude = $row["longitude"];
		$this->totalWatts = $row["total_watts"];
		$this->scalingFactor = $row["scaling_factor"];
		$this->isSubscribedForTraining = $row["is_subscribed_for_training"];
		$this->weatherNodeId = $row["weather_node_id"];
		$this->subscribedSourceIds = $row["subscribed_source_ids"];

    }
   
   
    function constructFromId()
    {
    	
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    
    	    	// setup sql
		$sql = "select node_id, location, wc_identifier, time_zone, latitude, longitude, total_watts, scaling_factor, is_subscribed_for_training, subscribed_source_ids, weather_node_id from node where node_id = ".$this->id;
		
		//echo("node constructFromId sql:". $sql. "<br>");
		
		//create utility
		$theUtility = new SolarUtility;

		// execute sql 
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node constructFromId sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		// loop through results 
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
			$this->constructFromRow($row);
		}
    
    
    }
    function getMaxAmps()
    {
    
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	
    	// TODO make sure we are getting max amps for the right sourceId
		$sql = "select max(amps) as max_amps from `consumption_datum` where node_id = ".$this->id;
		
		echo("node getMaxAmps sql:". $sql. "<br>");
		
		//create utility
		$theUtility = new SolarUtility;

		// execute sql 
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node constructFromId sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		// loop through results 
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
			$this->maxAmps = $row["max_amps"];
		}
    
    
    }   
  function listAll($displayMode, $defaultNodeId, $nodeType)
    {
       	
    	//debug
    	//echo("in Node:ListAll before connectToDB<br>");
    	//echo("before dbLink:". $this->dbLink. "<br>");
    	
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	
    
    if ($displayMode == "fullPage"){

			//table of entities
			echo("<table cellpadding='15' cellspacing='15' class='table table-striped' border='0'>\n");

			echo("<tr class='solar4' bgcolor='#ffffff'>");
		
			echo("<td align='center'>\n");
				echo ("NODEID");
			echo("</td>\n");
			
			echo("<td align='center'>\n");
				echo ("LOCATION");
			echo("</td>\n");
		
			echo("<td align='center'>\n");
				echo ("TIMEZONE");
			echo("</td>\n");

			echo("<td align='center'>\n");
				echo ("TRAINING");
			echo("</td>\n");
			
			echo("<td align='center'>\n");
				echo ("ACTION");
			echo("</td>\n");

			echo("</tr>\n");

		}
		elseif ($displayMode == "selectBox")
		{
			echo("<select name='nodeId' size='1'>\n");
			
			//if -1
			if ($defaultNodeId <= 0)
			{
				echo("<option value='0'>Other\n");
			}
			
		}
		elseif ($displayMode == "checkBox")
		{
			
		}
		
		if ($nodeType == "all")
		{
		//setup the sql 
		$sql = "select node_id, location, wc_identifier, time_zone, latitude, longitude, is_subscribed_for_training from node order by location asc";
		}
		elseif ($nodeType == "actual")
		{
			$sql = "select node_id, location, wc_identifier, time_zone, latitude, longitude, is_subscribed_for_training from node where node_type_id = 1 order by location asc";
		}
		elseif ($nodeType == "virtual")
		{
			$sql = "select node_id, location, wc_identifier, time_zone, latitude, longitude, is_subscribed_for_training from node where node_type_id = 2 order by location asc";
		}
		
		//debug
		//echo("in Node:ListAll sql:". $sql. "<br>");
		
		//create utility
		//$theUtility = new SolarUtility;
		
		//execute the sql 
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("listAll node select sql failed:".$sql."<br>");
		$result = $this->dbLink->query($sql);
		
		//debug
		//echo("in Node:ListAll after result<br>");
		
		$result->data_seek(0);
		
		//echo("in Node:ListAll after data_seek<br>");
		
		//setup vars
		$toggle = 0;
		$theColor = "#BFBFBF";

		//loop through results
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
		
						
						
			//instantiate object
			$theNode = new Node;
			$theNode->constructFromRow($row);

			if ($displayMode == "fullPage"){

			//echo("<tr bgcolor=".$theColor.">");
			echo("<tr class='solar4'>");

				echo("<td align='center'>\n");
					echo ($theNode->id);
				echo("</td>\n");
			
				echo("<td align='center'>\n");
					echo ($theNode->location);
				echo("</td>\n");
			
				echo("<td align='center'>\n");
					echo ($theNode->timeZone);
				echo("</td>\n");

				echo("<td align='center'>\n");
					echo ($theNode->isSubscribedForTraining);
				echo("</td>\n");
				
				echo("<td align='center'>\n");

					

					echo(" <a href='nodeAction.php?function=edit&nodeId=");
					echo($theNode->id);
					echo("'><button type='button' class='btn btn-success'>Edit</button></a>\n");

					echo(" <a href='nodeAction.php?function=viewProgress&nodeId=");
					echo($theNode->id);
					echo("'><button type='button' class='btn btn-success'>Progress</button></a>\n");
					
					echo(" <a href='nodeAction.php?function=createSinglePattern&nodeId=");
					echo($theNode->id);
					echo("'><button type='button' class='btn btn-success'>Add Pattern</button></a>\n");

					echo(" <a href='nodeAction.php?function=refreshSources&nodeId=");
					echo($theNode->id);
					echo("'><button type='button' class='btn btn-info'>Refresh Sources</button></a>\n");
					
					echo(" <a href='nodeAction.php?function=delete&nodeId=");
					echo($theNode->id);
					echo("'><button type='button' class='btn btn-danger'>Delete</button></a>\n");
					
					
				echo("</td>\n");	
			
				echo("</tr>\n");
			}
			elseif ($displayMode == "selectBox"){


				if ($theNode->id == $defaultNodeId)
				{
					echo("<option value='".$theNode->id."' selected>".$theNode->location.":".$theNode->id."\n");
				}
				elseif ($theNode->id != $defaultNodeId) {
					echo("<option value='".$theNode->id."'>".$theNode->location.":".$theNode->id."\n");
				}
			}
			elseif ($displayMode == "checkBox")
			{
				
				
				if ($theNode->id == $defaultNodeId)
				{
					echo("<span class='solar4'>".$theNode->location."(".$theNode->id.") </span> <input type='checkbox' checked value='".$theNode->id."' name='nodes[]'><br>");
				}
				elseif ($theNode->id != $defaultNodeId)
				{
					echo("<span class='solar4'>".$theNode->location."(".$theNode->id.") </span> <input type='checkbox'  value='".$theNode->id."' name='nodes[]'><br>");
				}
				
			}
			
		
		} //end while 

		if ($displayMode == "fullPage"){
				echo("</table>\n");
		}
		elseif ($displayMode == "selectBox"){
			echo("</select>\n");
		}

    
    }
    function getWattHours($thisHour,$nextHour)
    {
    	
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	
    		//getwatt hour data for this hour
    		$getWattHourSQL = "SELECT pv_volts, pv_amps FROM power_datum where node_id = ".$this->id." and when_logged > '".$thisHour."' and when_logged < '".$nextHour."' order by when_logged asc";
    	
    		echo("getWattHourSQL:" . $getWattHourSQL."<br><br>");
    		
    				//create utility
		$theUtility = new SolarUtility;
    	
    		// execute sql 
			//$wattHourResult = mysql_db_query($theUtility->dbName,"$getWattHourSQL") or die ("getWattHourSQL failed");
			$wattHourResult = $this->dbLink->query($getWattHourSQL);
			$wattHourResult->data_seek(0);
		
			//$totalWattMinuteRows = mysql_num_rows($wattHourResult);
			$totalWattMinuteRows = mysqli_num_rows($wattHourResult);
			

			echo "$totalWattMinuteRows watt minute rows between ".$thisHour." and ".$nextHour." <br> \n"; 
			
    		//tally up all watt hours
			$totalWattMinutes = 0;
			//while ($row = mysql_fetch_array ($wattHourResult))
			while ($row = $wattHourResult->fetch_assoc())
			{
				$totalWattMinutes += ($row["pv_volts"] * $row["pv_amps"]);
			}
				
			echo("totalWattMinutes:" . $totalWattMinutes."<br><br>");
			
		$totalWattHours = $totalWattMinutes/60;
		
		return $totalWattHours;
    }
    
    
function processWeatherData()
{
	
}
function getOffsetString($whenLogged, $timeZone)
{
	
	echo("in getOffsetString<br><br>");
	
	//set up timzones
	$dateTimeZoneRemote = new DateTimeZone($timeZone);
	$dateTimeZoneUTC = new DateTimeZone("UTC");
	
	echo("after dateTimeZoneUTC<br><br>");
	
	//generate dates for comparison
	$dateTimeRemote = new DateTime($whenLogged, $dateTimeZoneRemote);
	$dateTimeUTC = new DateTime($whenLogged, $dateTimeZoneUTC);
	
	$offSetHours = ($dateTimeZoneRemote->getOffset($dateTimeRemote)/3600);
	
	echo("offSetHours:" . $offSetHours."<br><br>");
	
	if ($offSetHours > 0)
	{
		$offSetString .= "+";
		
	}
	elseif ($offSetHours < 0)
	{
		echo("less than zero<br><br>");
		
		$offSetString .= "-";
	}
	else
	{
		echo("is zero<br><br>");
		
		$offSetString .= "-";
	}
	
	//if 1 char 
	if (strlen($offSetHours) == 1)
	{
		echo("one char<br><br>");
		
		$offSetString .= "0" . abs($offSetHours) . "00";
	}
	elseif (strlen($offSetHours) == 2)
	{
		echo("two char<br><br>");
		
		$offSetString .= abs($offSetHours) ."00";
	}

	return $offSetString;
	
}


function clearAverages($type)
{
	
	    //try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
 
	//if this this day datum exists
	$clearSQL = "DELETE FROM day_datum WHERE node_id = ".$this->id;
	
	    				//create utility
		$theUtility = new SolarUtility;

	//run select query
	//$result = mysql_db_query($theUtility->dbName,$clearSQL);
	$result = $this->dbLink->query($clearSQL);
	$result->data_seek(0);
	
}


    
   
    
    
 
    

    
function daysDifference($startDate, $endDate)
{
	
	// echo ("in dateDiff startDate:".$startDate."<br>" ); 
	 
    // Parse dates for conversion
    $startArry = date_parse($startDate);
    $endArry = date_parse($endDate);

   // echo ("startArry:".$startArry."<br>" );
    
    
    // Convert dates to Julian Days
    $start_date = gregoriantojd($startArry["month"], $startArry["day"], $startArry["year"]);
    $end_date = gregoriantojd($endArry["month"], $endArry["day"], $endArry["year"]);

    // Return difference
    return round(($end_date - $start_date), 0);
    //return 6;
} 


    
     function listTimeZones($defaultTimeZone)
    {
    	
    	echo("<td><select name='timeZone' size='1'>\n");
			
	
				echo("<option value='0'>Other\n");
		
			
	
		
		$timezone_identifiers = DateTimeZone::listIdentifiers();
		
		//loop through results 
		for ($i=0; $i < sizeof($timezone_identifiers); $i++)
		{
								

				if ($timezone_identifiers[$i] == $defaultTimeZone)
				{
					echo("<option value='".$timezone_identifiers[$i]."' selected>".$timezone_identifiers[$i]."\n");
				}
				else {
					echo("<option value='".$timezone_identifiers[$i]."'>".$timezone_identifiers[$i]."\n");
				}
				
					
				
	
		
		} //end while
		
			echo("</select>\n");
			
    }


     
    

    //TODO add a priority level to their execution?
    function getSubscribedNodes()
    {
    	
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	
    	    //create array
    	    $subscribedNodes = array();

    	    		// setup the sql/
		$sql = "select node_id from node where is_subscribed_for_training > 0";

		//create utility
		$theUtility = new SolarUtility;
		
		// execute the sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("getSubscribedNodes select sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		// loop through results 
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
			//add nodes					
			$subscribedNodes[] = $row["node_id"];
		
		} //end while
		
		//return array
		return  $subscribedNodes;
    	   
    }
    
    function getMostRecentPowerDatum()
    {
        //try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	    
    	//setup sql 
		$sql = "select when_logged from power_datum where node_id = ".$this->id." order by when_logged desc LIMIT 0 , 1";
		
		echo(" node getMostRecentPowerDatum sql:". $sql. "<br>");

		//create utility
		$theUtility = new SolarUtility;
		
		//execute sql 
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node getMostRecentPowerDatum sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		//loop through results 
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
			$mostRecentPowerDatumDate = $row["when_logged"];
		}
    
		return $mostRecentPowerDatumDate;
		
		echo(" mostRecentPowerDatumDate:". $mostRecentPowerDatumDate. "<br>");
    
    }
  
     function getMostRecentConsumptionDatum()
    {
        //try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	    
    	    	//setup sql 
		$sql = "select when_logged from consumption_datum where node_id = ".$this->id." order by when_logged desc LIMIT 0 , 1";
		
		echo(" node getMostRecentPowerDatum sql:". $sql. "<br>");

		//create utility
		$theUtility = new SolarUtility;
		
		//execute sql 
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node getMostRecentPowerDatum sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		//check how many ConsumptionDatum are found
		//$consumptionDatumFound = mysql_num_rows($result);
		$consumptionDatumFound = mysqli_num_rows($result);
		
		
		echo(" consumptionDatumFound:". $consumptionDatumFound. "<br>");
		
		//if there are none
		if ($consumptionDatumFound == 0)
		{
			
			//TODO set the date to earliest data that there is for this node
			$mostRecentConsumptionDatumDate = "2014-11-01";
		}
		else
		{
		
			//loop through results 
			//while ($row = mysql_fetch_array ($result))
			while ($row = $result->fetch_assoc())
			{
				$mostRecentConsumptionDatumDate = $row["when_logged"];
			}
    
		}
		echo(" mostRecentConsumptionDatumDate:". $mostRecentConsumptionDatumDate. "<br>");
		
		return $mostRecentConsumptionDatumDate;
		
		
    
    }
    
     
    //get the most recent unprocessed patternSet that includes this node
    function getMostRecentQueuedPatternSetEndDate()
    {
    
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	    
    	    	//setup sql 
    	    	//TODO: use centralized statusIds
		$sql = "SELECT end_date 
		FROM  `pattern_set` AS p
		INNER JOIN patternset_node_match AS pnm
		WHERE p.pattern_set_id = pnm.pattern_set_id
		AND pnm.node_id = ".$this->id." and p.status_id = 0
		ORDER BY p.end_date DESC 
		LIMIT 1";
		
		echo(" node getMostRecentQueuedPatternSetEndDate sql:". $sql. "<br>");

		//create utility
		$theUtility = new SolarUtility;
		
		//execute sql 
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node getMostRecentQueuedPatternSetEndDate sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		//check how many patternSets are found
		//$patternSetsFound = mysql_num_rows($result);
		$patternSetsFound = mysqli_num_rows($result);
		
		
		echo(" patternSetsFound:". $patternSetsFound. "<br>");
		
		//if there are none
		if ($patternSetsFound == 0)
		{
		
			//TODO set the date to earliest data that there is for this node
			$mostRecentQueuedPatternSetEndDate = "2014-07-01";
			
		}
		else
		{
			
			//loop through results
			//while ($row = mysql_fetch_array ($result))
			while ($row = $result->fetch_assoc())
			{
				$mostRecentQueuedPatternSetEndDate = $row["end_date"];
			}
		
		}
    
		echo(" mostRecentQueuedPatternSetEndDate:". $mostRecentQueuedPatternSetEndDate. "<br>");
		
		return $mostRecentQueuedPatternSetEndDate;
    
    }
    
    function getTrainingFiles($patternSetIds)
    {

    
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	    	
    	    //setup vars
    	    $trainingFileIds = array();
    
    	    //setup sql 
	    $sql = "select t.training_file_id 
	    from training_file t
	    INNER JOIN patternset_node_match as psnm
	    ON t.pattern_set_id = psnm.pattern_set_id
	    WHERE psnm.node_id = ".$this->id." 
	    AND t.status_id = 2 ";
	    
	    if (sizeof($patternSetIds)>0)
	    {
	    	    $sql .= "AND t.pattern_set_id IN (".implode(",", $patternSetIds).") ";	    
	    }
	    
	    $sql .= " ORDER BY t.stop_training DESC";
   
	    
	    
	   // echo("getTrainingFiles sql:". $sql. "<br>");
	    
	    //create utility
	    $theUtility = new SolarUtility;
	    
	    //execute sql 
	    //$result = mysql_db_query($theUtility->dbName,"$sql") or die ("getTrainingFiles sql failed");
	    $result = $this->dbLink->query($sql);
		$result->data_seek(0);
	    
	    //loop through results
	    //while ($row = mysql_fetch_array ($result))
	    while ($row = $result->fetch_assoc())
	    {
		array_push($trainingFileIds,  $row["training_file_id"]);
	    }
	    
	    return $trainingFileIds;
    	    
    }
    
    function getMostProcessedRecentTrainingFile()
    {
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}  	  

	    
	    //setup sql 
	    $sql = "select t.training_file_id 
	    from training_file t
	    INNER JOIN patternset_node_match as psnm
	    ON t.pattern_set_id = psnm.pattern_set_id
	    WHERE psnm.node_id = ".$this->id."  
	    AND t.status_id = 2
	    ORDER BY t.stop_training DESC
	    LIMIT 1";
	    
	    echo("getMostProcessedRecentTrainingFile sql:". $sql. "<br>");
	    
	    //log an logentry
	    $theError = new SolarError;
	    $theError->module = "Node::getMostProcessedRecentTrainingFile";
	    $theError->details = "getMostProcessedRecentTrainingFile sql:".$sql;
	    $theError->add();
	    
	    //create utility
	    $theUtility = new SolarUtility;
	    $theTrainingFile = new TrainingFile;

	    //execute sql 
	    //$result = mysql_db_query($theUtility->dbName,"$sql") or die ("getMostRecentTrainingFile sql failed");
	    $result = $this->dbLink->query($sql);
		$result->data_seek(0);
	    
	    //loop through results
	    //while ($row = mysql_fetch_array ($result))
	    while ($row = $result->fetch_assoc())
	    {
		$theTrainingFile->id = $row["training_file_id"];
	    }
	    
	    //if there is an id
	    if ($theTrainingFile->id > 0)
	    {
	    	    //construct object
	    	    $theTrainingFile->constructFromId();
	    
	    }
	    else
	    {
	    	    				//log an logentry
						$theError = new SolarError;
						$theError->module = "Node::getMostProcessedRecentTrainingFile";
						$theError->details = "could not construct trainingFile - no trainingFile id for patternset:".$this->id;
						$theError->add();
	    	    
	    }
	    
	    	    //log an logentry
	    $theError = new SolarError;
	    $theError->module = "Node::getMostProcessedRecentTrainingFile";
	    $theError->details = "theTrainingFile id :".$theTrainingFile->id." outputWeightsFileName;". $theTrainingFile->outputWeightsFileName;
	    $theError->add();
	    
	    return $theTrainingFile;
	    
    	    
    }
   
    //get patternSets that are currently in process
    //TODO add a priority to determine their execution order?
    function getPatternSetIds($status)
    {
    	
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	
    	    //determine StatusId
    	    //TODO refer to global status values
    	    if ($status == "notProcessed")
    	    {
    	    	  $statusId = 0;  
    	    }
    	    elseif ($status == "inProcess")
    	    {
    	    	  $statusId = 1;  
    	    }
    	    elseif ($status == "trainingFileCreated")
    	    {
    	    	  $statusId = 4;  
    	    }    	    
    	    elseif ($status == "trainingFileUnderway")
    	    {
    	    	  $statusId = 5;  
    	    } 
    	    elseif ($status == "completedSuccessfully")
    	    {
    	    	  $statusId = 6;  
    	    }
    	    elseif ($status == "questioningFileUnderway")
    	    {
    	    	  $statusId = 7;  
    	    }   
    	    //TODO  futureForecast - get the type 4 patternset ID where the end date is at least in the future from right now
    	    // most likely the status_id will be that it is completed questioning or status_id = 8
    	    elseif ($status == "futureForecast")
    	    {
    	    	  $statusId = 8; 
    	    	  $nowDatetime = new DateTime();
    	    	  $todayAsDate = $nowDatetime->format('Y-m-d');
    	    	  //$todayAsDate = "2017-09-11";
    	    }      	    

   	    
    	    
    	    //create blank array to hand back
    	    $patternSetIds = array();


    	    	//setup sql 
		$sql = "SELECT p.pattern_set_id 
		FROM  `pattern_set` AS p
		INNER JOIN patternset_node_match AS pnm
		WHERE p.pattern_set_id = pnm.pattern_set_id";
		
		//if we have not constructed a node from an id
		if ($this->id == 0)
		{
			//just sort by status
			$sql.= " AND p.status_id = ".$statusId;
		}
		else
		{
			if ($status == "completedOrUnderway")
			{
				$sql.= " AND pnm.node_id = ".$this->id." AND (p.status_id = 5 OR p.status_id = 6) ";
			}
			else
			{
			
				$sql.= " AND pnm.node_id = ".$this->id." AND p.status_id = ".$statusId;
				
				//if we're looking for futureForecast patternSets
				if ($status == "futureForecast")
				{
					//their type_id has to be 4
					$sql.= " AND p.pattern_set_type_id = 4 ";
					$sql.= " AND end_date >= '".$todayAsDate."'";
					
				}
			
			}
		}
		
		
		
		if (($status == "trainingFileCreated") | ($status == "inProcess") | ($status == "completedSuccessfully") )
		{
			$sql .=	" ORDER BY pnm.pattern_set_id DESC ";
		}
		
		//echo(" node patternSetIds sql:". $sql. "<br>");
		
			//log an logentry
			$theError = new SolarError;
			$theError->module = "Node::getPatternSetIds";
			$theError->details = "INFO: sql:".$sql." status:".$status ;
			$theError->add();

		//create utility
		$theUtility = new SolarUtility;
		
		//execute sql 
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node getPatternSetIdsInProcess sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		//loop through results
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
			//echo(" adding patternSetId to patternSetIds:". $row["pattern_set_id"]. "<br>");
			array_push($patternSetIds,  $row["pattern_set_id"]);
		}

		//echo(" size of patternSetIds:". sizeof($patternSetIds). "<br>");
		
		return $patternSetIds;
    
    }
    
    
    
    
    function add()
    {
        //try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    
		//setup sql
		//$sql = "insert into country (country_id, name, abbreviation) values ($this->id,\"$this->name\",\"$this->abbreviation\")";
		$sql = "insert into `node` (node_id, node_type_id, location, time_zone, city, country, notes) values ($this->id, $this->nodeTypeId, \"$this->location\", \"$this->timeZone\", \"$this->city\", \"$this->country\",\"$this->notes\")";

		echo("sql:". $sql. "<br>");
		
		//create utility
		$theUtility = new SolarUtility;

		//execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("insert sql failed3");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
    
    }
    function refreshSources()
    {
    	    
    	    		$theUtility = new SolarUtility;
    	    		
    	       	if (function_exists("curl_init")){
			echo("curl_init OK<br>");
		}
		else
		{
			echo("curl_init NOT OK<br>");;
		}
	
		if (function_exists("curl_setopt")){
			echo("curl_setopt OK<br>");
		}
		else
		{
			echo("curl_setopt NOT OK<br>");;
		}
		
		if (function_exists("curl_exec")){
			echo("curl_exec OK<br>");
		}
		else
		{
			echo("curl_exec NOT OK<br>");;
		}
		if (function_exists("curl_close")){
			echo("curl_close OK<br>");
		}
		else
		{
			echo("curl_close NOT OK<br>");;
		}
    	    
    	    	//create the parts of the URL
		$theProtocol = "https://";
		$theHost = "data.solarnetwork.net";
		$theNodeId = $this->id;
		
		//new JSON API
		$theUri = "/solarquery/api/v1/pub/range/sources?nodeId=".$theNodeId;
		
		//create the URL to use in the header of the curl call
		$theUrl = $theProtocol.$theHost.$theUri;
		
		//create the raw cURL command line
		//$theCurlCall = "curl -H '".$headerArray[0]."' -H '".$headerArray[1]."' '".$theUrl."'";
		$theCurlCall = "curl '".$theUrl."'";
		
		//write the curl call to a file
		$fp2 = fopen($theUtility->localAbsolutePath.'emergent/output/theSourceCurlCall.txt', 'w');
		fwrite($fp2, $theCurlCall);
		fclose($fp2);
		
		echo("wrote theSourceCurlCall.txt<br>");
		

		
		$theJSONData = $theUtility->cURLdownload($theUrl, $headerArray);
		
		echo("after theUtility->cURLdownload<br>");
		
		
		
		echo "after cURLdownload theJSONData :".$theJSONData."<br />"; 
		
		$resultsArray = json_decode($theJSONData, true);
		
		echo "sizeof(resultsArray):".sizeof($resultsArray)."<br />"; 
		
		echo "after json_decode theJSONData :".var_dump($resultsArray)."<br />"; 
		
		//echo "after json_decode resultsArray[0] :".$resultsArray[0]."<br />"; 
		
		//$innerResults = $resultsArray[1];
		
				
		//as long we we have sources
		
		//clear current sources
		$this->clearSources();
		
		
		
		
		foreach ($resultsArray as $name => $value)
		{
			
			echo "name :".$name." value: ".$value."<br />"; 
			
			if ($name == "data")
			{
				echo("in data<br>");
				
				foreach ($value as $itemName => $itemValue)
				{
				
					echo "itemName:".$itemName."<br />";
					echo "itemValue:".$itemValue."<br />";
					
					//add sources
					$this->addSource($itemValue);
					
					
					
				}
				
			}
			else
			{
				echo("no data<br>");
			}
			
		}
    	    
    	    
    }
    
     
    function clearSources()
    {
    
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	    	    
    	    echo "in clearSources this->id :".$this->id."<br />"; 
    	    
    	//clear out node sources
		$deleteNodeSources = "delete from node_source where node_id = ".$this->id;
		
		//create utility
		$theUtility = new SolarUtility;
		
		//execute sql
		//$deleteNodeSourcesResult = mysql_db_query($theUtility->dbName,"$deleteNodeSources") or die ("deleteNodeSources failed");
		$deleteNodeSourcesResult = $this->dbLink->query($deleteNodeSources);
		//$deleteNodeSourcesResult->data_seek(0);
		
    }
    function addSource($sourceId)
    {
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	
    		//setup sql 
		$addSourceSql = "insert into node_source (node_id, sourceId, source_type_id) values (".$this->id.",'".$sourceId."',1)";

		//create utility
		$theUtility = new SolarUtility;
		
		echo("addSourceSql:". $addSourceSql. "<br>");

		//break;
		// execute sql 
		//$result = mysql_db_query($theUtility->dbName,"$addSourceSql") or die ("addSourceSql failed");
		$result = $this->dbLink->query($addSourceSql);
		//$result->data_seek(0);
    }
    //returns an array of sourceId
    function getSources($patternSetId)
    {
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	
    	    //set vars
    	    $sourceIds = array();
    	    
    	    //echo "in getSources node->id :".$this->id."<br />"; 
    	    //echo "in getSources patternSetId :".$patternSetId."<br />"; 
		
    	    //get only the sources for this patternSetId 
    	    if ($patternSetId > 0)
    	    {
    	    	 $sql = "select sourceId from patternset_node_match where node_id = ".$this->id." and pattern_set_id = ".$patternSetId." order by sourceId asc";
    	    }
    	    else //if patternSetId = 0 then return all sources for this node
    	    {
    	    	 $sql = "select sourceId from node_source where node_id = ".$this->id." order by sourceId asc";
    	    }
    	    
    	    		//create utility
		$theUtility = new SolarUtility;
		
    	    ///execute the sql 
	    //$result = mysql_db_query($theUtility->dbName,"$sql") or die ("getSources sql failed:".$sql."<br>");
	    $result = $this->dbLink->query($sql);
		$result->data_seek(0);
	    
	    	//loop through results
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
			$sourceIds[] = $row["sourceId"];

		}
		
		return $sourceIds;
    	    
    }
    
    
    function listSources($displayMode, $patternSetId)
    {
    	    

		
    	$allSources = $this->getSources(0);
    	$sizeOfAllSources = sizeof($allSources);
    	
    	//echo("sizeOfAllSources:". $sizeOfAllSources. "<br>");
    	
    	$patternSetSourceIds = array();
    	$k = 0;
    	
    	
    	if ($patternSetId > 0)
    	{
    		$patternSetSourceIds = $this->getSources($patternSetId);
    		
    		//echo("sizeof patternSetSourceIds:". sizeof($patternSetSourceIds). "<br>");
    		
    		//echo("patternSetSourceIds data:". var_dump($patternSetSourceIds). "<br>");
    	}
    	   
    	
    	
        //loop through allSources
	for($k = 0; $k < $sizeOfAllSources;$k++)
	{
    	   
		$theSourceId = $allSources[$k];
		
		//echo("theSourceId:". $theSourceId. "<br>");
		
		if ($displayMode == "checkBox")
		{
			// if the sourceId is one of the default
			if (in_array($theSourceId , $patternSetSourceIds))
			{
				echo("<span class='solar4'>".$theSourceId."</span> <input type='checkbox' checked value='".$theSourceId."' name='sourceIds[]'><br>");
			}
			else
			{
				echo("<span class='solar4'>".$theSourceId."</span> <input type='checkbox' value='".$theSourceId."' name='sourceIds[]'><br>");
			}
			
		}  
		
	
	}
	
    	    
    }
    
    function edit()
    {
    
		//construct from id
		$this->constructFromId();
		
		// generate edit form 
		echo("<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>\n");

		echo("<html>\n");
		echo("<head>\n");
	
		echo("<title>solarquant.Admin</title>\n"); 
		echo("</head>\n");
		echo("<body bgcolor='#BFBFBF'>\n");

		echo("<form method=POST action='nodeAction.php'>\n");
		
		echo("<table>");
		echo("<tr>");
			echo("<td bgcolor='#92999C'>Location</td>");
			echo("<td><input type='text' name='location' value='$this->location' size='20'></td>");
		echo("</tr>");
		echo("<tr>");
			echo("<td bgcolor='#92999C'>wcIdentifier</td>");
			echo("<td><input type='text' name='wcIdentifier' value='$this->wcIdentifier' size='20'></td>");
		echo("</tr>");
		
		echo("<tr>");
			echo("<td bgcolor='#92999C'>nodeId</td>");
			echo("<td><input type='text' name='nodeId' value='$this->id' size='20'></td>");
		echo("</tr>");

		echo("<tr>");
			echo("<td bgcolor='#92999C'>Subscribed For Training</td>");
			echo("<td><input type='text' name='isSubscribedForTraining' value='$this->isSubscribedForTraining' size='2'></td>");
		echo("</tr>");

		echo("<tr>");
			echo("<td bgcolor='#92999C'>WeatherNodeId</td>");
			echo("<td><input type='text' name='weatherNodeId' value='$this->weatherNodeId' size='4'></td>");
		echo("</tr>");

		echo("<tr>");
			echo("<td bgcolor='#92999C'>Subscribed sourceIds</td>");
			echo("<td><input type='text' name='sourceIds' value='$this->subscribedSourceIds' size='24'></td>");
		echo("</tr>");
		
		echo("<tr>");
			echo("<td bgcolor='#92999C'>timeZone</td>");
			echo("<td>".$this->listTimeZones($this->timeZone)."</td>");
		echo("</tr>");
		
		echo("</table>");
		

	
		echo("<input type='hidden' name='function' value='update'><br><br>\n");
		echo("<input type='submit' name='theButton' value='Update'>\n");
		echo("</form>\n");
		echo("</body>\n");
		echo("</html>\n");
    
    
    
    }
   
    function update()
    {
    
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	    
    	    	// setup sql
		$sql = "update node 
		set 
		location = '".$this->location."', 
		wc_identifier = '".$this->wcIdentifier."', 
		time_zone = '".$this->timeZone."',
		is_subscribed_for_training = ".$this->isSubscribedForTraining." ,
		weather_node_id = ".$this->weatherNodeId." ,
		subscribed_source_ids = '".$this->subscribedSourceIds."' 
		where node_id = ".$this->id;

		
		echo("update sql:". $sql. "<br>");
		
		//create utility
		$theUtility = new SolarUtility;
		
		// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node update sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
    
    }
    
    function delete    ()
    {
    
    	    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	
    	    		//create utility
		$theUtility = new SolarUtility;
    	    
    	    $sql = "delete from weather_input_pattern where node_id = ".$this->id;
    	    
    	    		// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node delete sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
    	    
    	    $sql = "delete from weather_datum where node_id = ".$this->id;
    	    
    	    		// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node delete sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
    	    
    	    $sql = "delete from consumption_input_pattern where node_id = ".$this->id;
    	    
    	    		// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node delete sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
    	   
    	    $sql = "delete from consumption_datum where node_id = ".$this->id;
    	    
    	    		// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node delete sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
    	    
    	     $sql = "delete from power_datum where node_id = ".$this->id;
    	     
    	     		// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node delete sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
		
		//TODO set all patternsetIds associated with this node and delete them
    	     
    	    $sql = "delete from patternset_node_match where node_id = ".$this->id;
    	    
    	    		// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node delete sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
		
		
		
		    	    // setup sql
		$sql = "delete from node_source where node_id = ".$this->id;
		
				// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node delete sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
    	    
    	    // setup sql
		$sql = "delete from node where node_id = ".$this->id;

		
		//echo("delete sql:". $sql. "<br>");
		

		
		// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node delete sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
    }
    function deleteConsumptionDatum($startDate,$endDate)
    {
    	
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	
    	//create utility
	$theUtility = new SolarUtility;
	
	//set SQL	
    	$sql = "delete from consumption_datum where node_id = ".$this->id." AND when_logged > '".$startDate."' AND when_logged < '".$endDate."'";
    	    
    	//log an logentry
	    $theError = new SolarError;
	    $theError->module = "Node::deleteConsumptionDatum";
	    $theError->details = "deleteConsumptionDatum sql:".$sql;
	    $theError->add();
	    
    	// execute sql
    	//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("deleteConsumptionDatum sql failed");
    	$result = $this->dbLink->query($sql);
		//$result->data_seek(0);    
    }
    
  
    
}

?>