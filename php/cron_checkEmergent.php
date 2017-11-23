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



//TODO get this ready for question also
//$emergentMode = "train";
//$emergentMode = "question";

//grab mode from argument of cron command
$emergentMode = $argv[1];

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
		$theError->details = "sizeof(activeQuestioningPatternSets):".sizeof($activeQuestioningPatternSets);
		$theError->add();

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
		$fp1 = fopen($theUtility->tempWriteablePath.'jobTicket.txt', 'r');
	
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
		//if there is a job ticket
		else
		{
			
			//set flag
			$isJobTicketFound = true;
			
			//debug	
			echo "found a job ticket <br>\n";
			
			//if the mode is train
			if ($emergentMode == "train")
			{
				//get rid of the last questioning file
				//unlink("/tmp/finishedQuestioning.txt");
				unlink($theUtility->tempWriteablePath."finishedQuestioning.txt");
				
			
				//debug
				echo "in mode train<br>\n";
				
				//TODO: determine the mode and the patternSet and trainingFile underway
				//TODO if in mode train
				
				//check the emergent process
				exec("pgrep emergent", $output, $return);
				
				//debug
				echo "sizeof(output): ".sizeof($output)."<br>\n";
				echo "output[0]: ".$output[0]."<br>\n";
				echo "output[1]: ".$output[1]."<br>\n";
				echo "return: ".$return."<br>\n";
			
				//if emergent running
				if ($return == 0)
				{
		   
					//debug
					echo "Ok, emergent process is running\n";
					
					//log an logentry
					$theError = new SolarError;
					$theError->module = "cron_checkEmergent";
					$theError->details = "emergent running as pid ".$output[0];
					$theError->add();
		
					//TODO
					//if the mode = train then get datum		
					
					// full path to text file
					define("TEXT_FILE", "/var/www/html/solarquant/emergent/log/".$theTrainingFile->emergentLogFileName);
					// number of lines to read from the end of file
					define("LINES_COUNT", 1);
					
					//TODO use the memory efficient version of text file reading
					
					//get the array of lines in the logfile
					$lines = $theUtility->read_file(TEXT_FILE, LINES_COUNT);
					
					foreach ($lines as $line) 
					{
						
						//debug
						echo "line:".$line."<br>\n";
						
						//create an array for this trial result	                                                            
						$emergentTrialResult = explode("\t", $line);
		    
						
						/* debug
						echo "emergentTrialResult[0]".$emergentTrialResult[0]."<br>";
						echo "emergentTrialResult[1]".$emergentTrialResult[1]."<br>";
						echo "emergentTrialResult[2]".$emergentTrialResult[2]."<br>";
						echo "emergentTrialResult[3]".$emergentTrialResult[3]."<br>";
						echo "emergentTrialResult[4]".$emergentTrialResult[4]."<br>";
						echo "emergentTrialResult[5]".$emergentTrialResult[5]."<br>";
						echo "emergentTrialResult[6]".$emergentTrialResult[6]."<br>";
						*/
						
						//set trainingDatum details
						$theTrainingDatum->trainingFileId = $theTrainingFile->id;
						$theTrainingDatum->batch = $emergentTrialResult[1];
						$theTrainingDatum->epoch = $emergentTrialResult[2];
						$theTrainingDatum->sse = $emergentTrialResult[3];
						
						//debug	
						echo "before add datum\n";
					
						//add the training datum
						$theTrainingDatum->add();
						
						//debug	
						echo "after add datum\n";
						
						//log an logentry
						$theError = new SolarError;
						$theError->module = "cron_checkEmergent";
						$theError->details = "just added trainingDatum \n";
						$theError->add();
												
					}  //end for each line of log file

				
					//log file not growing but static
				
					//log an logentry
					
					//set trainingDatum details
				
			} //emergent is running
			//if emergent is not running
			else
			{
				//debug	
				echo "Ok, emergent process is NOT running, in mode train\n";	
				
				//log an logentry
				$theError = new SolarError;
				$theError->module = "cron_checkEmergent";
				$theError->details = "emergent found not running in mode train";
				$theError->add();
				
				//IDEA how long ago was this job started? should something be triggered
				
				//compute the path of the intended output weights file
				$theWeightsFile = $theUtility->localAbsolutePath."emergent/weights/".$theTrainingFile->outputWeightsFileName.".wts";
				
				
				//log an logentry
				$theError = new SolarError;
				$theError->module = "cron_checkEmergent";
				$theError->details = "checking file system for weights file:".$theWeightsFile;
				$theError->add();
	
				//is there a resulting weights file on the file system?
				$fp2 = fopen($theWeightsFile, 'r');
				
				//debug	
				echo "fp2:".$fp2."<br>";
				
				//if there is no output weights file
				if ($fp2 == false)
				{
					//log an logentry
					$theError = new SolarError;
					$theError->module = "cron_checkEmergent";
					$theError->details = "intended output weights file from training ".$theWeightsFile." for training file".$theTrainingFile->id." was NOT found";
					$theError->add();
					
				}
				//we did find the output weights file
				else
				{
					//log an logentry
					$theError = new SolarError;
					$theError->module = "cron_checkEmergent";
					$theError->details = "intended logfile ".$theWeightsFile." for training file".$theTrainingFile->id." was found on file system";
					$theError->add();
					
					//get now								
					$startDateTime = strtotime("now");
					$showSaveTime = date("Y-m-d H:i:s",$startDateTime);
					
					//log an logentry
					$theError = new SolarError;
					$theError->module = "cron_checkEmergent";
					$theError->details = "about to update training file ".$theTrainingFile->id." inputWeightsFileName:".$theTrainingFile->inputWeightsFileName." outputWeightsFileName".$theTrainingFile->outputWeightsFileName;
					$theError->add();
					
					//update trainingFile status as processed
					//TODO centralize statuses
					$theTrainingFile->statusId = 2;
					$theTrainingFile->stopTraining = $showSaveTime;
					$theTrainingFile->update();
					
					//IDEA does the node need to updated with "mostRecentTrainingFile" property?
					
					//log an logentry
					$theError = new SolarError;
					$theError->module = "cron_checkEmergent";
					$theError->details = "training file ".$theTrainingFile->id." was processed";
					$theError->add();   
					
					//update patternSet status as completed successfully
					//TODO centralize statuses
					$thePatternSet->statusId = 6;
					$thePatternSet->update();
					
					//log an logentry
					$theError = new SolarError;
					$theError->module = "cron_checkEmergent";
					$theError->details = "PatternSet ".$thePatternSet->id." was completed successfully";
					$theError->add();
					
					//remove the job ticket and the runEmergentScript
					
					//debug	
					echo("before unlinking job ticket and the runEmergentScript<br>");
					
					//unlink("/var/www/html/solarquant/emergent/runEmergent1.sh");
					//unlink("/var/www/html/solarquant/emergent/jobTicket.txt");
				
					//unlink("/tmp/runEmergent1.sh");
					//unlink("/tmp/jobTicket.txt");
					//unlink("/tmp/emergentUnderway.txt");

					unlink($theUtility->tempWriteablePath."runEmergent1.sh");
					unlink($theUtility->tempWriteablePath."jobTicket.txt");
					unlink($theUtility->tempWriteablePath."emergentUnderway.txt");
					
					
					//log an logentry
					$theError = new SolarError;
					$theError->module = "cron_checkEmergent";
					$theError->details = "Just unlinked 3 main files runEmergent1.sh, jobticket.txt, emergentUnderway.txt";
					$theError->add();
					
					//debug	
					echo("after unlinking 3 files<br>");
					
					//TODO allow the question cronjob to happen now
					
					//write finished training
					$fp7 = fopen($theUtility->tempWriteablePath.'finishedTraining.txt', 'w');
					fwrite($fp7,  $theTrainingFile->id);
					fclose($fp7);
					
					
				} //we did find a weights file

			
				//set trainingDatum details
				
			} //emergent running
		
		}//mode = train
		elseif ($emergentMode == "question") //if the mode is question
		{
			
			//TODO if we're starting to read the result of the Q file we need to stop going any further here questioningUnderway
			
			//debug
			echo("in mode question<br>\n");
			
			//log an logentry
			$theError = new SolarError;
			$theError->module = "cron_checkEmergent";
			$theError->details = "starting in mode question";
			$theError->add(); 
			
			//if the file exist on the file system
			//$q2 = fopen('/tmp/openingQuestionFile.txt', 'r');
			$q2 = fopen($theUtility->tempWriteablePath.'openingQuestionFile.txt', 'r');
			
					
			//only if it doesn't exist yet
			if ($q2 == false)
			{
			
				//if we've already done the questioning
				//$finishedQuestioning = fopen('/tmp/finishedQuestioning.txt', 'r');
				$finishedQuestioning = fopen($theUtility->tempWriteablePath.'finishedQuestioning.txt', 'r');
				
				
				
				//if we've already done the training
				//$finishedTraining = fopen('/tmp/finishedTraining.txt', 'r');
				$finishedTraining = fopen($theUtility->tempWriteablePath.'finishedTraining.txt', 'r');
				
				//log an logentry
				$theError = new SolarError;
				$theError->module = "cron_checkEmergent";
				$theError->details = "finishedQuestioning:".$finishedQuestioning." finishedTraining:".$finishedTraining;
				$theError->add(); 
						
				//if we have not finished questioning but we have finished training
				//if (($finishedQuestioning == false) && ($finishedTraining == true))
				if (($finishedQuestioning == false) )
				{
				
					//read the contents of jobTicket
					//$jobTicketContents = fread($fp1, filesize('/tmp/jobTicket.txt'));
					$jobTicketContents = fread($fp1, filesize($theUtility->tempWriteablePath.'jobTicket.txt'));
					
					//debug
					echo "jobTicketContents:".$jobTicketContents."<br>\n";
					
					//log an logentry
					$theError = new SolarError;
					$theError->module = "cron_checkEmergent";
					$theError->details = "jobTicketContents:".$jobTicketContents;
					$theError->add(); 
					
					//create the array that holds the information about the current job
					$jobTicketArray = array();
					$jobTicketArray = explode(',',$jobTicketContents);
					
					//debug
					echo "jobTicketArray[1]:".$jobTicketArray[1]."<br>\n";
										
					//get the patternSets to check for
					$patternSetsToUpdate = explode(':',$jobTicketArray[1]);
				
					//log the patternSetsToUpdate
					$theError = new SolarError;
					$theError->module = "cron_checkEmergent";
					$theError->details = "patternSetsToUpdate ".$patternSetsToUpdate." found in jobTicket";
					$theError->add(); 
					
					//debug
					echo "patternSetsToUpdate count:".count($patternSetsToUpdate)."<br>\n";
					
					//loop through to check if all outputfiles exist on file system
					$k = 0;
					$filesFoundonFileSystem = array();
					while ($k < count($patternSetsToUpdate))
					{
					
						//construct the patternSet
						$thisPatternSet = new PatternSet();
						$thisPatternSet->id = $patternSetsToUpdate[$k]+0;
						$thisPatternSet->constructFromId();
						
						//debug
						echo "thisPatternSet->id:".$thisPatternSet->id."<br>\n";
						
						//logentry
						$theError = new SolarError;
						$theError->module = "cron_checkEmergent";
						$theError->details = "thisPatternSet->id:".$thisPatternSet->id;
						$theError->add();
	
						//construct the training file
						$thisTrainingFile = new TrainingFile();
						$thisTrainingFile->id = $thisPatternSet->trainingFiles[0];
						$thisTrainingFile->constructFromId();
						
												//log file name
						$theError = new SolarError;
						$theError->module = "cron_checkEmergent";
						$theError->details = "thisTrainingFile id: ".$thisTrainingFile->id." and outputFileName:".$thisTrainingFile->outputFileName;
						$theError->add();
						
						//$fullOutputFileName = "/var/www/html/solarquant/emergent/output/".$thisTrainingFile->outputFileName.".csv";
						//Altered to use the Q prefix
						//$fullOutputFileName = "/var/www/html/solarquant/emergent/output/Q".$thisTrainingFile->outputFileName.".csv";
						
						$fullOutputFileName = "/var/www/html/solarquant/emergent/output/".$thisTrainingFile->outputFileName.".csv";
						
						
						
						//log file name
						$theError = new SolarError;
						$theError->module = "cron_checkEmergent";
						$theError->details = "fullOutputFileName of the output file checking on file system: ".$fullOutputFileName." ";
						$theError->add();
						
						//debug
						echo "looking for fullOutputFileName:".$fullOutputFileName."<br>\n";
						
						//if the file exist on the file system
						$fp3 = fopen($fullOutputFileName, 'r');
						
						//if it doesn't exist yet
						if ($fp3 == false)
						{
							//debug
							echo "not found on file system:".$thisTrainingFile->outputFileName."<br>\n";
							
							//log file name
							$theError = new SolarError;
							$theError->module = "cron_checkEmergent";
							$theError->details = "NOT found on file system: ".$fullOutputFileName." exiting process";
							$theError->add();
						
							//break out of here
							//break;
							
						} //output file does not exist
						else //it does exist
						{
							//debug
							echo "YES found on file system:".$thisTrainingFile->outputFileName."<br>\n";
								
							//log file name
							$theError = new SolarError;
							$theError->module = "cron_checkEmergent";
							$theError->details = "yes found on file system: ".$fullOutputFileName." ";
							$theError->add();
							
							//add the file to an array
							$filesFoundonFileSystem[] = $thisTrainingFile->outputFileName;
							
						}  //output file does exist
						
						//move on to next patternSet
						$k++;
						
					}  //while loop through patternSets
				
				
				//if our array of files size = patterns to update
				if(count($filesFoundonFileSystem) == count($patternSetsToUpdate))
				{
					//debug
					echo "all ".count($filesFoundonFileSystem)." files found on file system, expected:".count($patternSetsToUpdate)."<br>\n";
					
					//log file name
					$theError = new SolarError;
					$theError->module = "cron_checkEmergent";
					$theError->details = "yes : ".count($filesFoundonFileSystem)." found on file system, expected :".count($patternSetsToUpdate);
					$theError->add();
					
					//start loop through files found on filesystem
					$m = 0;
					while ($m < count($filesFoundonFileSystem))
					{
						//debug
						echo "m:".$m."<br>\n";
						
						
						
						//$fullOutputFileName = "/var/www/html/solarquant/emergent/output/".$filesFoundonFileSystem[$m].".csv";	
						//Altered for Question
						//$fullOutputFileName = "/var/www/html/solarquant/emergent/output/Q".$filesFoundonFileSystem[$m].".csv";	
						$fullOutputFileName = "/var/www/html/solarquant/emergent/output/".$filesFoundonFileSystem[$m].".csv";	
						
						//log file name
						$theError = new SolarError;
						$theError->module = "cron_checkEmergent";
						$theError->details = "about to open output file : ".$fullOutputFileName." ";
						$theError->add();
					
						echo "fullOutputFileName:".$fullOutputFileName."<br>\n";
						
						//$homepage = file_get_contents($fullOutputFileName);
						
						//log file name
						//$theError = new SolarError;
						//$theError->module = "cron_checkEmergent";
						//$theError->details = "homepage ".$homepage;
						//$theError->add();
					
						//set var
						$lineCount = 0;
							
						//get a handle to the file
						$handle = fopen($fullOutputFileName, "r");
							
						//log file name
						$theError = new SolarError;
						$theError->module = "cron_checkEmergent";
						$theError->details = "opened handle ".$handle;
						$theError->add();
						
						//as long as we have opened the file
						if ($handle)
						{
							
							//log file name
							$theError = new SolarError;
							$theError->module = "cron_checkEmergent";
							$theError->details = "opened file ".$fullOutputFileName;
							$theError->add();
						
							// full path to text file
							//define("TEXT_FILE",$fullOutputFileName);
							//TODO use a number for the number of trials plus one for  lines to read from the end of file
							//define("LINES_COUNT", 60000);
							//$lines = $theUtility->read_file(TEXT_FILE, LINES_COUNT);
							
							//$lines = $theUtility->read_file($fullOutputFileName, LINES_COUNT);
						
							//set vars
							$lineNumber = 0;
							
							//foreach ($lines as $line) 
							//while (!feof($handle))
							
							//grab a line of the file
							while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
							{
								//determine the number of elements in the line
								$num = count($data);
								
								//loop through elements
								for ($c=0; $c < $num; $c++) 
								{ 
							   
									//grab the first element
									$buffer = $data[$c];
							   
									//debug
									echo "buffer:".$buffer . "<br />\n";
							
									//log file name
									$theError = new SolarError;
									$theError->module = "cron_checkEmergent";
									$theError->details = "$num fields in line $c: <br />\n";;
									$theError->add();
        
									//debug
									//echo $data[0] . "<br />\n";
									//$buffer = $data[0];
									
									//make an array of the buffer
									$emergentQuestionResult = explode("\t", $buffer);	
									
									//log file name
									$theError = new SolarError;
									$theError->module = "cron_checkEmergent";
									$theError->details = "thePredictedkWhMeasured will be from from emergentQuestionResult[6]:".$emergentQuestionResult[6]."<br />\n";;
									$theError->add();
							
									//on the first line
									if ($lineNumber == 0)
									{
										//set a flag so that we don't do in here again on the first insert
										//$q1 = fopen('/tmp/openingQuestionFile.txt', 'w');
										$q1 = fopen($theUtility->tempWriteablePath.'openingQuestionFile.txt', 'w');
										fwrite($q1,  $fullOutputFileName);
										fclose($q1);
										
										//debug
										echo "wrote openingQuestionFile.txt <br />\n";
									}
									else
									{					
							
										//debug
										
										echo "sizeof emergentQuestionResult:".sizeof($emergentQuestionResult) . "<br />\n";
										echo "emergentQuestionResult 0:".$emergentQuestionResult[0] . "<br />\n";
										echo "emergentQuestionResult 1:".$emergentQuestionResult[1] . "<br />\n";
										echo "emergentQuestionResult 2:".$emergentQuestionResult[2] . "<br />\n";
										echo "emergentQuestionResult 3:".$emergentQuestionResult[3] . "<br />\n";
										  
										//determine the trial we are on, the actual and predicted kWh values
										$theTrialName = trim(str_replace('"', "", $emergentQuestionResult[3]));
										$thePredictedkWhMeasured = $emergentQuestionResult[6];
										$theActualkWhMeasured = $emergentQuestionResult[7];
										
										//create a new ConsumptionPattern
										$theConsumptionPattern = new ConsumptionPatternSet();
										
										//set the properties for this 
										$theConsumptionPattern->trialName = $theTrialName;
										$theConsumptionPattern->predictedKiloWattHoursWeight = $thePredictedkWhMeasured + 0;
										$theConsumptionPattern->kiloWattHoursWeight = $theActualkWhMeasured + 0;
										
										//run the update
										$theConsumptionPattern->updatePredictedkWh();
									
									}
									
									//go to the next line
									$lineNumber++;
									
									echo "just indexed lineNumber:".$lineNumber . "<br />\n";


            
							//log file name
							//$theError = new SolarError;
							//$theError->module = "cron_checkEmergent";
							//$theError->details = "emergentQuestionResult 3:".$emergentQuestionResult[3]."<br>";
							//$theError->add();
							
							echo "theTrialName:".$theTrialName."<br>\n";
					
							 } //end for
								
								/*
											

							
							//log file name
					$theError = new SolarError;
					$theError->module = "cron_checkEmergent";
					$theError->details = "about to create buffer";
					$theError->add();
							
							//$buffer = fgets($handle, 4096);
							//$buffer = fgetcsv($handle, 4096);
							
							$buffer = $data[$c];
								
							//log file name
					$theError = new SolarError;
					$theError->module = "cron_checkEmergent";
					$theError->details = "after create buffer";
					$theError->add();		
							
					//log file name
					//$theError = new SolarError;
					//$theError->module = "cron_checkEmergent";
					//$theError->details = "buffer: ".$buffer;
					//$theError->add();
					
										//log file name
					$theError = new SolarError;
					$theError->module = "cron_checkEmergent";
					$theError->details = "buffer[0]: ".$buffer[0];
					$theError->add();
					
							//echo "line:".$line."<br>";
							
							//log file name
							$theError = new SolarError;
							$theError->module = "cron_checkEmergent";
							$theError->details = "opened Q file and sizeof(buffer)  : ".sizeof($buffer);
							$theError->add();

					
							
							$emergentQuestionResult = explode("\t", $buffer);
							
							//log file name
							$theError = new SolarError;
							$theError->module = "cron_checkEmergent";
							$theError->details = " sizeof(emergentQuestionResult)  : ".sizeof($emergentQuestionResult);
							$theError->add();
							
							echo "emergentQuestionResult 3:".$emergentQuestionResult[3]."<br>";
							echo "emergentQuestionResult 6:".$emergentQuestionResult[6]."<br>";
							echo "emergentQuestionResult 7:".$emergentQuestionResult[7]."<br>";
							
							$theTrialName = trim(str_replace('"', "", $emergentQuestionResult[3]));
							$thePredictedkWhMeasured = $emergentQuestionResult[6];
							$theActualkWhMeasured = $emergentQuestionResult[7];
							
							//log file name
							$theError = new SolarError;
							$theError->module = "cron_checkEmergent";
							$theError->details = "theTrialName  : ".$theTrialName ." thePredictedkWhMeasured:".$thePredictedkWhMeasured." theActualkWhMeasured:".$theActualkWhMeasured;
							$theError->add();
							
							//TODO look up consumption_input_pattern and update predicted value
							
							$theConsumptionPattern = new ConsumptionPatternSet();
							
							$theConsumptionPattern->trialName = $theTrialName;
							$theConsumptionPattern->predictedKiloWattHoursWeight = $thePredictedkWhMeasured + 0;
							$theConsumptionPattern->kiloWattHoursWeight = $theActualkWhMeasured + 0;
							
							//log file name
							$theError = new SolarError;
							$theError->module = "cron_checkEmergent";
							$theError->details = "about to run updatePredictedkWh";
							$theError->add();
							
							//$theConsumptionPattern->updatePredictedkWh();
							
							
							//$lineCount = $lineCount + substr_count($line, PHP_EOL);
							
							if ($lineNumber == 0)
							{
								//set a flag so that we don't do in here again on the first insert
								$q1 = fopen('/tmp/openingQuestionFile.txt', 'w');
								fwrite($q1,  $fullOutputFileName);
								fclose($q1);
							}
							
							$lineNumber++;
							
							//log file name
							$theError = new SolarError;
							$theError->module = "cron_checkEmergent";
							$theError->details = "lineNumber".$lineNumber."<br>";
							$theError->add();
							
							*/
							
							
							
						} //end while
						
					//log file name
					$theError = new SolarError;
					$theError->module = "cron_checkEmergent";
					$theError->details = "after loop through file lines";
					$theError->add();
					
						fclose($handle);
						
						} //if handle
						else
						{
							//log file name
							$theError = new SolarError;
							$theError->module = "cron_checkEmergent";
							$theError->details = "not able to open file ".$fullOutputFileName;
							$theError->add();
							
						} //else if not open handle
						
						//move on to the next file
						$m++;
						
					} //end while
					
												//log file name
							$theError = new SolarError;
							$theError->module = "cron_checkEmergent";
							$theError->details = "about to update patternsets to status 8 count(patternSetsToUpdate):".count($patternSetsToUpdate);
							$theError->add();
							
					
					//return status of patternSets to completedTraining
					$n = 0;
					while ($n < count($patternSetsToUpdate))
					{
						
						//construct the patternSet
						$thisPatternSet = new PatternSet();
						
						$thisPatternSet->id = $patternSetsToUpdate[$n]+0;
						
						echo "thisPatternSet->id:".$thisPatternSet->id."<br>";
						
						
						$thisPatternSet->constructFromId();
						//setting this to finished questioning
						$thisPatternSet->statusId = 8;
						
						//TODO add sourceIds as an argument here but blank
						$thisPatternSet->update();
						
						echo "after update statusId to 8 for thisPatternSet->id:".$thisPatternSet->id."<br>";
						
						$n++;
						
					}
					
					//get rid of files
					//unlink("/tmp/runEmergent1.sh");
					//unlink("/tmp/jobTicket.txt");
					//unlink("/tmp/emergentUnderway.txt");
					//unlink("/tmp/openingQuestionFile.txt");

					unlink($theUtility->tempWriteablePath."runEmergent1.sh");
					unlink($theUtility->tempWriteablePath."jobTicket.txt");
					unlink($theUtility->tempWriteablePath."emergentUnderway.txt");
					unlink($theUtility->tempWriteablePath."openingQuestionFile.txt");

					
					echo("after unlinking 4 files<br>");
					
					//write finished questioning
					//$fp5 = fopen('/tmp/finishedQuestioning.txt', 'w');
					$fp5 = fopen($theUtility->tempWriteablePath.'finishedQuestioning.txt', 'w');
					fwrite($fp5,  'done');
					fclose($fp5);
					
					
				}
				//
				else //if not the same number
				{
					echo "ONLY ".count($filesFoundonFileSystem)." files found on file system, expected:".count($patternSetsToUpdate)."<br>";
					//log an error saying only X of totalFiles found
					
					
				}
			
			
			
			} // if we have not finished questioning but we have finished training
			
			} // as long as we have not just opened the Q file
			else
			{
				
				//log file name
				$theError = new SolarError;
				$theError->module = "cron_checkEmergent";
				$theError->details = "not executing - found a openingQuestionFile.txt";
				$theError->add();
				
			}
			
		} //if mode question
		
		
	} //job ticket

}
//if no active patternSet
else {

	//set trainingDatum details?
	
	//log an logentry
	$theError = new SolarError;
	$theError->module = "cron_checkEmergent";
	$theError->details = "no activepatternsets or activeQuestioningPatternSets ";
	$theError->add();
	
	
	
	
} //active pattern set


//add trainingDatum

} // if chockfile pointer exists


                            
echo "OUT cron_checkEmergent<br>";

?>
