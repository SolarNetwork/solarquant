<?php

//debug
echo "in cron_acquireTrainingData <br />"; 

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
echo "in cron_acquireTrainingData checking chockfile <br />"; 
		
//check the file system for the chock file
$theFilePointer = fopen($theUtility->createTrainingFileChockFile, 'r');

//only if the chockfile does exist yet
if ($theFilePointer == false)
{
	//log an logentry
	$theError = new SolarError;
	$theError->module = "cron_acquireTrainingData";
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
	$theError->module = "cron_acquireTrainingData";
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
		
		//check to see if there is anything in the queue with status ID 1
		$patternSetsInProcess = $theNode->getPatternSetIds("inProcess");
		
		//debug
		echo "count of patternSetsInProcess :".count($patternSetsInProcess)."<br />"; 

		//as long as there is nothing in process at the moment
		if (count($patternSetsInProcess) < 1)
		{
			//debug
			echo "zero patternSetsInProcess <br />"; 
			
			//TODO get the not processed OR the data downloaded 
			
			//get the notProcessed patternSets involved with this node
			$queuedPatternSetIds = $theNode->getPatternSetIds("notProcessed");
			
			//debug
			echo "count of queuedPatternSetIds :".count($queuedPatternSetIds)."\n"; 
			
			//log an logentry
			$theError = new SolarError;
			$theError->module = "cron_acquireTrainingData";
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
				$theError->module = "cron_acquireTrainingData";
				$theError->details = "for patternSet id".$thePatternSet->id." type is: ".$thePatternSet->patternSetTypeId."for this node to status beingProcessed";
				$theError->add();
										
				//TODO check if the status = 0 notProcessed
								
				//set the patternSet for this node to beingProcessed
				//TODO centralize statusIds
				$thePatternSet->statusId = 1;
				$thePatternSet->update();
				
				//log an logentry
				$theError = new SolarError;
				$theError->module = "cron_acquireTrainingData";
				$theError->details = "set patternSet ".$thePatternSet->id." for this node to status beingProcessed";
				$theError->add();
				
				//debug
				echo "after patternset update about to getMyDatum<br />"; 
				echo "thePatternSet->id :".$thePatternSet->id."<br />";

				//get appropriate datum based on type
				$thePatternSet->getMyDatum();
								
				//debug
				echo "after getMyDatum :".$thePatternSet->id."<br />";
						
				//set the patternSet for this node to data downloaded
				//TODO centralize statusIds
				$thePatternSet->statusId = 2;
				$thePatternSet->update();
				
                //the next step will be normalising the data
											
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
			$theError->module = "cron_acquireTrainingData";
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
		$theError->module = "cron_acquireTrainingData";
		$theError->details = "processed chocked, theFilePointer :";
		$theError->add();
	
}

//release this file pointer
fclose($theFilePointer);

//debug
echo "out cron_acquireTrainingData <br />"; 
	
?>
