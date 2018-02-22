<?php

//require_once "/var/www/html/solarquant/classes/node.php";
//require_once "/var/www/html/solarquant/classes/ConsumptionDatum.php";
//require_once "/var/www/html/solarquant/classes/PowerDatum.php";
require_once "/var/www/html/solarquant/classes/SolarUtility.php";
require_once "/var/www/html/solarquant/classes/SolarError.php";

//Training file statuses

//-1 = terminated
// 0 = unprocessed
// 1 = in process
// 2 = processed

class TrainingFile {

    var $id;
    var $filename;
    var $createdOn;
    var $title;
    var $notes;
    var $statusId;
    var $patternSetId;
    var $inputWeightsFileName;
    var $outputWeightsFileName;
    var $inputFileName;
    var $outputFileName;
    var $emergentLogFileName;
    var $startTraining;
    var $stopTraining;
    var $dbLink;
    
    function connectToDB()
    {
    	$theUtility = new SolarUtility;
    	$this->dbLink = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
    }
    
    //TODO add a longtext field for weights file contents? 4GB limit
    
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
    	
    
		/* setup sql*/
		$sql = "insert into training_file (filename, created_on, title, notes, status_id, pattern_set_id, input_weights_file_name, output_weights_file_name, input_file_name, output_file_name, emergent_log_file_name, start_training, stop_training) values (\"$this->filename\",\"$this->createdOn\",\"$this->title\",\"$this->notes\",$this->statusId,$this->patternSetId,\"$this->inputWeightsFileName\",\"$this->outputWeightsFileName\",\"$this->inputFileName\",\"$this->outputFileName\",\"$this->emergentLogFileName\",\"$this->startTraining\",\"$this->stopTraining\")";

		echo("add training file sql:". $sql. "<br>");
		
		//create utility
		$theUtility = new SolarUtility;

		//break;
		/* execute sql*/
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("training file insert sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
		
		//$this->id = mysql_insert_id();
		$this->id =  $this->dbLink->insert_id;
		

    
    }
    function update()
    {
    	       	    echo("in trainingFile update <br>");
    	 
    	       	        
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
		$sql = "update training_file 
		set 
		filename = '".$this->filename."', 
		created_on = '".$this->createdOn."', 
		title = '".$this->title."', 
		notes = '".$this->notes."',
		status_id = ".$this->statusId.",
		pattern_set_id = ".$this->patternSetId.",
		input_weights_file_name = '".$this->inputWeightsFileName."',
		output_weights_file_name = '".$this->outputWeightsFileName."',
		input_file_name = '".$this->inputFileName."',
		output_file_name = '".$this->outputFileName."',
		emergent_log_file_name = '".$this->emergentLogFileName."',
		start_training = '".$this->startTraining."',
		stop_training = '".$this->stopTraining."' 
		where training_file_id = ".$this->id;

		
		echo("trainingFile update sql:". $sql. "<br>");
		
				
		//create utility
		$theUtility = new SolarUtility;
		
		// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("trainingFile update sql failed"); 
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
		
    }
    function delete()
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
    	
    	        $this->constructFromId();
    
		// setup sql
		$sql = "delete from training_file where training_file_id = ".$this->id;

		echo("delete sql:". $sql. "<br>");
		
				//create utility
		$theUtility = new SolarUtility;

		//break
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("training file delete sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
		 


		try {
			echo("inside transaction before unlink<br>");
			//remove from file system
			//TODO use global path
			unlink("../emergent/inputFiles/".$this->filename);
			
			echo("after unlink<br>");
			
		} catch (Exception $e) {
			echo("exception at unlink:".  $e->getMessage(). "\n");
		}
	 /*	
*/

    
    }
    function constructFromId()
    {
    	
    	echo("in TrainingFile constructFromId <br>");
    	
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
    	    $sql = "select training_file_id, filename, created_on, title, notes, status_id, pattern_set_id, input_weights_file_name, output_weights_file_name, input_file_name, output_file_name, emergent_log_file_name, start_training, stop_training from training_file where training_file_id = ".$this->id;

    	    echo("construct sql:". $sql. "<br>");
    	    
    	    					//log an logentry
		$theError = new SolarError;
		
		 echo("after new solarError<br>");
		 
		$theError->module = "TrainingFile::constructFromId";
		$theError->details = "construct sql:".$sql;
		
		echo("after set solarError vars<br>");
		
		//$theError->add();
		
		echo("before new utility<br>");
    	    
    	    		//create utility
		$theUtility = new SolarUtility;
		
		 echo("TrainingFile constructFromId before query exec<br>");
    	    
		/* execute sql*/
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("training file construct sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		 echo("TrainingFile constructFromId after query exec<br>");
		
		/* loop through results*/
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
			$this->constructFromRow($row);
		}
		
		
    	    
    
    }
        function constructFromRow($row)
    {
    	$this->id = $row["training_file_id"];
    	$this->createdOn = $row["created_on"];
    	$this->filename = $row["filename"];
    	$this->title = $row["title"];
	$this->notes = $row["notes"];
	$this->statusId = $row["status_id"];
	$this->patternSetId = $row["pattern_set_id"]; 
	$this->inputWeightsFileName = $row["input_weights_file_name"]; 
	$this->outputWeightsFileName = $row["output_weights_file_name"];
	$this->inputFileName = $row["input_file_name"]; 
	$this->outputFileName= $row["output_file_name"]; 
	$this->emergentLogFileName = $row["emergent_log_file_name"];
	$this->startTraining = $row["start_training"];
	$this->stopTraining = $row["stop_training"];


    }

    function listAll($displayMode, $defaultTrainingFileId)
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
    	
    if ($displayMode == "fullPage"){

			/* table of entities*/
			echo("<table cellpadding='15' cellspacing='15' class='table table-striped' border='0'>\n");

			echo("<tr class='solar4' bgcolor='#ffffff'>");
		
			echo("<td align='center'>\n");
				echo ("ID");
			echo("</td>\n");
			
			echo("<td align='center'>\n");
				echo ("PATTERN SET ID");
			echo("</td>\n");
			
			echo("<td align='center'>\n");
				echo ("FILENAME");
			echo("</td>\n");

			echo("<td align='center'>\n");
				echo ("Start/Stop");
			echo("</td>\n");
			
			echo("<td align='center'>\n");
				echo ("CREATED");
			echo("</td>\n");


			echo("<td align='center'>\n");
				echo ("STATUS ID");
			echo("</td>\n");


			
			echo("<td align='center'>\n");
				echo ("ACTION");
			echo("</td>\n");

			echo("</tr>\n");

		}
		elseif ($displayMode == "selectBox")
		{
			echo("<select name='trainingFileId' size='1'>\n");
			
			//if -1
			if ($defaultTrainingFileId <= 0)
			{
				echo("<option value='0'>Other\n");
			}
			
		}
		

		/* setup the sql*/
		$sql = "select training_file_id, filename, start_training, stop_training, created_on, notes, status_id, pattern_set_id from training_file order by created_on desc, training_file_id desc";

				//create utility
		$theUtility = new SolarUtility;

		/* execute the sql*/
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("listAll trainingFile select sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		//setup vars
		$toggle = 0;
		$theColor = "#BFBFBF";

		/* loop through results*/
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
		

						
			//instantiate object
			$theTrainingFile = new TrainingFile();
			$theTrainingFile->constructFromRow($row);

			if ($displayMode == "fullPage"){

			echo("<tr class='solar5'>");

				echo("<td align='center'>\n");
					echo ($theTrainingFile->id);
				echo("</td>\n");
				
				echo("<td align='center'>\n");
					echo ($theTrainingFile->patternSetId);
				echo("</td>\n");
			
				echo("<td align='center'>\n");
					echo ($theTrainingFile->filename);
				echo("</td>\n");

				echo("<td align='center'>\n");
					echo ($theTrainingFile->startTraining.":".$theTrainingFile->stopTraining."<br>");
					
					// Create two new DateTime-objects...
					$date1 = new DateTime($theTrainingFile->startTraining);
					$date2 = new DateTime($theTrainingFile->stopTraining);
					
					// The diff-methods returns a new DateInterval-object...
					$diff = $date2->diff($date1);
					
					// Call the format method on the DateInterval-object
					echo $diff->format('%a Day and %h hours %i minutes');

				echo("</td>\n");
				
				echo("<td align='center'>\n");
					echo ($theTrainingFile->createdOn);
				echo("</td>\n");
				

				echo("<td align='center'>\n");
					echo ($theTrainingFile->statusId);
				echo("</td>\n");


				
				echo("<td align='center'>\n");
					
					echo(" <a href='trainingFileAction.php?function=delete&trainingFileId=");
					echo($theTrainingFile->id);
					echo("'><button type='button' class='btn btn-danger btn-xs'>Delete</button></a>\n");
				

					//echo(" <a href='trainingFileAction.php?function=edit&trainingFileId=");
					//echo($theTrainingFile->id);
					//echo("'><img src='../admin/images/edit_small.gif' border='0' alt=''></a>\n");
					
		
					
				
				echo("</td>\n");	
			
				echo("</tr>\n");
			}
			elseif ($displayMode == "selectBox"){


				if ($theTrainingFile->id == $defaultTrainingFileId)
				{
					echo("<option value='".$theTrainingFile->id."' selected>".$theTrainingFile->filename.":".$theTrainingFile->id."\n");
				}
				elseif ($theTrainingFile->id != $defaultTrainingFileId) {
					echo("<option value='".$theTrainingFile->id."'>".$theTrainingFile->filename.":".$theTrainingFile->id."\n");
				}
			}
		
		} /*end while*/

		if ($displayMode == "fullPage"){
				echo("</table>\n");
		}
		elseif ($displayMode == "selectBox"){
			echo("</select>\n");
		}

    
    }
    
    
}

?>
