<?php

// 2017.06.12 this is being repurposed to mostly generate new virtual weather patterns sets to be questioned against the most
// recent trained network. weather forecasts are generally a few days to 2 weeks in the future
// these patternSets will be tagged as question only

//imports
require_once "/var/www/html/solarquant/classes/node.php";
require_once "/var/www/html/solarquant/classes/PowerDatum.php";
require_once "/var/www/html/solarquant/classes/PatternSet.php";
require_once "/var/www/html/solarquant/classes/SolarError.php";
require_once "/var/www/html/solarquant/classes/SolarUtility.php";

//debug 
//echo("start cron_generateNewPatternSets<br>");
//exit;

//get db connection
if ($link = " "){
	//centralize authentication
	$theUtility = new SolarUtility;
	//$link = mysql_connect ($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword) or die ("Could not connect1");
	$link = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
}

//check the file system for the chock file
$theFilePointer = fopen($theUtility->generateNewPatternChockFile, 'r');	

//only if it does exist yet
if ($theFilePointer == false)
{

	//log an logentry
	$theError = new SolarError;
	$theError->module = "cron_generateNewPatternSets";
	$theError->details = "processed NOT chocked, theFilePointer";
	$theError->add();
			
	//log an logentry
	$theError = new SolarError;
	$theError->module = "cron_generateNewPatternSets";
	$theError->details = "starting cron_generateNewPatternSets ";
	$theError->add();
			
	//create a node
	$theNode = new Node;
	$thePowerDatum = new PowerDatum;
	
	//get subscribed nodes
	$subscribedNodes = $theNode->getSubscribedNodes();
		
	//debug
	echo "subscribedNodes[0] :".$subscribedNodes[0]."<br />"; 
		
	//log an logentry
	$theError = new SolarError;
	$theError->module = "cron_generateNewPatternSets";
	$theError->details = "found ".count($subscribedNodes)." subscribed nodes to generate patternsets for";
	$theError->add();
		
	//loop through subscribers
	$i = 0;
	while ($i < count($subscribedNodes) )
	{
	
		//set up this node
		$theNode->id = $subscribedNodes[$i];
		
		//debug
		echo "cron theNode->id:".$theNode->id."<br>";
		
		//TODO - we need to separate the consumption datum from the pattern set possibly using a match table?
		//so we keep consumption datum standalone but use the unique consumption_datum_id values and match them to a pattern_set_id
		
		//get most recent consumption datum
		$mostRecentConsumptionDatumDate = $theNode->getMostRecentConsumptionDatum();
		
		//debug
		echo "mostRecentConsumptionDatumDate :".$mostRecentConsumptionDatumDate."<br />"; 
		
		//create a new set of DateTime object for now and the most recent consumption
		$nowDatetime = new DateTime();
		$mostRecentConsumptionDatetime = new DateTime($mostRecentConsumptionDatumDate);
		$timeSinceLastCollection = $nowDatetime->diff($mostRecentConsumptionDatetime);
		
		//debug
		echo "nowDatetime".$nowDatetime->format('Y-m-d H:i:s')."<br>";
		echo "timeSinceLastCollection days:".$timeSinceLastCollection->d."<br>";
		
		//get total minutes since last datum capture
		$minutesSinceLastCollection = $timeSinceLastCollection->days * 24 * 60;
		$minutesSinceLastCollection += $timeSinceLastCollection->h * 60;
		$minutesSinceLastCollection += $timeSinceLastCollection->i;
		
		//debug
		echo "minutesSinceLastCollection:".$minutesSinceLastCollection."<br>";
		
		//get the most recent Actual (versus Virtual) queued patternset date
		$mostRecentQueuedPatternSetEndDate = $theNode->getMostRecentQueuedPatternSetEndDate();
		
		//debug
		echo "cron mostRecentQueuedPatternSetEndDate:".$mostRecentQueuedPatternSetEndDate."<br>";
		
		//determine how long it has been since the last end date of a patternset
		$mostRecentQueuedPatternSetEndDateTime = new DateTime($mostRecentQueuedPatternSetEndDate);
		$timeSinceLastQueuedPatternSetEndDate = $nowDatetime->diff($mostRecentQueuedPatternSetEndDateTime);
		
		//debug
		//echo "timeSinceLastQueuedPatternSetEndDate:".$timeSinceLastQueuedPatternSetEndDate."<br>";
		
		//determine how large this interval is in minutes
		$minutesSinceLastQueuedPatternSet = $timeSinceLastQueuedPatternSetEndDate->days * 24 * 60;
		$minutesSinceLastQueuedPatternSet += $timeSinceLastQueuedPatternSetEndDate->h * 60;
		$minutesSinceLastQueuedPatternSet += $timeSinceLastQueuedPatternSetEndDate->i;
		
		//debug
		echo "cron minutesSinceLastQueuedPatternSet:".$minutesSinceLastQueuedPatternSet."<br>";
		
		//are there other queued pattersets within 24 hours or 1440 minutes of now?
		//TODO gloabize this constant
		if ($minutesSinceLastQueuedPatternSet > 1440)
		{
			//debug
			echo "minutesSinceLastQueuedPatternSet greater than 1440<br>";	
			
			//has it been 24 hours or 1440 minutes since then? 
			//TODO gloabize this constant
			if ($minutesSinceLastCollection > 1440)
			{
				
				//debug
				echo "minutesSinceLastCollection greater than 1440<br>";
		
				//create new patternset between then and now
				//instantiate object
				$thePatternSet = new PatternSet;
				
				//debug
				//$thePatternSet->name = "node_".$theNode->id."_".$mostRecentConsumptionDatetime->format('Y-m-d_H:i:s')."_".$nowDatetime->format('Y-m-d_H:i:s');
				//$thePatternSet->name = "node_".$theNode->id."_".$mostRecentConsumptionDatetime->format('Y-m-d')."_".$nowDatetime->format('Y-m-d');
				echo "thePatternSet->name:".$thePatternSet->name."<br>";
				
				//determine which is newer
				//TODO reconcile these - which one?
				$intervalBetweenDates = $mostRecentConsumptionDatetime->diff($mostRecentQueuedPatternSetEndDateTime);
				$intervalBetweenDates = $mostRecentQueuedPatternSetEndDateTime->diff($mostRecentConsumptionDatetime);
				
				//if the most recent consumption data is newer
				if ($intervalBetweenDates->format('%R%a') > 0) 
				{
					//debug
					echo "mostRecentConsumptionDatetime more recent<br>";
					
					//use this as the startdate for the new PatternSet
					$thePatternSet->startDate = $mostRecentConsumptionDatetime->format('Y-m-d_H:i:s');
					$theStartDateTime = $mostRecentConsumptionDatetime;
					
					//debug
					echo "set thePatternSet->startDate:".$thePatternSet->startDate."<br>";
				}
				else //if the most recent queued pattern end datedate time is newer
				{
					//debug 
					echo "mostRecentQueuedPatternSetEndDateTime more recent<br>";
					
					//use this as the startdate for the new PatternSet
					$thePatternSet->startDate = $mostRecentQueuedPatternSetEndDateTime->format('Y-m-d_H:i:s');
					$theStartDateTime = $mostRecentQueuedPatternSetEndDateTime;
					
					//debug
					echo "set thePatternSet->startDate:".$thePatternSet->startDate."<br>";
					
				}
				
				echo "intervalBetweenDates:".$intervalBetweenDates->format('%R%a')."<br>";
				
		
				//create an enddate that is trainingInterval hours infront of startDate
				
				//debug
				//$thePatternSet->endDate = $nowDatetime->format('Y-m-d_H:i:s');
				echo("startdate:". $thePatternSet->startDate ."<br>");
				
				//create a variable for the pattern set name
				$theStartDateOnly = $theStartDateTime->format('Y-m-d');
				
				//start with the start time and add a given interval
				//TODO globalise this setting
				//TODO make sure we have weather forecasts this far out
				$theEndDatetime = $theStartDateTime;
				$theEndDatetime->add(new DateInterval('P3D'));
				
				//debug
				echo("theStartDateTime after adding 3 days:". $theEndDatetime->format('Y-m-d_H:i:s') ."<br>");
				
				//set the name for the new PatternSet and the enddate
				$thePatternSet->name = "node_".$theNode->id."_".$theStartDateOnly."_".$theEndDatetime->format('Y-m-d');
				$thePatternSet->endDate = $theEndDatetime->format('Y-m-d_H:i:s');
				
				//debug
				//$thePatternSet->notes = $_REQUEST['notes'];
				
				//add the nodeId as the only member of this array for now
				//TODO could be that a patternset might reflect a lot of nodes, sources?
				$thePatternSet->nodes = array($theNode->id);
							
				//as long it's a valid pattern set with nodes
				if (sizeof($thePatternSet->nodes) > 0)
				{
					//debug
					echo "about to add PatternSet:".$thePatternSet->name."<br>";
					
					//set statusId to queued
					//TODO make this a global value
					$thePatternSet->statusId = 0;
					
					//TODO: set patternSetType to follow node (consumption, generation, both)
					//TODO do we want to make this 4 for futureVirtual
					$thePatternSet->patternSetTypeId = 1;
					
					//run add
					$thePatternSet->add();
					
					//debug
					echo "after add PatternSet:".$thePatternSet->name."<br>";
					
					//log an logentry
					$theError = new SolarError;
					$theError->module = "cron_generateNewPatternSets";
					$theError->details = "generated patternset:".$thePatternSet->name." as ID: ".$thePatternSet->id;
					$theError->add();
					
				}
				else
				{
					//debug
					echo "cannot add PatternSet no nodes:<br>";
					
				}
				
			} //has it been 1440 minutes since last collection
			else
			{
				echo "minutesSinceLastCollection less than 1440<br>";
				
				//log an logentry
				$theError = new SolarError;
				$theError->module = "cron_generateNewPatternSets";
				$theError->details = "not creating new patternsets because minutesSinceLastCollection less than or equal to 1440:".$minutesSinceLastCollection;
				$theError->add();
			
			}
		
		} //has it been 1440 minutes since last collection
		else
		{
			//debug
			echo "minutesSinceLastQueuedPatternSet less than 1440<br>";
			
			//log an logentry
			$theError = new SolarError;
			$theError->module = "cron_generateNewPatternSets";
			$theError->details = "not creating new patternsets because minutesSinceLastQueuedPatternSet less than 1440:".$minutesSinceLastQueuedPatternSet;
			$theError->add();
			
		}

		//increment 
		$i++;
		
	} //end while

} //theFilePointer does not exist
else
{

	//log an logentry
	$theError = new SolarError;
	$theError->module = "cron_generateNewPatternSets";
	$theError->details = "processed chocked, theFilePointer :";
	$theError->add();
	
}

//release this file pointer
fclose($theFilePointer);
		
?>
