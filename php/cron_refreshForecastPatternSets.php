<?php

//debug
echo "in cron_refreshForecastPatternSets <br />"; 

//TODO add logfile entries for this process

//imports
require_once "/var/www/html/solarquant/classes/node.php";
require_once "/var/www/html/solarquant/classes/PowerDatum.php";
require_once "/var/www/html/solarquant/classes/SolarUtility.php";
require_once "/var/www/html/solarquant/classes/PatternSet.php";
require_once "/var/www/html/solarquant/classes/ConsumptionPatternSet.php";
require_once "/var/www/html/solarquant/classes/SolarError.php";

//write to training log
$messageDigest = date("Y-m-d H:i:s")." start of cron_refreshForecastPatternSets "."\n" ;

//debug
echo "messageDigest :".$messageDigest."<br />"; 
		
//write messageDigest to a file
$fp1 = fopen('/var/www/html/solarquant/emergent/output/cron_refreshForecastPatternSets_log.txt', 'a');
fwrite($fp1, $messageDigest);
		
//get db connection
if ($link = " "){
	//centralize authentication
	$theUtility = new SolarUtility;
	//$link = mysql_connect ($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword) or die ("Could not connect1");
	$link = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
}
		
//check the file system for the chock file
$theFilePointer = fopen($theUtility->refreshForecastPatternSetsChockFile, 'r');

//only if the chockfile does exist yet
if ($theFilePointer == false)
{
	//log an logentry
	$theError = new SolarError;
	$theError->module = "cron_refreshForecastPatternSets";
	$theError->details = "processed NOT chocked, theFilePointer";
	$theError->add();
	
	//create a node
	$theNode = new Node;
	$thePowerDatum = new PowerDatum;
	
	//get subscribed nodes
	$subscribedNodes = $theNode->getSubscribedNodes();
	
	//debug
	echo "count subscribedNodes :".count($subscribedNodes)."<br />"; 
	
	//log an logentry
	$theError = new SolarError;
	$theError->module = "cron_refreshForecastPatternSets";
	$theError->details = "found ".count($subscribedNodes)." subscribedNodes";
	$theError->add();
			
	//so far noneInProcess
	$noneInProcess = true;
	
	//debug
	echo "about to loop through subscribers <br />";
		
	//loop through subscribers
	//TODO do we really need to wait for none in process? this is just about getting actual data
	$i = 0;
	while (($i < count($subscribedNodes)) & $noneInProcess )
	{
		//debug
		echo "i :".$i."<br />"; 

		//set up this node
		$theNode->id = $subscribedNodes[$i];
		$theNode->constructFromId();
		
		//TODO probably need to check of inProcess or reDo - where data is downloaded but trainingfile not created
		
		//check to see if there is anything in the queue
		$patternSetsInProcess = $theNode->getPatternSetIds("inProcess");
		
		//debug
		echo "count of patternSetsInProcess :".count($patternSetsInProcess)."<br />"; 

		//as long as there is nothing in process at the moment
		if (count($patternSetsInProcess) < 1)
		{
			//debug
			echo "zero patternSetsInProcess <br />"; 
			
			//TODO get the not processed OR the data downloaded 
			
			//get the forecast patternSets involved with this node
			$queuedPatternSetIds = $theNode->getPatternSetIds("futureForecast");
			
			//debug
			echo "count of queuedPatternSetIds :".count($queuedPatternSetIds)."\n"; 
			
			//log an logentry
			$theError = new SolarError;
			$theError->module = "cron_refreshForecastPatternSets";
			$theError->details = "found ".count($queuedPatternSetIds)." queued PatternSetIds";
			$theError->add();
						
			//loop through PatternSets
			$j = 0;
			while ($j < count($queuedPatternSetIds))
			{
				//debug		
				echo "j :".$j."<br />";
				echo "queuedPatternSetIds[j] :".$queuedPatternSetIds[$j]."\n"; 

				//grab a PatternSet and construct
				$thePatternSet = new PatternSet;
				
				//debug
				echo "after new patternset \n"; 
				
				//instantiate the patternSet
				$thePatternSet->id =  $queuedPatternSetIds[$j];
				$thePatternSet->constructFromId();
				
				//debug
				echo "after patternset constructFromId\n"; 
										
				//TODO check if the status = 0 notProcessed
								
				//set the patternSet for this node to beingProcessed
				//TODO figure out what status id this should take to be flagged as about to be processing - 9?
				//$thePatternSet->statusId = 1;
				//$thePatternSet->update();
				
				//log an logentry
				$theError = new SolarError;
				$theError->module = "cron_refreshForecastPatternSets";
				$theError->details = "set patternSet ".$thePatternSet->id." for this node to status beingProcessed";
				$theError->add();
				
				//debug
				echo "after patternset update<br />"; 
				echo "thePatternSet->id :".$thePatternSet->id."\n";
				
				//reset the startdate of this startDate 12 hours earlier (or whatever offset)
				
				//create the text string for start date
				$tempStartDate = $thePatternSet->startDate." 00:00";
		
				//debug
				echo "before getMyDatum tempStartDate:".$tempStartDate."\n";
		
				//create the datetime object of this start date
				$currentDate = strtotime($tempStartDate);
				
				//offset in minutes it to GMT  TODO: use the offset of the node
				$earlierStartDate = $currentDate-(12*60);
				
				//create the string version of this new startDate
				$newStartDate = date("Y-m-d\TH:i", $earlierStartDate);
				$newEndDate = date("Y-m-d\TH:i");
		
				//debug
				echo "before getMyDatum newStartDate:".$newStartDate."\n";
				echo "before getMyDatum newEndDate:".$newEndDate."\n";
		
				//set this pattern set to this string
				$thePatternSet->startDate = $newStartDate;
				$thePatternSet->endDate = $newEndDate;
				
				//log an logentry
				$theError = new SolarError;
				$theError->module = "cron_refreshForecastPatternSets";
				$theError->details = "about to getMyDatum for patternset ".$thePatternSet->id;
				$theError->add();
				

				//get appropriate datum based on type
				$thePatternSet->getMyDatum();
				
				//debug
				echo "after getMyDatum :".$thePatternSet->id."\n";
						
				//set the patternSet for this node to data downloaded
				//TODO what is this status id after downloading actual consumption datum to refresh forecast?
				//$thePatternSet->statusId = 2;
				//$thePatternSet->update();
				
				//otherwise already data downloaded so continue
				
				//log an logentry
				$theError = new SolarError;
				$theError->module = "cron_refreshForecastPatternSets";
				$theError->details = "downloaded datum from SolarNet for patternset ".$thePatternSet->id;
				$theError->add();
				
				//create new ConsumptionPatternSet
				//TODO do we really need this class?
				$theConsumptionPatternSet = new ConsumptionPatternSet;
				
				//debug
				echo "after new ConsumptionPatternSet <br />";
				
				//set this patternSetId to this one
				$theConsumptionPatternSet->patternSetId = $thePatternSet->id;
				
				//debug
				echo "after set theConsumptionPatternSet->patternSetId:".$theConsumptionPatternSet->patternSetId."\n";
								
				//construct the consumptionPatternSet
				$theConsumptionPatternSet->constructFromPatternSetId();
				
				//debug
				echo "after constructFromPatternSetId:".$theConsumptionPatternSet->patternSetId."\n";
				
				//log an logentry
				$theError = new SolarError;
				$theError->module = "cron_refreshForecastPatternSets";
				$theError->details = "after constructFromPatternSetId, startDate: ".$thePatternSet->startDate." endDate: ".$thePatternSet->endDate;
				$theError->add();
				
				//TODO generate the weights only for kilowattHour weight - and only update the rows that exist
				//$theConsumptionPatternSet->generateNNInputWeights();
				$weightsMode = "refreshEnergy";
				
				//generate the weights
				$theConsumptionPatternSet->generateNNInputWeights($weightsMode);
				
				//set the patternSet for this node to NN weights generated
				//TODO do we want to set a status here?
				//$thePatternSet->statusId = 3;
				//$thePatternSet->update();
						
				//log an logentry
				$theError = new SolarError;
				$theError->module = "cron_refreshForecastPatternSets";
				$theError->details = "after generateNNInputWeights for ".$thePatternSet->id;
				$theError->add();
				
				//debug
				echo "after generateNNInputWeights :".$theConsumptionPatternSet->patternSetId."\n";
				echo "about to generate Trainingfile \n";
				
				/*
				
				//set timestamp for file creation
				$saveTime = strtotime("now");
				$showSaveTime = date("Y-m-d H:i:S",$saveTime);
				
				//TODO - this function should generate teh contents for all patternsets from the start of this subscription
				$fileContents = $thePatternSet->generateConsumptionDataTableContents($showSaveTime);
				
				//debug
				echo "after File contents chars:".strlen($fileContents)."<br />";
				echo("before writeTrainingFile \n");
				
				//start a transaction in case this ends badly
				try {
					//debug
					echo("inside transaction \n");
					
					//TODO make sure this new training file uses the latest output weights file as the input weights file like:
					
					//get most recent TrainingFile
					//$theTrainingFile = new TrainingFile;
					//$theTrainingFile = $thePatternSet->getMostRecentTrainingFile();
					
					//write the trainingFile
					$thePatternSet->writeTrainingFile($fileContents);
					
					//set the patternSet for this node to training file generated
					$thePatternSet->statusId = 4;
					$thePatternSet->update();
					
					//log an logentry
					$theError = new SolarError;
					$theError->module = "cron_refreshForecastPatternSets";
					$theError->details = "training file created on filesystem for patternset ".$thePatternSet->id;
					$theError->add();
					
					//update this patternSet as processed
					
				} catch (Exception $e) {
					
					//debug
					echo 'Caught exception at writeTrainingFile: ',  $e->getMessage(), "\n";
					
					//log an logentry
					$theError = new SolarError;
					$theError->module = "cron_refreshForecastPatternSets";
					$theError->details = "ERROR: Could not write training file to file system";
					$theError->add();
					
				} //end transaction
				
				//debug
				echo("after writeTrainingFile <br>");
				
				*/
											
				//increment for next queued pattern set
				$j++;
				
				
				
			}  //loop through pattern sets
	
		} //no patternSets in process
		else
		{
			//debug
			echo "some patternSetsInProcess exiting<br />"; 
			
			//set flag to bounce out of the loop
			$noneInProcess = false;
			
			//log an logentry
			$theError = new SolarError;
			$theError->module = "cron_refreshForecastPatternSets";
			$theError->details = "found ".count($patternSetsInProcess)." patternSetsInProcess so exiting";
			$theError->add();
			
		} //a patternset in process
		
		//increment 
		$i++;
				
	} //loop through subscribers
		
} //theFilePointer does not exist
else  //file pointer does exist
{

		//log an logentry
		$theError = new SolarError;
		$theError->module = "cron_refreshForecastPatternSets";
		$theError->details = "processed chocked, theFilePointer :";
		$theError->add();
	
}

//release this file pointer
fclose($theFilePointer);

//debug
echo "out cron_createTrainingFiles <br />"; 
	
?>
