<?php

		echo("in patternSetAction2<br>");
		
		
		//imports
require_once "../classes/PatternSet.php";
require_once "../classes/ConsumptionPatternSet.php";
require_once "/var/www/html/solarquant/classes/SolarUtility.php";
require_once "/var/www/html/solarquant/classes/TrainingFile.php";

echo("after imports<br>");

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

	/* function = list*/
	if ($function == "list")
	{
		echo("in list<br>");
		
		
		
		

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
		$thePatternSet
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

		echo("out patternSetAction2.php<br>");
		
?>

