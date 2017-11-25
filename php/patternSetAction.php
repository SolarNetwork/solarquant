<?php

		//echo("in patternSetAction<br>");
		
		
		
//imports
require_once "../classes/PatternSet.php";

//echo("patternSetAction after PatternSet import<br>");

require_once "../classes/ConsumptionPatternSet.php";

//echo("patternSetAction after ConsumptionPatternSet import<br>");
		//imports
//require "../classes/node.php";





//exit;

require_once "/var/www/html/solarquant/classes/SolarUtility.php";
require_once "/var/www/html/solarquant/classes/TrainingFile.php";


		//echo("nodeAction after all imports 2");
		
//break;


/* if there isn't an existing link */
if ($link = " "){
	/* create a link to the database*/
	//$link = mysql_connect ("mysql.fatcow.com","solar","solar") or die ("Could not connect1");
	
	//centralize authentication
	$theUtility = new SolarUtility;
	//$link = mysql_connect ($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword) or die ("Could not connect1");
	$link = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
}

//catch POSTed vars
if ($function == "")
{
	$function = trim($_REQUEST['function']);
}
if ($displayMode == "")
{
	$displayMode = trim($_REQUEST['displayMode']);
}
if ($theButton == "")
{
	$theButton = trim($_REQUEST['theButton']);
}

//echo("before function<br>");

//exit;

	/* function = list*/
	if ($function == "list")
	{
		//echo("in list<br>");
		
		
		
		

		//instantiate object
		$thePatternSet = new PatternSet;

			//echo("in list after instan");
			
		echo("<html>");
		
		echo("<head>\n");
		echo("<title>solarquant.Admin</title>\n"); 
		echo("<link href='../css/solarStyle.css' type='text/css' rel='stylesheet'>");
		echo("<link href='../css/bootstrap.min.css' type='text/css' rel='stylesheet'>");
		echo("<link href='../css/bootstrap-theme.min.css' type='text/css' rel='stylesheet'>");
		echo("<script src='../js/bootstrap.min.js'></script>");
		
		echo("<link href='../includes/calendar.css' rel='stylesheet' type='text/css' />");
		echo("<script language='javascript' src='../includes/calendar.js'></script>");
		
		echo("</head>\n");
		
		echo("<body bgcolor='#ffffff'>");
		

		
		//call list function
		$thePatternSet->listAll("fullPage",-1);
		
		
	//show files in the output director
	/*
	echo ("<br><br><span class='solar3'>Files in /emergent/output/</span><br><br>");
		
      $dir = new DirectoryIterator( '../emergent/output/' );
     foreach($dir as $file )
  
      {
  
      	        if ( ($file->getFilename() != ".") and ($file->getFilename() != "..") )
        {
        	
        echo ("<a href='http://www.solarnetwork.net/emergent/output/".$file->getFilename() ."'><span class='solar5'>".$file->getFilename());
        

       		echo ("</span> <a href='patternSetAction.php?function=deleteFile&file=".$file->getFilename()."' class='solar5'><button type='button' class='btn btn-danger btn-xs'>Delete</button></a><br>");
        }
      }
*/

		
		echo("</body");
		echo("</html>");


	}
	elseif ($function == "deleteFile")
	{
		
		$theFile = "../emergent/output/".trim($_REQUEST['file']);
		
		if (is_file($theFile))
		{
			unlink($theFile);
			
								//return to list
			header ("Location: patternSetAction.php?function=list&displayMode=fullPage");
		}
		else 
		{
			echo("error deleting file");
		}


		
	}
	elseif ($function == "createConsumptionDataTableFile")
	{
		
		echo("in createConsumptionDataTableFile<br>");
		
		//instantiate object
		$thePatternSet = new PatternSet;
		$thePatternSet->id = $_REQUEST['patternSetId'];
		$thePatternSet->constructFromId();
		
		$saveTime = strtotime("now");
		$showSaveTime = date("Y-m-d H:i:S",$saveTime);
		
		$fileContents = $thePatternSet->generateConsumptionDataTableContents($showSaveTime);
		
		echo("before writeTrainingFile <br>");
		
		//write the trainingFile
		$thePatternSet->writeTrainingFile($fileContents);
		
		echo("after writeTrainingFile <br>");
		
		/*  refactored 20140907
		
		echo(" fileContents:" . $fileContents."<br><br>");	
		
		//generate unique name based on datetime
		$startTime = strtotime($thePatternSet->startDate);
		$showStartDate = date("Ymd",$startTime);
		$endTime = strtotime($thePatternSet->endDate);
		$showEndDate = date("Ymd",$endTime);
				
		//name the file
		$fileName = "ConsumptionPattern_".$thePatternSet->id."_".$showStartDate."_".$showEndDate."_".$saveTime.".dat";
		//$fileName = "S".$thePatternSet->id."_".$saveTime.".dtbl";
		
		echo(" fileName:" . $fileName."<br><br>");	
		
		$fp = fopen("../emergent/output/".$fileName, 'w');
		//$fp = fopen($fileName, 'w');
		
		//write the file
		fwrite($fp, $fileContents);

		//close the file
		fclose($fp);
		
		*/
		
		echo("out createConsumptionDataTableFile<br>");
		
		//return to list
		
	}
	elseif ($function == "createDataTableFile")
	{
		
		echo("in createDataTableFile<br>");
		
		//instantiate object
		$thePatternSet = new PatternSet;
		$thePatternSet->id = $_REQUEST['patternSetId'];
		$thePatternSet->constructFromId();
		
		$saveTime = strtotime("now");
		$showSaveTime = date("Y-m-d H:i:S",$saveTime);
		
		$fileContents = $thePatternSet->generateDataTableContents($showSaveTime);
		
		
		
		echo(" fileContents:" . $fileContents."<br><br>");	
		
		//generate unique name based on datetime
		$startTime = strtotime($thePatternSet->startDate);
		$showStartDate = date("Ymd",$startTime);
		$endTime = strtotime($thePatternSet->endDate);
		$showEndDate = date("Ymd",$endTime);
		
		
		//name the file
		$fileName = "S".$thePatternSet->id."_".$showStartDate."_".$showEndDate."_".$saveTime.".dat";
		//$fileName = "S".$thePatternSet->id."_".$saveTime.".dtbl";
		
		echo(" fileName:" . $fileName."<br><br>");	
		
		$fp = fopen("../emergent/output/".$fileName, 'w');
		//$fp = fopen($fileName, 'w');
		
		//write the file
		fwrite($fp, $fileContents);

		//close the file
		fclose($fp);
		
		echo("out createDataTableFile<br>");
		
		//return to list
		
	}
	elseif ($function == "generateNNWeights")
	{
	
		//echo("in generateNNWeights<br>");
		
		
//	echo(" REQUEST['patternSetId']:" . $_REQUEST['patternSetId']."<br><br>");	
		
		//instantiate object
		$thePatternSet = new PatternSet;
		
		$thePatternSet->id = $_REQUEST['patternSetId'];
		//echo("thePatternSet->id:" . $thePatternSet->id."<br><br>");
		
		$thePatternSet->constructFromId();
		
//	echo("thePatternSet->patternSetId:" . $thePatternSet->id."<br><br>");	
		
	//	echo("before generateNNWeights<br>");
		
		$thePatternSet->generateNNInputWeights();
		
	//	echo("out generateNNWeights<br>");
	}
	elseif ($function == "generateConsumptionNNWeights")
	{
	
		//echo("in generateNNWeights<br>");
		
		
//	echo(" REQUEST['patternSetId']:" . $_REQUEST['patternSetId']."<br><br>");	
		
		//instantiate object
		$theConsumptionPatternSet = new ConsumptionPatternSet;
		
		$theConsumptionPatternSet->patternSetId = $_REQUEST['patternSetId'];
		//echo("thePatternSet->id:" . $thePatternSet->id."<br><br>");
		
		$theConsumptionPatternSet->constructFromPatternSetId();
		
//	echo("thePatternSet->patternSetId:" . $thePatternSet->id."<br><br>");	
		
	//	echo("before generateNNWeights<br>");
		
		$theConsumptionPatternSet->generateNNInputWeights();
		
	//	echo("out generateNNWeights<br>");
	}
	elseif ($function == "pullFromSolarNet")
	{
		echo("in pullFromSolarNet<br>");
		
		//instantiate object
		$thePatternSet = new PatternSet;
		
		//TODO do we want to listen for flags of what data to pull down
		//right now it's consumption and weather but...
		
		$thePatternSet->id = $_REQUEST['patternSetId'];
		echo("thePatternSet->id:" . $thePatternSet->id."<br><br>");
		

		
		$thePatternSet->constructFromId();
		
		echo("before getMyDatum<br>");
		
		$thePatternSet->statusId = 1;
		$thePatternSet->update();
		
		//get appropriate datum based on type
		$thePatternSet->getMyDatum();
		
		/*
		//TODO refactor this section as a method on PatternSet
		//always pull weather but use the weatherNodeId for this node
		$thePatternSet->getDatumFromSolarNet("Weather");
		
		
		
		echo("thePatternSet->patternSetTypeId:". $thePatternSet->patternSetTypeId. " <br>");
		
		
		//just consumption
		if ($thePatternSet->patternSetTypeId == 1) 
		{
			$thePatternSet->getDatumFromSolarNet("Consumption");
		}
		//just power
		elseif ($thePatternSet->patternSetTypeId == 2) 
		{
			$thePatternSet->getDatumFromSolarNet("Power");
		}
		//both consumption and power
		elseif ($thePatternSet->patternSetTypeId == 3) 
		{
			$thePatternSet->getDatumFromSolarNet("Power");
			$thePatternSet->getDatumFromSolarNet("Consumption");
		}		
		
		*/
		
		echo("out pullFromSolarNet<br>");
		
	}
	elseif ($function == "add")
	{
				
		//echo("in the add function<br>");
		
		//instantiate object
		$thePatternSet = new PatternSet;
		
		$thePatternSet->name = $_REQUEST['patternSetName'];
		$thePatternSet->startDate = $_REQUEST['startDate'];
		$thePatternSet->endDate = $_REQUEST['endDate'];
		$thePatternSet->statusId = $_REQUEST['statusId'];
		$thePatternSet->analysisEngineId = $_REQUEST['analysisEngineId'];
		$thePatternSet->patternSetTypeId = $_REQUEST['patternSetTypeId'];
		$thePatternSet->notes = $_REQUEST['notes'];
		
		//echo("after notes<br>");
		
		$thePatternSet->nodes = $_REQUEST['nodes'];
		
		echo("after nodes sizeof:".sizeof($thePatternSet->nodes)."<br>");
		echo("thePatternSet->analysisEngineId:".$thePatternSet->analysisEngineId."<br>");
	
		
		//break;
		
		//as long it's a valid pattern set
		if (sizeof($thePatternSet->nodes) > 0)
		{
		
			//run update
			$thePatternSet->add();

			//return to list
			header ("Location: patternSetAction.php?function=list&displayMode=fullPage");
		
		}
		else
		{
			echo("error: invalid pattern set ");
		}
		
	}
	elseif ($function == "edit")
	{
	
		//instantiate object
		$thePatternSet = new PatternSet;
				
		//catch POSTed vars
		$thePatternSet->id = $_REQUEST['patternSetId'];
		
		//echo "theContact.id:".$theContact->id;
		
		//run the edit method
		$thePatternSet->edit();
		

		

	}
	elseif ($function == "update")
	{

		//instantiate object
		$thePatternSet = new PatternSet;
		
		//catch POSTed vars
		$thePatternSet->id = trim($_REQUEST['patternSetId']);
		$thePatternSet->startDate = $_REQUEST['startDate'];
		$thePatternSet->endDate = $_REQUEST['endDate'];
		$thePatternSet->name = trim($_REQUEST['patternSetName']);
		$thePatternSet->notes = trim($_REQUEST['notes']);
		
		//explode into an array
		//TODO be able to catch multiple named checkbox sets?
		$thePatternSet->nodes = array();
		//$thePatternSet->nodes = explode(",", trim($_REQUEST['nodeList']));
		$thePatternSet->nodes = explode(",", trim($_REQUEST['nodeId']));
		
		//$thePatternSet->sourceIds = trim($_REQUEST['sourceIds']);
		
		//explode into an array
		$theSourceIds = array();
		//$theSourceIds = explode(",", trim($_REQUEST['sourceIds']));
		$theSourceIds = $_REQUEST['sourceIds'];
    	
		$thePatternSet->statusId = trim($_REQUEST['statusId']);
		$thePatternSet->patternSetTypeId = trim($_REQUEST['patternSetTypeId']);
		
		/*
		echo("before update thePatternSet sizeof:".sizeof($thePatternSet->nodes)."<br>");
		echo("nodes[0]:".$thePatternSet->nodes[0]."<br>");

		echo("before update sourceIds:".$_REQUEST['sourceIds']."<br>");
		echo("before update thePatternSet sizeof theSourceIds:".sizeof($theSourceIds)."<br>");
		echo("theSourceIds[0]:".$theSourceIds[0]."<br>");
		echo("theSourceIds[1]:".$theSourceIds[1]."<br>");
		echo("theSourceIds[2]:".$theSourceIds[2]."<br>");
		*/
		
		//run update
		$thePatternSet->update($theSourceIds);

		//return to list
		header ("Location: patternSetAction.php?function=list&displayMode=fullPage");
	


	}
	// function = clear
	elseif ($function == "clear")
	{

		$thePatternSet = new PatternSet;

		$thePatternSet->id = $_REQUEST['patternSetId'];
		
		$thePatternSet->constructFromId();
		
		$thePatternSet->clear();

 		//return to list
		header ("Location: patternSetAction.php?function=list&displayMode=fullPage");               

	}
	/* function = delete*/
	elseif ($function == "delete")
	{

		echo("in function delete<br>");
		
		$thePatternSet = new PatternSet;
		
		echo("after new thePatternSet<br>");

		$thePatternSet->id = $_REQUEST['patternSetId'];
		
		echo("before delete id:".$thePatternSet->id."<br>");
		
		$thePatternSet->delete();
		
			echo("after delete<br>");

 		//return to list
		header ("Location: patternSetAction.php?function=list&displayMode=fullPage");               

	}
		
	//function = viewCorrelation 
	elseif ($function == "viewCorrelation")
	{
		// Start the session
		session_start();
	
		$_SESSION["patternSetId"] = $_REQUEST['patternSetId'];
		
				//construct from id
		$thePatternSet = new PatternSet();
		$thePatternSet->id = $_SESSION["patternSetId"];
		$thePatternSet->constructFromId();
		
		$_SESSION["startDate"] = $thePatternSet->startDate;
		$_SESSION["endDate"] = $thePatternSet->endDate;
		
		//unset($_SESSION["startDate"]);
		//unset($_SESSION["endDate"]);
		
		//echo ("session patternSetId:".$_SESSION["patternSetId"]);
		
		//return to list
		header ("Location: predictedVsActual5.html");     
		
	}
	//function = viewTraining 
	elseif ($function == "viewTraining")
	{

		$thePatternSet = new PatternSet;

		$thePatternSet->id = $_REQUEST['patternSetId'];
		
		//echo "thePatternSet->id :".$thePatternSet->id ."<br />\n";
		
		//echo "before getMostRecentTrainingFile <br />";
		
		$theTrainingFile = $thePatternSet->getMostRecentTrainingFile();
		
		//echo "after getMostRecentTrainingFile <br />";
		
		//echo "theTrainingFile->id :".$theTrainingFile->id ."<br />\n";
		
		// Start the session
		session_start();
		
		//echo "after session start <br />";
		
		$_SESSION["theTrainingFileId"] = $theTrainingFile->id;
		
		//echo "session theTrainingFileId :".$_SESSION["theTrainingFileId"]."<br />\n";

 		//return to list
		header ("Location: simple-graph6.html");               

	}
	elseif ($function == "nnOutputUpload")
	{
		
		$theFileName = basename($_FILES['userfile']['name']);
		$uploaddir = '/var/www/solarnetwork/emergent/input/';
		$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
		
		echo "uploadfile:".$uploadfile."<br />\n";

		echo '<pre>';
		if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
			echo "File is valid, and was successfully uploaded.\n";

			//grab patternId from name
			$firstUnderscorePosition = strpos($theFileName, '_');
			
			echo "firstUnderscorePosition:".$firstUnderscorePosition."<br />\n";
			
			$patternDigits = $firstUnderscorePosition - 1;
        	$patternSetId = substr($theFileName,1,$patternDigits);
        	
        	echo "patternId:".$patternId."<br />\n";
        	
        	//clear out predictive data for this patternSet only!
       		clearPredictiveData($patternSetId);
        	
        	$processedRecords = processNNOutputFile($patternSetId, $theFileName);
        	
        	echo "processedRecords:".$processedRecords."<br />\n";


		} else {
			echo "Possible file upload attack!\n";
		}

		echo 'Here is some more debugging info:';
		print_r($_FILES);

		print "</pre>";

	} //end nnOutputUpload
	
	function processNNOutputFile($patternSetId, $theFile)
	{


if ($link = " "){
	/* create a link to the database*/
	//$link = mysql_connect ("mysql.fatcow.com","solar","solar") or die ("Could not connect1");
	
	//centralize authentication
	$theUtility = new SolarUtility;
	//$link = mysql_connect ($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword) or die ("Could not connect1");
	$link = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
}


$row = 1;
$tab = (chr(9)); 
$handle = fopen("../emergent/input/".$theFile, "r");
while (($data = fgetcsv($handle, 1000, $tab)) !== FALSE) {
    $fieldCount = count($data);
    echo "<p> $fieldCount fields in line $row: <br /></p>\n";

    
    //skip first row
    if ($row > 1)
    {

    //loop through  fields in each row
    for ($i=0; $i < $fieldCount; $i++) {
    	
        echo "item ".$i.":".$data[$i] . "<br />\n";
        
        //grab halfHour
        if ($i == 4)
        {
        	$halfHourIdentifier = $data[$i];
        	$firstUnderscorePosition = strpos($halfHourIdentifier, '_');
        	echo "firstUnderscorePosition:".$firstUnderscorePosition . "<br />\n";
        	
        	$secondUnderscorePosition = strpos($halfHourIdentifier, '_', $firstUnderscorePosition+1);
        	$thirdUnderscorePosition = strpos($halfHourIdentifier, '_', $secondUnderscorePosition+1);
        	$fourthUnderscorePosition = strpos($halfHourIdentifier, '_', $thirdUnderscorePosition+1);
      	
        	
        	echo "secondUnderscorePosition:".$secondUnderscorePosition . "<br />\n";
        	echo "thirdUnderscorePosition:".$thirdUnderscorePosition . "<br />\n";
        	echo "fourthUnderscorePosition:".$fourthUnderscorePosition . "<br />\n";
        	
       	
        	//grab nodeid
        	$nodeDigits = $firstUnderscorePosition - 1;
        	$nodeId = substr($halfHourIdentifier,1,$nodeDigits);
        	
        	echo "nodeId:".$nodeId . "<br />\n";
        	
        	        //instantiate the node
       $theNode = new Node;
        $theNode->id = $nodeId;
        $theNode->constructFromId();
        
        echo "theNode->id:".$theNode->id."<br />\n";

        	
        	//create startDate
        	$theStartYear = substr($halfHourIdentifier,$firstUnderscorePosition+1,4);
        	$theStartMonth = substr($halfHourIdentifier,$firstUnderscorePosition+5,2);
        	$theStartDay = substr($halfHourIdentifier,$firstUnderscorePosition+7,2);
        	
        	$theStartHour = substr($halfHourIdentifier,$secondUnderscorePosition+1,2);
        	$theStartMinute = substr($halfHourIdentifier,$secondUnderscorePosition+3,2);
        	$theStartSecond = substr($halfHourIdentifier,$secondUnderscorePosition+5,2);
        	
        	$theStartDate = $theStartYear."-".$theStartMonth."-".$theStartDay." ".$theStartHour.":".$theStartMinute.":".$theStartSecond;
        	
        	//create endDate
        	$theEndYear = substr($halfHourIdentifier,$thirdUnderscorePosition+1,4);
        	$theEndMonth = substr($halfHourIdentifier,$thirdUnderscorePosition+5,2);
        	$theEndDay = substr($halfHourIdentifier,$thirdUnderscorePosition+7,2);
        	
        	$theEndHour = substr($halfHourIdentifier,$fourthUnderscorePosition+1,2);
        	$theEndMinute = substr($halfHourIdentifier,$fourthUnderscorePosition+3,2);
        	$theEndSecond = substr($halfHourIdentifier,$fourthUnderscorePosition+5,2);
        	
        	$theEndDate = $theEndYear."-".$theEndMonth."-".$theEndDay." ".$theEndHour.":".$theEndMinute.":".$theEndSecond;

        	        //calculate predictedWattHours for this node for this halfhour
        $predictedWattHours = $predictedWattHourCoef * $theNode->totalWatts * 0.5;
        
        //divide by 30 minutes to get predictedWattMinutes
        if ($predictedWattHours > 0)
        {
        	$predictedWattMinutes = $predictedWattHours / 30;
        }
        else 
        {
        	$predictedWattMinutes = 0;
        }
        	
        	echo "theStartYear:".$theStartYear . "<br />\n";
        	echo "theStartMonth:".$theStartMonth . "<br />\n";
        	echo "theStartDay:".$theStartDay . "<br />\n";
        	echo "theStartHour:".$theStartHour . "<br />\n";
        	echo "theStartMinute:".$theStartMinute . "<br />\n";
        	echo "theStartSecond:".$theStartSecond . "<br />\n";
        	
        	echo "theEndYear:".$theEndYear . "<br />\n";
        	echo "theEndMonth:".$theEndMonth . "<br />\n";
        	echo "theEndDay:".$theEndDay . "<br />\n";
        	echo "theEndHour:".$theEndHour . "<br />\n";
        	echo "theEndMinute:".$theEndMinute . "<br />\n";
        	echo "theEndSecond:".$theEndSecond . "<br />\n";
        	
        	echo "theStartDate:".$theStartDate . "<br />\n";
        	echo "theEndDate:".$theEndDate . "<br />\n";
        	
        	
        	
        	//create endDate
        	
        	//echo "nodeId:".$nodeId."<br />\n";
        	
        }
        elseif ($i == 6)  //grab sse
        {
        	$sse = $data[$i];
        }
        elseif ($i == 7)  //grab predicted
        {
        	$predictedWattHourCoef = $data[$i];
        }        
        elseif ($i == 8)  //grab actual
        {
        	$actualWattHourCoef = $data[$i];
        }
        
         
        
    }  //end for loop through fields
    
            	echo "after loop theStartDate:".$theStartDate . "<br />\n";
        	echo "after loop theEndDate:".$theEndDate . "<br />\n";
        	
        	//update weather_input_pattern with the sse
        	updateWeatherPatternWithErrorLevel($patternSetId,$theNode->id,$theStartDate,$theEndDate,$sse);
        	
        	
        	
            //get list of valid powerDatumIds for this half hour
       $powerDatumIds = getValidPowerDatumIds($theNode->id,$theStartDate,$theEndDate);
        
       echo "sizeof(powerDatumIds):".sizeof($powerDatumIds)."<br />\n";
       
       echo "powerDatumIds[ 0 ]:".$powerDatumIds[ 0 ]."<br />\n";
       echo "powerDatumIds[ 1 ]:".$powerDatumIds[ 1 ]."<br />\n";
       

        
        //loop though powerDatumIds
        for ($j=0; $j < sizeof($powerDatumIds); $j++)
        {
        	
        	$powerDatumId = $powerDatumIds[ $j ];
        	
        	echo "powerDatumId:".$j.":".$powerDatumId."<br />\n";
        	
        	//insert row with sse and wattMinutes
        	$matchId = addPredictiveData($patternSetId,$powerDatumId,$sse,$predictedWattMinutes);
        	
        	echo "matchId inserted:".$matchId."<br />\n";
        	
        }
        
        
        	
    
     } //if row is data
     
     //increment row
     $row++;
         
}  //end while
fclose($handle);

return sizeof($powerDatumIds);

	}
	
	
	
function updateWeatherPatternWithErrorLevel($patternSetId, $nodeId,$startDate,$endDate, $sse)
{
	
		
//open db
if ($link = " "){
	/* create a link to the database*/
	//$link = mysql_connect ("mysql.fatcow.com","solar","solar") or die ("Could not connect1");
	
	//centralize authentication
	$theUtility = new SolarUtility;
	//$link = mysql_connect ($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword) or die ("Could not connect1");
	$link = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
}

//locate the weather_input_pattern_id that matches this 
$sql = "SELECT weather_input_pattern_id from weather_input_pattern where ".
		"pattern_set_id = ".$patternSetId." AND ".
		"node_id = ".$nodeId." AND ".
		"start_sample = '".$startDate."' AND ".
		"end_sample = '".$endDate."'";
		
		echo "sql:".$sql."<br />\n";

		//$result = mysql_db_query("solarnetwork","$sql") or die ("get weather pattern id failed:" . $sql);
		$result = $link->query($sql);
		$result->data_seek(0);

		/* loop through results*/
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
			$weatherInputPatternSetId = $row["weather_input_pattern_id"];
						
			//if we found it
			echo "found weatherInputPatternSetId:".$weatherInputPatternSetId."<br />\n";

				//update it with SSE
				$updateSQL = "UPDATE weather_input_pattern set sse = ".$sse." where weather_input_pattern_id = ".$weatherInputPatternSetId;
				
				echo "updateSQL:".$updateSQL."<br />\n";
				
				//$updateResult = mysql_db_query("solarnetwork","$updateSQL") or die ("update weather pattern id failed:" . $updateSQL);
				$updateResult = $link->query($updateSQL);
				//$updateResult->data_seek(0);
		
		}




} //end updateWeatherPatternWithErrorLevel

function getValidPowerDatumIds($nodeId,$startDate,$endDate)
{
	
		
if ($link = " "){
	/* create a link to the database*/
	//$link = mysql_connect ("mysql.fatcow.com","solar","solar") or die ("Could not connect1");
	
	//centralize authentication
	$theUtility = new SolarUtility;
	//$link = mysql_connect ($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword) or die ("Could not connect1");
	$link = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
}


$getNodesSql = "select power_datum_id from power_datum where node_id = ".$nodeId." and when_logged > '".$startDate."' and when_logged < '".$endDate."'";
	
echo "getNodesSql:".$getNodesSql."<br />\n";

//$result = mysql_db_query("solarnetwork","$getNodesSql") or die ("getPowerDatumIdsSql failed:" . $getNodesSql);
$result = $link->query($getNodesSql);
$result->data_seek(0);

//loop through nodes
//for ( $i=0; $i < mysql_num_rows($result); $i++ )
for ( $i=0; $i < mysqli_num_rows($result); $i++ )
{
	  // Seek to row number 15
  mysqli_data_seek($result,$i);

  // Fetch row
  $row=mysqli_fetch_row($result);
	
	
	//$powerDatumIds[ $i ] = mysql_result ( $result, $i, "power_datum_id");
	$powerDatumIds[ $i ] = $row["power_datum_id"];
	
	
	
}
	
	return $powerDatumIds;
}

function clearPredictiveData($patternSetId)
{
	
	$deleteSql = "DELETE from power_prediction where pattern_set_id = ". $patternSetId;
	
		
//run insert
//$result = mysql_db_query("solarnetwork","$deleteSql") or die ("deleteSql failed:" . $deleteSql);
$result = $link->query($deleteSql);
//$result->data_seek(0);

}

function addPredictiveData($patternSetId,$powerDatumId,$sse,$predictedWattMinutes)
{
	//set vars
global $startDay;
global $startMonth;
global $startYear;

	//open db
if ($link = " "){
	/* create a link to the database*/
	//$link = mysql_connect ("mysql.fatcow.com","solar","solar") or die ("Could not connect1");
	
	//centralize authentication
	$theUtility = new SolarUtility;
	//$link = mysql_connect ($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword) or die ("Could not connect1");
	$link = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
}

$theDay = mktime(0,0,0,$startMonth,$startDay+$dayId,$startYear);

$insertSql = "INSERT into power_prediction (pattern_set_id, power_datum_id, sse, watt_minutes) values (".
		$patternSetId.",".
		$powerDatumId.",".
		$sse.",".
		$predictedWattMinutes.")";
		
//run insert
//$result = mysql_db_query("solarnetwork","$insertSql") or die ("insertPredictionSql failed:" . $insertSql);
$result = $link->query($insertSql);
//$result->data_seek(0);
	
//return mysql_insert_id();
return $link->insert_id;

}


?>
