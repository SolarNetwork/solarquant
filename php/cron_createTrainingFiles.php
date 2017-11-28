<?php

//debug
echo "in cron_createTrainingFiles <br />"; 

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
echo "in cron_createTrainingFiles checking chockfile <br />"; 
		
//check the file system for the chock file
$theFilePointer = fopen($theUtility->createTrainingFileChockFile, 'r');

//only if the chockfile does exist yet
if ($theFilePointer == false)
{
	//log an logentry
	$theError = new SolarError;
	$theError->module = "cron_createTrainingFiles";
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
	$theError->module = "cron_createTrainingFiles";
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
			
			//get the notProcessed patternSets involved with this node
			$queuedPatternSetIds = $theNode->getPatternSetIds("notProcessed");
			
			//debug
			echo "count of queuedPatternSetIds :".count($queuedPatternSetIds)."\n"; 
			
			//log an logentry
			$theError = new SolarError;
			$theError->module = "cron_createTrainingFiles";
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
				$theError->module = "cron_createTrainingFiles";
				$theError->details = "for patternSet id".$thePatternSet->id." type is: ".$thePatternSet->patternSetTypeId."for this node to status beingProcessed";
				$theError->add();
										
				//TODO check if the status = 0 notProcessed
								
				//set the patternSet for this node to beingProcessed
				//TODO centralize statusIds
				$thePatternSet->statusId = 1;
				$thePatternSet->update();
				
				//log an logentry
				$theError = new SolarError;
				$theError->module = "cron_createTrainingFiles";
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
				
				//TODO this is where a new thread should be started to create the files rather than have it
				// as part of this thread. the next set of statuses are about creating normailised
				// weights and then a set of training files on the file system. For testing also it 
				// makes sense not having to go get more data all the time, but process the data that
				// we already have. Since all of thst work is already done in this file, it makes sense
				// to send a mode parameter into this cron event Currently we have a nonused cron event which
				// makes new empty pattern sets, which is not as relevant in the sense that training is 
				// cumulative. we do need a process that perhaps edits the end date for a PatternSet if it's
				// a dynamic one. Perhaps some PatternSets and created and that's it - but mostly if they 
				// are allowed to change with time then they could grow. there is no need to re-download 
				// all the data just what is new since we last downloaded. This could be an UpdatePatternSet
				// function to reset the enddate of every dynamic function but maybe you might as well grab
				// the new data while you're there as it would be incremental and there's no reason why 
				// this thread could not be happening at the same time as training or other downloading.
				
				// one activity which is going to be happening all the time is generating small virtual future
				// weathertime of the next 36 hours and bouncing it against a trained network. This might 
				// be the best use of GenerateNewPatternSets, but the logic is going to be similar to UpdatePetternSet
				// probably better to get that working first.
				
				// as weather predictions get better the closer you are to the event, there will also be multiple
				// futures for the next 36 hours, These should ideally be saved independently rather that getting
				// written over eachother. That means that each patternSet has its own set of PrediuctedkWh
				// and in visualising them we need to get a result set that may need to show all of them
				// on the screen at the same time.
				
				
				//otherwise already data downloaded so continue
				
				//log an logentry
				$theError = new SolarError;
				$theError->module = "cron_createTrainingFiles";
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
				$theError->module = "cron_createTrainingFiles";
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
				$theError->module = "cron_createTrainingFiles";
				$theError->details = "weightsMode: ".$weightsMode;
				$theError->add();
				
				//generate the weights
				$theConsumptionPatternSet->generateNNInputWeights($weightsMode);
				
				//set the patternSet for this node to NN weights generated
				//TODO centralize statusIds
				$thePatternSet->statusId = 3;
				$thePatternSet->update();
						
				//log an logentry
				$theError = new SolarError;
				$theError->module = "cron_createTrainingFiles";
				$theError->details = "generated NN weights for patternset ".$thePatternSet->id;
				$theError->add();

				//TODO
				// This is where a new thread should be created that prepares the training file
				
				//debug
				echo "after generateNNInputWeights :".$theConsumptionPatternSet->patternSetId."\n";
				echo "about to generate Trainingfile \n";
				
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
					
					//if we're dealing with a consumption type patternSet
					if (($thePatternSet->patternSetTypeId == 1) | ($thePatternSet->patternSetTypeId == 2))
					{
						//advance the status to be ready for training
						$thePatternSet->statusId = 4;
					}
					//if we're dealing with a forecast PatternSet
					elseif($thePatternSet->patternSetTypeId == 4)
					{
						//skip training and set status to be ready for questioning
						$thePatternSet->statusId = 6;
					}
					

					$thePatternSet->update();
					
					//log an logentry
					$theError = new SolarError;
					$theError->module = "cron_createTrainingFiles";
					$theError->details = "training file created on filesystem for patternset ".$thePatternSet->id;
					$theError->add();
					
					//update this patternSet as processed
					
				} catch (Exception $e) {
					
					//debug
					echo 'Caught exception at writeTrainingFile: ',  $e->getMessage(), "\n";
					
					//log an logentry
					$theError = new SolarError;
					$theError->module = "cron_createTrainingFiles";
					$theError->details = "ERROR: Could not write training file to file system";
					$theError->add();
					
				} //end transaction
				
				//debug
				echo("after writeTrainingFile <br>");
											
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
			$theError->module = "cron_createTrainingFiles";
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
		$theError->module = "cron_createTrainingFiles";
		$theError->details = "processed chocked, theFilePointer :";
		$theError->add();
	
}

//release this file pointer
fclose($theFilePointer);

//debug
echo "out cron_createTrainingFiles <br />"; 
	
?>
