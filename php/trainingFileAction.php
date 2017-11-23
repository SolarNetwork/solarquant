<?php

		//echo("in trainingFileAction");
		
		//imports
require_once "/var/www/html/solarquant/classes/TrainingFile.php";

		//imports
//require "../classes/node.php";

//echo("nodeAction after node import");

require_once "/var/www/html/solarquant/classes/SolarUtility.php";
		
		/* if there isn't an existing link */
if ($link = " "){
	/* create a link to the database*/
	
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
		
		echo("<html>");
		echo("<head>\n");
		
		echo("<title>solarquant.Admin</title>\n"); 
		echo("<link href='../css/solarStyle.css' type='text/css' rel='stylesheet'>");
		echo("<link href='../css/bootstrap.min.css' type='text/css' rel='stylesheet'>");
		echo("<link href='../css/bootstrap-theme.min.css' type='text/css' rel='stylesheet'>");
		echo("<script src='../js/bootstrap.min.js'></script>");
		
				//instantiate object
		$theTrainingFile = new TrainingFile;
		
				//call list function
		$theTrainingFile->listAll("fullPage",-1);
		
		echo("</head>\n");
		echo("</html>\n");
		
	}
	/* function = add*/
	elseif ($function == "add")
	{
				//echo("in the add function<br>");
		
		//instantiate object
		$theTrainingFile = new TrainingFile;
		
		$theTrainingFile->filename = $_REQUEST['filename'];
		$theTrainingFile->createdOn = $_REQUEST['createdOn'];
		$theTrainingFile->title = $_REQUEST['title'];
		$theTrainingFile->patternSetId = $_REQUEST['patternSetId'];
		$theTrainingFile->statusId = $_REQUEST['statusId'];
		$theTrainingFile->notes = $_REQUEST['notes'];
		
		//echo("after notes<br>");
		

		echo("theTrainingFile->filename:".$theTrainingFile->filename."<br>");
		
		//break;
		
		//as long it's a valid pattern set
		//if (sizeof($thePatternSet->nodes) > 0)
		//{
		
			//run update
			$theTrainingFile->add();
			
			echo("after add<br>");

			//return to list
			//header ("Location: trainingFileAction.php?function=list&displayMode=fullPage");
		
		//}
		//else
		//{
		//	echo("error: invalid pattern set ");
		//}
		
	}
	//delete
	elseif ($function == "delete")
	{
		
		//echo("in the delete function<br>");
		
		//instantiate object
		$theTrainingFile = new TrainingFile;
		
		//echo("theTrainingFile->id:".$_REQUEST['trainingFileId']."<br>");
		
			$theTrainingFile->id = $_REQUEST['trainingFileId'];
			
			//echo("theTrainingFile->id:".$_REQUEST['trainingFileId']."<br>");
		
			//run delete
			$theTrainingFile->delete();
			
			//return to list
			header ("Location: trainingFileAction.php?function=list&displayMode=fullPage");
	}
	
?>
