<?php

// TODO check job ticket for mode parameter for train vs. question
//if train we want to produce training datums
//if question we can watch the log but more interested whether the output file is on the file system
// we want to gather the predicted Wh datum and save it to the consumption_input_pattern table 

//TODO - we're using file system flags here in the /tmp directory assuming that the machine has not been rebooted between tasks
// we probably need to replace these with logs to the DB, but they are generated from another process called by cron
// cron scripts probably need simple PHP actionpages that can write to the DB instead of the file system to record status/events

//jobTicket.txt is one way we still depend on the file system for scheduling training/questioning
//on a reboot maybe these files need to be actively replaced based on recorded status in DB?
//OR clever .sh script with pipes that checks the db directly? as in: mysql --login-path=storedPasswordKey
//running whole service in an S3 cloud would be good

echo "IN cron_checkEmergent<br>";

//grab mode from argument of cron command
//$emergentMode = $argv[1];
$emergentMode = "train";

//IDEA maybe grab mode from the contents of a file on file system?

//set defaults
$isJobTicketFound = false;

//debug
echo "after jobticketfound<br>\n";
echo "emergentMode:".$emergentMode."<br>\n";

//imports
require_once "/var/www/html/solarquant/classes/node.php";
require_once "/var/www/html/solarquant/classes/TrainingFile.php";
require_once "/var/www/html/solarquant/classes/TrainingDatum.php";
require_once "/var/www/html/solarquant/classes/SolarUtility.php";
require_once "/var/www/html/solarquant/classes/SolarError.php";
require_once "/var/www/html/solarquant/classes/PatternSet.php";
require_once "/var/www/html/solarquant/classes/ConsumptionPatternSet.php";

//debug
echo "after requires<br>\n";

//centralize authentication
$theUtility = new SolarUtility;
			
//get db connection
if ($link = " "){

	//$link = mysql_connect ($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword) or die ("Could not connect1");
	$link = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
}

//debug
echo "before file pointer<br />\n";
echo "checkEmergentChockFile: ".$theUtility->checkEmergentChockFile."<br />\n";

//check the file system for the chock file
$theFilePointer = fopen($theUtility->checkEmergentChockFile, 'r');	

//debug
echo "after file pointer: ".$theFilePointer."<br />\n";


//only if it does exist yet
if ($theFilePointer == false)
{
 
	//set vars
	$theNode = new Node();
	
	//debug
	echo "after node<br>\n";
	
	//set up a trainingDatum no matter what
	$theTrainingDatum = new TrainingDatum();
	
	//get active patternsets
	//TODO rename to activeTrainingPatternSets
	$activePatternSets = $theNode->getPatternSetIds("trainingFileUnderway");
	
	//debug	
	echo "sizeof(activePatternSets):".sizeof($activePatternSets)."<br>\n";
	
	//get active patternsets
	$activeQuestioningPatternSets = $theNode->getPatternSetIds("questioningFileUnderway");
	
	//debug	
	echo "in :".sizeof($activeQuestioningPatternSets)."<br>\n";

	//log an logentry
	$theError = new SolarError;
	$theError->module = "cron_checkEmergent";
	$theError->details = "sizeof(activePatternSets) ".sizeof($activePatternSets)." sizeof(activeQuestioningPatternSets):". sizeof($activeQuestioningPatternSets);
	$theError->add();
	
		//if there is an active patternSet of either kind
	if ( (sizeof($activePatternSets) > 0) or (sizeof($activeQuestioningPatternSets) > 0) )
	{
		
				//log an logentry
		$theError = new SolarError;
		$theError->module = "cron_checkEmergent";
		$theError->details = "emergentMode:".$emergentMode;
		$theError->add();
				
		//if the mode is train
		if ($emergentMode == "train")
		{
	
				
			//pick up the patternset
			$thePatternSet = new PatternSet;
			$thePatternSet->id = $activePatternSets[0];
			$thePatternSet->constructFromId();
			
			//debug	
			echo "found thePatternSet->id:".$thePatternSet->id."<br>";
			
			//TODO if in mode train get trainingFile log file
			$theTrainingFile = $thePatternSet->getMostRecentTrainingFile();
			
			echo "found theTrainingFile->id:".$theTrainingFile->id."<br>";
			echo "found theTrainingFile->emergentLogFileName:".$theTrainingFile->emergentLogFileName."<br>";
			echo "found theTrainingFile->outputWeightsFileName:".$theTrainingFile->outputWeightsFileName."<br>";
			
			//log an logentry
			$theError = new SolarError;
			$theError->module = "cron_checkEmergent";
			$theError->details = "mode is train and PatternSet underway is ".$thePatternSet->id."found theTrainingFile->id:".$theTrainingFile->id."found theTrainingFile->emergentLogFileName:".$theTrainingFile->emergentLogFileName."found theTrainingFile->outputWeightsFileName:".$theTrainingFile->outputWeightsFileName;
			$theError->add();
	
		} //if mode is train
		elseif ($emergentMode == "question")
		{
			//log an logentry
			$theError = new SolarError;
			$theError->module = "cron_checkEmergent";
			$theError->details = "mode is question";                             
			$theError->add();
			
			//debug	
			echo "mode is question<br>\n";
			
		}  //mode is question
		
				//TODO determine the best place for these - restart of machine loses /tmp
		//$fp1 = fopen('/var/www/html/solarquant/emergent/jobTicket.txt', 'r');
		//$fp1 = fopen('/tmp/jobTicket.txt', 'r');
		$jobTicketFullPath = $theUtility->tempWriteablePath."jobTicket.txt";
		
		//debug	
		echo "found jobTicketFullPath:".$jobTicketFullPath."<br>";
		
		$fp1 = fopen($jobTicketFullPath, 'r');
		
		echo "after open jobTicketFullPath".$fp1."<br>\n";
		
	
		//if there is no jobTicket
		if ($fp1 == false)
		{
			//debug	
			echo "no job ticket<br>\n";
			
			//log an logentry
			$theError = new SolarError;
			$theError->module = "cron_checkEmergent";
			$theError->details = "there is no job ticket found";
			$theError->add();
			
			//exit out completely
			//break;
		}
		else
		{
						//debug	
			echo "found ticket<br>\n";
			
		}  //found jobticket
		
		
	} // active patterns sets of either kind
	
} //end theFilePointer false



                            
echo "OUT cron_checkEmergent<br>";

?>
