<?php

//debug
echo "in cron_normaliseTrainingData <br />"; 

//TODO add logfile entries for this process
//TODO need to be able to run this command with a mode input that creates future/virtual training files only for questioning
// in that case, must use weather forecast data to generate virtual weatherdatum and tag those training files as forecasts
// rather than go get real weather datum.

//imports
require_once "/var/www/html/solarquant/classes/node.php";
require_once "/var/www/html/solarquant/classes/PowerDatum.php";
require_once "/var/www/html/solarquant/classes/SolarUtility.php";
require_once "/var/www/html/solarquant/classes/PatternSet.php";
require_once "/var/www/html/solarquant/classes/ConsumptionPatternSet.php";
require_once "/var/www/html/solarquant/classes/SolarError.php";

//write to training log
$messageDigest = date("Y-m-d H:i:s")." start of create training files "."\n" ;

//debug
echo "messageDigest :".$messageDigest."<br />"; 
		
//write messageDigest to a file
$fp1 = fopen('/var/www/html/solarquant/emergent/output/training_log.txt', 'a');
fwrite($fp1, $messageDigest);
		
//get db connection
if ($link = " "){
	//centralize authentication
	$theUtility = new SolarUtility;
	//$link = mysql_connect ($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword) or die ("Could not connect1");
	$link = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
}

//debug
echo "in cron_normaliseTrainingData checking chockfile <br />"; 
		
//check the file system for the chock file
$theFilePointer = fopen($theUtility->createTrainingFileChockFile, 'r');

//only if the chockfile does exist yet
if ($theFilePointer == false)
{
	//log an logentry
	$theError = new SolarError;
	$theError->module = "cron_normaliseTrainingData";
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
	$theError->module = "cron_normaliseTrainingData";
	$theError->details = "found ".count($subscribedNodes)." subscribedNodes";
	$theError->add();
			
	//so far noneInProcess
	$noneInProcess = true;
	
	//debug
	echo "about to loop through subscribers <br />";
		
	//loop through subscribers
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
			
			//get the notProcessed patternSets involved with this node that are in status 2
			$queuedPatternSetIds = $theNode->getPatternSetIds("dataDownloaded");
			
			//debug
			echo "count of queuedPatternSetIds :".count($queuedPatternSetIds)."\n"; 
			
			//log an logentry
			$theError = new SolarError;
			$theError->module = "cron_normaliseTrainingData";
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

				
				//log an logentry
				$theError = new SolarError;
				$theError->module = "cron_normaliseTrainingData";
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
				$theError->module = "cron_normaliseTrainingData";
				$theError->details = "after constructFromPatternSetId, startDate: ".$thePatternSet->startDate;
				$theError->add();
				
				//if we're dealing with a consumption type patternSet
				if ($theConsumptionPatternSet->patternSetTypeId == 1)
				{
					$weightsMode = "actual";
				}
				//if we're dealing with a generation type patternSet
				elseif ($theConsumptionPatternSet->patternSetTypeId == 2)
				{
					$weightsMode = "actual";
				}
				//if we're dealing with a forecast PatternSet
				elseif($theConsumptionPatternSet->patternSetTypeId == 4)
				{
					$weightsMode = "virtual";
				}
				
				//log an logentry
				$theError = new SolarError;
				$theError->module = "cron_normaliseTrainingData";
				$theError->details = "weightsMode: ".$weightsMode;
				$theError->add();
				
				//generate the weights
				$theConsumptionPatternSet->generateNNInputWeights($weightsMode);
				
				//set the patternSet for this node to NN weights generated
				//TODO centralize statusIds
				$thePatternSet->statusId = 3;
				$thePatternSet->update();
						
				
											
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
			$theError->module = "cron_normaliseTrainingData";
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
		$theError->module = "cron_normaliseTrainingData";
		$theError->details = "processed chocked, theFilePointer :";
		$theError->add();
	
}

//release this file pointer
fclose($theFilePointer);

//debug
echo "out cron_normaliseTrainingData <br />"; 
	
?>
