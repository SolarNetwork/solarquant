<?php

//debug
echo "IN cron_startEmergentJob <br />"; 

// TODO send in a mode parameter for train vs. question
// in train mode, we want an output of weights file and a log file we can ready over a few hours

// in question mode we want an output file with the predicted value of the kwh weight
// held in the field consumption_input_pattern.predicted_kilowatt_hours_weight
// the job ticket file needs to hold which mode we are in
// because the question mode of emergent is much faster we might not reserve one questioning for each interval
// we might line up a large number of training files serially to be questioned and recorded, and do this once a day 
// for all training files that exist. we need a history of the comparison of STDEV between actual and predicted and 
// link this value to the training file (really network + output weights) with a timestamp so we can visualise progress

//debug
echo "before error reporting <br />"; 

//set error level
error_reporting(E_ERROR | E_PARSE);

//debug
echo "before imports <br />"; 

//imports
require_once "/var/www/html/solarquant/classes/SolarUtility.php";
require_once "/var/www/html/solarquant/classes/node.php";
require_once "/var/www/html/solarquant/classes/PatternSet.php";
require_once "/var/www/html/solarquant/classes/TrainingFile.php";
require_once "/var/www/html/solarquant/classes/SolarError.php";
  
//debug
echo "after imports <br />"; 

//instantiate objects
$theUtility = new SolarUtility;

//debug
echo "after utility<br />"; 



//2015.09.25 - with newly compiled emergent7 stable you need the full path or root cannot find it despite the .bashrc entries of:
// export LD_LIBRARY_PATH=$HOME/lib:/usr/lib:/usr/local/lib:$LD_LIBRARY_PATH

//set vars
//with emergent7
//$emergentCommandLine = "/home/jwgorman/emergent7/build7/bin/emergent7";  
//with emergent INTEL
$emergentCommandLine = "/home/solarquant/emergent/build/bin/emergent";
//with emergent 8 CUDA
//$emergentCommandLine = "/home/jwgorman/emergent/build_cuda/bin/emergent_cuda";

//$emergentCommandLine = "emergent7";
$emergentParameters = "-nogui -ni -proj";
$emergentProjectPath = "/var/www/html/solarquant/emergent/";
//with emergent 7
//$emergentProjectFile = "consumption_bp_nogui_20160321a.proj";
//with emergent 8
//$emergentProjectFile = "consumption_bp_nogui_20160321a_convert2.proj";
$emergentProjectFile = "consumption_bp_nogui_20160321a_5000epochs_0batches.proj";
//$emergentProjectFile = "consumption_bp_nogui_20160321a_500epochs_0batches.proj";
$numBatches = "1";
$emergentLogPath = "/var/www/html/solarquant/emergent/log/";
$emergentTag = "test1";
$emergentWeightsPath = "/var/www/html/solarquant/emergent/weights/";
$emergentInputFilePath = "/var/www/html/solarquant/emergent/inputFiles/";
$emergentOutputFilePath = "/var/www/html/solarquant/emergent/output/";

//TODO get this ready for question also
//$emergentMode = "train";
//$emergentMode = "question";

//debug
echo "before emer mode<br />"; 

//catch the mode vai the command line argument
$emergentMode = $argv[1];

//debug
echo "after emergent mode<br />"; 
echo ("emergentMode:".$emergentMode."<br>");

//debug
//$theJobTicketFile = $theUtility->localAbsolutePath."emergent/jobTicket.txt";

//TODO set these paths in an .ini file in best practice way
//$theJobTicketFile = "/tmp/jobTicket.txt";
//$emergentUnderwayFile = "/tmp/emergentUnderway.txt";

$theJobTicketFile = $theUtility->tempWriteablePath."jobTicket.txt";
$emergentUnderwayFile = $theUtility->tempWriteablePath."emergentUnderway.txt";



//debug
echo "before database<br />"; 

//get db connection
if ($link = " "){
	//centralize authentication
	$theUtility = new SolarUtility;
	//$link = mysql_connect ($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword) or die ("Could not connect1");
	$link = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
}

//debug
echo "before file pointer<br />";

//debug
echo "createEmergentScriptChockFile: ".$theUtility->createEmergentScriptChockFile."<br />";

//check the file system for the chock file
$theFilePointer = fopen($theUtility->createEmergentScriptChockFile, 'r');	

//debug
echo "after file pointer: ".$theFilePointer."<br />";

//only if it does exist yet
if ($theFilePointer == false)
{
	//debug
	echo " file pointer is false: ".$theFilePointer."<br />";
	
	//create a node
	$theNode = new Node;
	
	//only create a start emergent file if there is NOT patternsets in questioning OR we're finished with questioning
	$questioningFilesUnderway = $theNode->getPatternSetIds("questioningFileUnderway");
	
	//as long as there isn't a file underway
	if (count($questioningFilesUnderway) == 0)
	{
		//get subscribed nodes
		$subscribedNodes = $theNode->getSubscribedNodes();
		
		//debug
		echo ("subscribedNodes:".$subscribedNodes."<br>");
		echo "count of subscribedNodes :".count($subscribedNodes)."\n"; 
		
		//determine whether there is one in process now
		$noneInProcess = true;
		
		//set vars	
		$i = 0;
		
		//loop through subscribers
		while (($i < count($subscribedNodes)) & $noneInProcess )
		{
			//debug
			echo "i :".$i."\n"; 
			
			//set up this node
			$theNode->id = $subscribedNodes[$i];
			$theNode->constructFromId();
			
			//get patterns underway
			$patternSetsCurrentlyUnderway = $theNode->getPatternSetIds("trainingFileUnderway"); 
			
			//debug
			echo ("size of patternSetsCurrentlyUnderway :".sizeof($patternSetsCurrentlyUnderway)."\n");
							
			//if there is one underway
			if (sizeof($patternSetsCurrentlyUnderway) == 1)
			{
				$noneInProcess = false;
				
				//log error
				$theError = new SolarError;
				$theError->module = "cron_startEmergentJob";
				$theError->details = "there is a patternset presently underway";
				$theError->add();
				
				//exit this while loop
				break;
			} //if one underway
			//if there is more than one underway
			if (sizeof($patternSetsCurrentlyUnderway) > 1)
			{
				//set flag
				$noneInProcess = false;
				
				//debug
				echo ("about to add error<br>");
				
				//log error
				$theError = new SolarError;
				$theError->module = "cron_startEmergentJob";
				$theError->details = "more than one PatternSet currently underway";
				$theError->add();
				 
				//debug
				echo ("added error<br>");
				
				//exit this while loop
				break;
				
				
			} //if more than one underway
			//if there are none underway
			if (sizeof($patternSetsCurrentlyUnderway) == 0)
			{
				
				//leave noneInProcess alone
				echo ("no patternsets currently underway<br>");
				
				
			} //maybe move this to the bottom so all logic is contained within this IF
								
			//TODO we need to get the completed files here if in mode question
			
			//if we're training
			if ($emergentMode == "train")
			{
				$theStatusOfPatternSets = "trainingFileCreated";
			}
			elseif ($emergentMode == "question")
			{
				$theStatusOfPatternSets = "completedSuccessfully";
			}
			else
			{
				//debug
				echo ("ERROR in emergentMode setting<br>");
			}
			//check to see if there is anything in the queue
			$patternSetsWithTrainingFiles = $theNode->getPatternSetIds($theStatusOfPatternSets);
			
			//debug
			echo "count of patternSetsWithTrainingFiles :".count($patternSetsWithTrainingFiles)."\n"; 
			
			//count how many patternsets exist with training files
			$patternSetsWithTrainingFilesCount = count($patternSetsWithTrainingFiles);
			
			//log error
			$theError = new SolarError;
			$theError->module = "cron_startEmergentJob";
			$theError->details = "in mode:".$emergentMode." and patternSetsWithTrainingFilesCount:".$patternSetsWithTrainingFilesCount;
			$theError->add();

			//TODO use this to record workflow later - we don't know the sequence of how one solarquant server will serially
			// process the set of jobs it has accumulated so TBD
			
			//we need at least one patternset
			if ($patternSetsWithTrainingFilesCount > 0)
			{
				//if we're training
				if ($emergentMode == "train")
				{
					//only deal with one as we'll be questioning this next
					$howManyPatternSetsToLoopThrough = 1;
				}
				elseif ($emergentMode == "question")
				{
					$howManyPatternSetsToLoopThrough = $patternSetsWithTrainingFilesCount;
				}
				
				//loop through PatternSets
				//TODO - in mode train we only want to schedule one patternSetsWithTrainingFiles to run by emergent
				// in mode question we want to produce a fullExecutionString that includes all of the patternSetsWithTrainingFiles there are
				// with forecasting that means bouncing the latest weather-time data against the network using the most recent weights file
				// we don't know how long we can use a weights file before it is stale. currently this value is going to be 1 also
				// as alternate between train-question-train-question
				
				//debug
				echo "howManyPatternSetsToLoopThrough :".$howManyPatternSetsToLoopThrough."\n"; 
				
				//log error
				$theError = new SolarError;
				$theError->module = "cron_startEmergentJob";
				$theError->details = "in mode:".$emergentMode." and howManyPatternSetsToLoopThrough:".$howManyPatternSetsToLoopThrough;
				$theError->add();
				
				//set vars
				$j = 0;
				
				//loop through the number of patterns we have
				while ($j < $howManyPatternSetsToLoopThrough)
				{
					//debug
					echo "j :".$j."<br />";
					echo "patternSetsWithTrainingFiles[j] :".$patternSetsWithTrainingFiles[$j]."\n"; 
					
					//put these within a try in case we fail miserably
					try
					{  
						//debug
						echo "before new patternset <br />"; 
						
						//grab a PatternSet and construct
						$thePatternSet = new PatternSet;
						
						//debug
						echo "after new patternset <br />"; 
						
						//set the id to the next one and instantiate
						$thePatternSet->id = $patternSetsWithTrainingFiles[$j];
						$thePatternSet->constructFromId();
							
						//get most recent TrainingFile
						$theTrainingFile = new TrainingFile;
						$theTrainingFile = $thePatternSet->getMostRecentTrainingFile();
						
						//debug
						echo "after getMostRecentTrainingFile <br />";
						echo "theTrainingFile->id :".$theTrainingFile->id."<br />"; 
						echo "theTrainingFile->filename :".$theTrainingFile->filename."<br />"; 
						echo "theTrainingFile->inputWeightsFileName :".$theTrainingFile->inputWeightsFileName."<br />"; 
						echo "theTrainingFile->outputWeightsFileName :".$theTrainingFile->outputWeightsFileName."<br />"; 
						echo "theTrainingFile->patternSetId :".$theTrainingFile->patternSetId."<br />"; 
						
						//get this node
						$thisNode = new Node();
						$thisNode->id = $thePatternSet->nodes[0];
						$thisNode->constructFromId();
							
						//blank trainingFile
						$theLastTrainingFile = new TrainingFile;
						
						//get the most recently processed trainingFile for this node
						$theLastTrainingFile = $thisNode->getMostProcessedRecentTrainingFile();
						
						//TODO are we sure the weights file exists on file system and OK?
						
						//use the weights file from this processed trainingFile as the inputWeightsfFile for this trainingFile
						$theTrainingFile->inputWeightsFileName = $theLastTrainingFile->outputWeightsFileName;
						
						//log error
						$theError = new SolarError;
						$theError->module = "cron_startEmergentJob";
						$theError->details = "in mode:".$emergentMode."just set inputWeightsFileName for trainingfile id:".$theTrainingFile->id." to the outputWeightsFileName:".$theTrainingFile->outputWeightsFileName;
						$theError->add();
						
						//TODO - why is this commented out?? update this training file
						//$theTrainingFile->update();
							
						//get timestanp date ready								
						$startDateTime = strtotime("now");
						$showSaveTime = date("Y-m-d H:i:s",$startDateTime);
						$fileNameSaveTime = date("Y-m-d_H:i:s",$startDateTime);
						
						//debug
						echo "fileNameSaveTime :".$fileNameSaveTime."<br />"; 
						
						//set properties of the weights file
						$emergentInputWeightsFileName = $theTrainingFile->inputWeightsFileName.".wts";
						
						//debug
						//$theTrainingFile->inputWeightsFileName = $emergentInputWeightsFileName;
						
						//make sure we send a blank for the input_weights_file string if file is empty
						if (strlen(trim($theTrainingFile->inputWeightsFileName)) == 0 )
						{
							//we don't have a weights file to pass to emergent and that is OK
							$fullInputWeightsFileString = "";
						}
						else
						{
							//we do have a weights file so get it ready for the emergent call
							$fullInputWeightsFileString = $emergentWeightsPath.$emergentInputWeightsFileName;
						}
							
						//if we're training
						if ($emergentMode == "train")
						{	
							
							//set the value of the output weights file name	
							$emergentOutputWeightsFileName = "OW_PS_".$thePatternSet->id."_TF_".$theTrainingFile->id."_".$fileNameSaveTime;
							$theTrainingFile->outputWeightsFileName = $emergentOutputWeightsFileName;
							
							//log error
							$theError = new SolarError;
							$theError->module = "cron_startEmergentJob";
							$theError->details = "in mode:".$emergentMode."just set outputWeightsFileName for trainingfile id:".$theTrainingFile->id." to:".$theTrainingFile->outputWeightsFileName;
							$theError->add();
						
						}
						elseif ($emergentMode == "question")
						{
							//log error
							$theError = new SolarError;
							$theError->module = "cron_startEmergentJob";
							$theError->details = "in mode:".$emergentMode."did not set outputWeightsFileName - value is:".$theTrainingFile->outputWeightsFileName;
							$theError->add();
							
						}
						

						
						//debug
						echo "before setting emergentInputFileName theTrainingFile->filename :".$theTrainingFile->filename."<br />"; 
						
						//set the value of the input file name to the training file name
						$emergentInputFileName = $theTrainingFile->filename;
						
						//debug
						echo "after setting emergentInputFileName emergentInputFileName :".$emergentInputFileName."<br />"; 
						
						//set the value of the training file input file name
						$theTrainingFile->inputFileName = $emergentInputFileName;
						
						//if we're training
						if ($emergentMode == "train")
						{	
							//name the file related to the Output File (OF) for this PatternSet (PS) and this Training File (TF)
							$emergentOutputFileName = "OF_PS_".$thePatternSet->id."_TF_".$theTrainingFile->id;
						}
						elseif ($emergentMode == "question")
						{
							//name the file related to the Questioning Output File (OF) for this PatternSet (PS) and this Training File (TF)
							$emergentOutputFileName = "QOF_PS_".$thePatternSet->id."_TF_".$theTrainingFile->id;
						}
						
						//set the value of the training file output name
						$theTrainingFile->outputFileName = $emergentOutputFileName;
						
						//set the value of the logfile
						$emergentLogFileName = "LOG_PS_".$thePatternSet->id."_TF_".$theTrainingFile->id;
						
						//set the value of the training file's log file
						$theTrainingFile->emergentLogFileName = $emergentLogFileName;
						
						//log error
						$theError = new SolarError;
						$theError->module = "cron_startEmergentJob";
						$theError->details = "in mode:".$emergentMode." about to update the trainingfile id:".$theTrainingFile->id." emergentOutputFileName:".$theTrainingFile->emergentOutputFileName;
						$theError->add();
							
						//update trainingFile for the output filename to be created
						//TODO - why is this commented out??
						$theTrainingFile->update();

						//debug
						echo exec('whoami')."<br>";
						
						//TODO get PatternSet related arguments
						//$emergentInputWeightsFileName = "consumption_bp_nogui_20141222a.wts.gz";
						
						//debug
						echo ("right before exec fullExecutionString<br>");
						
						//set the path before we call the command
						$pathExportCommand = " export LD_LIBRARY_PATH=\$HOME/lib:/usr/lib:/usr/local/lib:\$LD_LIBRARY_PATH ";
						
						//TODO if mode is train it's just one line, 
						
						//if we are training
						if ($emergentMode == "train")
						{	
							//debug
							echo "in mode train emergentInputFileName :".$emergentInputFileName."<br />"; 
							
							//set the full execution string for emergent
							$fullExecutionString .= $emergentCommandLine." ".$emergentParameters." ".$emergentProjectPath.$emergentProjectFile." batches=".$numBatches." log_dir=log log_file_nm=".$emergentLogPath.$emergentLogFileName." tag=".$emergentTag." input_weights_file=".$fullInputWeightsFileString." output_weights_file=".$emergentWeightsPath.$emergentOutputWeightsFileName." input_file=".$emergentInputFilePath.$emergentInputFileName." output_file=".$emergentOutputFilePath.$emergentOutputFileName." mode=".$emergentMode;
							
						}
						//if mode question then append text - loop through all available patternSetsWithTrainingFiles and no output weights file
						elseif ($emergentMode == "question")
						{
							//set the full execution string for emergent
							$fullExecutionString .= $emergentCommandLine." ".$emergentParameters." ".$emergentProjectPath.$emergentProjectFile." batches=".$numBatches." log_dir=log log_file_nm=".$emergentLogPath.$emergentLogFileName." tag=".$emergentTag." input_weights_file=".$fullInputWeightsFileString." input_file=".$emergentInputFilePath.$emergentInputFileName." output_file=".$emergentOutputFilePath.$emergentOutputFileName." mode=".$emergentMode;
							$fullExecutionString .= "\n\n";
							
						}
							
							
							
							
							
					}  //end try
					catch (Exception $e)
					{
						//debug						
						echo ("Exception:".$e."<br>");
						
						//TODO log an error here
						
					}
					
					
					
					
					$j++;
					
				} //end while loop through patterns
				
			} //as long as there is one
			
			// if we have patternsets with training files	
			if ($patternSetsWithTrainingFilesCount > 0)
			{
				//set flag
				$noneInProcess = false;	
				
				//log error
				$theError = new SolarError;
				$theError->module = "cron_startEmergentJob";
				$theError->details = "in mode:".$emergentMode." and since we have found training files to process we're setting the noneInProcess flag as true (about to process it)";
				$theError->add(); 
			}
			
			//index to next subscriber
			$i++;
			
		} //end while we have subscribers
			
		//only write this file if there were count($patternSetsWithTrainingFiles
		if ($patternSetsWithTrainingFilesCount > 0)
		{
			
			//debug
			echo ("fullExecutionString:".$fullExecutionString."<br>");
			
			//set contents of the jobTicket file to the mode
			$jobTicketContents = $emergentMode;
			
			//if we're training just leave the patternSet we're on
			if ($emergentMode == "train")
			{
				//make the second parameter of the jobTicket file the patternSet we're training on
				$jobTicketContents .= ",".$theTrainingFile->id;
			}
			//if we're questioning, leave the patternsets we're questioning
			elseif ($emergentMode == "question")
			{
				//make the seccond parameter the delimited set of patternsets with training files
				$jobTicketContents .= ",".implode(":",$patternSetsWithTrainingFiles);
			}
							
			//debug	
			echo ("jobTicketContents:".$jobTicketContents."<br>");
			
			//log error
			$theError = new SolarError;
			$theError->module = "cron_startEmergentJob";
			$theError->details = "setting jobTicket contents to:".$jobTicketContents;
			$theError->add(); 
			
			//write files to filesystem
			writeRunEmergentScript($theJobTicketFile,$jobTicketContents,$emergentUnderwayFile,$pathExportCommand,$fullExecutionString,$theUtility->runEmergentScriptChockFile);
			
			//if we're training
			if ($emergentMode == "train")
			{
				
				//update status of training file
				//TODO global these status values somehow
				$theTrainingFile->statusId = 1;
				$theTrainingFile->startTraining = $showSaveTime;
				$theTrainingFile->update();
				
				//update status of patternset
				//TODO global these status values somehow
				$thePatternSet->statusId = 5;
				$thePatternSet->update();
				
				//log error
				$theError = new SolarError;
				$theError->module = "cron_startEmergentJob";
				$theError->details = "in mode:".$emergentMode." and just updated the training file to underway and the patternSet to status:".$thePatternSet->statusId;
				$theError->add(); 
				
			} //if we're in mode question
			elseif ($emergentMode == "question")
			{
					
				//update status of patternset
				
				//debug				
				echo ("count(patternSetsWithTrainingFiles):".count($patternSetsWithTrainingFiles)."<br>");
				
				//set vars
				$k = 0;
				
				//loop through patternSets with training files
				while ($k < count($patternSetsWithTrainingFiles))
				{
					//debug
					echo ("k:".$k."<br>");
					echo ("patternSetsWithTrainingFiles[k]:".$patternSetsWithTrainingFiles[$k]."<br>");
					
					//construct this patternSet
					$theQuestionPatternSet = new PatternSet();
					$theQuestionPatternSet->id = $patternSetsWithTrainingFiles[$k];
					$theQuestionPatternSet->constructFromId();
					
					//now questioning is underway
					//TODO global these status values somehow
					$theQuestionPatternSet->statusId = 7;
					$theQuestionPatternSet->update();
					
					//index to the next patternSet
					$k++;
					
				} //end loop through patternSets with training files					
					
				//log error
				$theError = new SolarError;
				$theError->module = "cron_startEmergentJob";
				$theError->details = "in mode:".$emergentMode." and just updated the training file to underway and the patternSet to status 7";
				$theError->add(); 					
					
			} //mode is question
			else
			{
				//log error
				$theError = new SolarError;
				$theError->module = "cron_startEmergentJob";
				$theError->details = "in unknown mode:".$emergentMode." and not updating the training file or the patternSet to trainingUnderway";
				$theError->add(); 
				
			} //if mode
				
				
				
		}  //as long as there were patternsets found
		else  //there were no patternSets found
		{
			//log error
			$theError = new SolarError;
			$theError->module = "cron_startEmergentJob";
			$theError->details = "in mode:".$emergentMode." and there were no patternsets found";
			$theError->add(); 
		}  //if patternSets found
		
	}  // only iif count($questioningFilesUnderway) > 0
	else // no questioningFilesUnderway underway
	{
	
			//log an logentry
			$theError = new SolarError;
			$theError->module = "cron_startEmergentJob";
			$theError->details = "there are questioningFilesUnderway";
			$theError->add();
		
	} //if questioningFilesUnderway
		
} //theFilePointer does not exist
else  //filepointer exists
{

		//log an logentry
		$theError = new SolarError;
		$theError->module = "cron_startEmergentJob";
		$theError->details = "processed chocked, theFilePointer true";
		$theError->add();
	
}  //file pointer exists
		
//release this file pointer
fclose($theFilePointer);

//function to encapsulate the writing of the emergent script based on parameters		
function writeRunEmergentScript($theJobTicketFile,$jobTicketContents,$emergentUnderwayFile,$pathExportCommand,$fullExecutionString,$chockFile)
{
    	//TODO add the condition of the the jobTicket.txt file existing before being able to execute
			//create utility
		$theUtility = new SolarUtility;
    	
	$shellScriptContents = "
	#!/bin/bash
	
	# create a random file before checking the ticket
	#for i in 1
	#do
	#FILE=\"/tmp/beforeCheckTicket.\$RANDOM.txt\"
	#> \$FILE # create files
	#done 
	
	file3=\"$chockFile\"
	if [ -f \"\$file3\" ]
	then
	
	touch ".$theUtility->tempWriteablePath."chockFileFound.txt
	
	else
	
	file=\"$theJobTicketFile\"
	if [ -f \"\$file\" ]
	then
	
		file2=\"$emergentUnderwayFile\"
		if [ -f \"\$file2\" ]
		then
	
			echo \"\$file2 found emergent already possibly running.\"
	
		else
	
			touch ".$theUtility->tempWriteablePath."emergentUnderway.txt
		
			echo \"\$file found starting emergent.\"
			$pathExportCommand
			$fullExecutionString
	
		
	
		fi
	
	
	else
		echo \"\$file not found do nothing.\"
	
	
	
	fi
	
	fi
	
	";
	
	
	
	//write messageDigest to a file
	//TODO: set the path for this file globally
	//$fp1 = fopen('/tmp/runEmergent1.sh', 'w');
	$fp1 = fopen($theUtility->tempWriteablePath.'runEmergent1.sh', 'w');
	
	
	fwrite($fp1,  $shellScriptContents);
	fclose($fp1);
	
	//change the file to executable 
	//TODO: set the path for this file globally
	echo exec('chmod a+x '.$theUtility->tempWriteablePath.'runEmergent1.sh')."<br>";
	
	//debug	
	echo ("before jobTicketFullFileName theJobTicketFile :".$theJobTicketFile."<br>");
	
	//write messageDigest to a file
	$fp2 = fopen($theJobTicketFile, 'w');
	fwrite($fp2,  $jobTicketContents);
	fclose($fp2);
	
	//debug
	echo ("after writing $theJobTicketFile jobTicketContents :".$jobTicketContents."<br>");
	
	//log error
	$theError = new SolarError;
	$theError->module = "cron_startEmergentJob::writeRunEmergentScript";
	$theError->details = "finished writing all files to file system";
	$theError->add();
	
	//debug
	echo ("after jobTicketFullFileName write <br>");
	echo ("right after exec fullExecutionString<br>");
 
  }

//debug
echo "OUT cron_startEmergentJob <br />"; 

?>
