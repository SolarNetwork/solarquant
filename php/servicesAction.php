<?php

		//echo("in servicesAction2<br>");
		
				//imports
require_once "/var/www/html/solarquant/classes/SolarError.php";

		//imports
//require "../classes/node.php";

//echo("nodeAction after node import");

require_once "/var/www/html/solarquant/classes/SolarUtility.php";

//echo("after imports<br>");

/* if there isn't an existing link */
if ($link = " "){
	/* create a link to the database*/
	//$link = mysql_connect ("mysql.fatcow.com","solar","solar") or die ("Could not connect1");
	
	//centralize authentication
	$theUtility = new SolarUtility;
	//$link = mysql_connect ($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword) or die ("Could not connect1");
	$link = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
}

//echo("after db link<br>");

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
else
{
	echo("request:".$_REQUEST['theButton']);
}

//echo("after catching vars<br>");

//echo("theButton: $theButton<br>");
//echo("function: $function<br>");

	//function = list
	if ($function == "list")
	{
		//echo("in list<br>");
		
				//instantiate object
		$theError = new SolarError;

		echo("<html>\n");
		
		echo("<head>\n");
		
		echo("<title>solarquant.Admin</title>\n"); 
		echo("<link href='../css/solarStyle.css' type='text/css' rel='stylesheet'>\n");
		echo("<link href='../css/bootstrap.min.css' type='text/css' rel='stylesheet'>\n");
		echo("<link href='../css/bootstrap-theme.min.css' type='text/css' rel='stylesheet'>\n");
		echo("<script src='../js/bootstrap.min.js'></script>\n");
				
		echo("</head>\n");
		
		echo("<body>\n");
		
					
		
		echo("<table border='1' cellpadding='9' cellspacing='5'>\n");
		
		echo("<tr>\n");
		
		echo("<td>");
			echo("<div>Service</div>");
		echo("</td>");
		echo("<td>");
			echo("<div>Status</div>");
		echo("</td>");
		echo("<td>");
			echo("<div>Toggle</div>");
		echo("</td>");
		
		echo("</tr>\n");

		echo("<tr>\n");
		
		echo("<td>");
			echo("<div>Generate New Pattern Sets</div>");
		echo("</td>");
		echo("<td>");
			$theStatus = $theUtility->getGenerateNewPatternSetStatus();
			echo($theStatus);
		echo("</td>");
		echo("<td>");
		//echo("<button type='button' type='submit' class='btn btn-primary btn-block'>Pattern Sets</button>\n");
		
		echo("<form action='servicesAction.php'>\n");	
			
			if ($theStatus == "stopped")
			{
				$buttonLabel = "Start";
				$mappedFunction = "unChockGenerateNewPattern";
			}
			elseif ($theStatus == "running")
			{	
				$buttonLabel = "Stop";
				$mappedFunction = "chockGenerateNewPattern";
			}	
			
			echo("<input type='hidden' name='function' value='$mappedFunction'>\n");
			echo("<input type='submit' name='theButton' value='$buttonLabel'>\n");
			
		echo("</form>\n");
			
		echo("</td>");
		
		echo("</tr>\n");
		
		echo("<tr>\n");
		
		echo("<td>");
			echo("<div>Create Training Files</div>");
		echo("</td>");
		echo("<td>");
			$theStatus = $theUtility->getCreateTrainingFilesStatus();
			echo($theStatus);
		echo("</td>");
		echo("<td>");
		//echo("<button type='button' type='submit' class='btn btn-primary btn-block'>Pattern Sets</button>\n");
		
		echo("<form action='servicesAction.php'>\n");	
		
			if ($theStatus == "stopped")
			{
				$buttonLabel = "Start";
				$mappedFunction = "unChockCreateTrainingFiles";
			}
			elseif ($theStatus == "running")
			{	
				$buttonLabel = "Stop";
				$mappedFunction = "chockCreateTrainingFiles";
			}	
			
			echo("<input type='hidden' name='function' value='$mappedFunction'>\n");
			echo("<input type='submit' name='theButton' value='$buttonLabel'>\n");
			
		echo("</form>\n");
		
		echo("</td>");
		
		echo("</tr>\n");

		echo("<tr>\n");
		
		echo("<td>");
			echo("<div>Create Emergent Script</div>");
		echo("</td>");
		echo("<td>");
			$theStatus = $theUtility->getCreateEmergentScriptChockFileStatus();
			echo($theStatus);
		echo("</td>");
		echo("<td>");
		//echo("<button type='button' type='submit' class='btn btn-primary btn-block'>Pattern Sets</button>\n");
		
		echo("<form action='servicesAction.php'>\n");	
		
			if ($theStatus == "stopped")
			{
				$buttonLabel = "Start";
				$mappedFunction = "unChockCreateEmergentScript";
			}
			elseif ($theStatus == "running")
			{	
				$buttonLabel = "Stop";
				$mappedFunction = "chockCreateEmergentScript";
			}	
			
			echo("<input type='hidden' name='function' value='$mappedFunction'>\n");
			echo("<input type='submit' name='theButton' value='$buttonLabel'>\n");
			
		echo("</form>\n");
		
		echo("</td>");
		
		echo("</tr>\n");

		echo("<tr>\n");
		
		echo("<td>");
			echo("<div>Run Emergent Script</div>");
		echo("</td>");
		echo("<td>");
			$theStatus = $theUtility->getRunEmergentScriptChockFileStatus();
			echo($theStatus);
		echo("</td>");
		echo("<td>");
		//echo("<button type='button' type='submit' class='btn btn-primary btn-block'>Pattern Sets</button>\n");
		
		echo("<form action='servicesAction.php'>\n");	
		
			if ($theStatus == "stopped")
			{
				$buttonLabel = "Start";
				$mappedFunction = "unChockRunEmergentScript";
			}
			elseif ($theStatus == "running")
			{	
				$buttonLabel = "Stop";
				$mappedFunction = "chockRunEmergentScript";
			}	
			
			echo("<input type='hidden' name='function' value='$mappedFunction'>\n");
			echo("<input type='submit' name='theButton' value='$buttonLabel'>\n");
			
		echo("</form>\n");
		
		echo("</td>");
		
		echo("</tr>\n");

		echo("<tr>\n");
		
		echo("<td>");
			echo("<div>Check Emergent </div>");
		echo("</td>");
		echo("<td>");
			$theStatus = $theUtility->getCheckEmergentChockFileStatus();
			echo($theStatus);
		echo("</td>");
		echo("<td>");
		//echo("<button type='button' type='submit' class='btn btn-primary btn-block'>Pattern Sets</button>\n");
		
		echo("<form action='servicesAction.php'>\n");	
		
			if ($theStatus == "stopped")
			{
				$buttonLabel = "Start";
				$mappedFunction = "unChockCheckEmergent";
			}
			elseif ($theStatus == "running")
			{	
				$buttonLabel = "Stop";
				$mappedFunction = "chockCheckEmergent";
			}	
			
			echo("<input type='hidden' name='function' value='$mappedFunction'>\n");
			echo("<input type='submit' name='theButton' value='$buttonLabel'>\n");
			
		echo("</form>\n");
		
		echo("</td>");
		
		echo("</tr>\n");

		echo("<tr>\n");
		
		echo("<td>");
			echo("<div>Refresh Forecasts </div>");
		echo("</td>");
		echo("<td>");
			$theStatus = $theUtility->getRefreshForecastsChockFileStatus();
			echo($theStatus);
		echo("</td>");
		echo("<td>");
		//echo("<button type='button' type='submit' class='btn btn-primary btn-block'>Pattern Sets</button>\n");
		
		echo("<form action='servicesAction.php'>\n");	
		
			if ($theStatus == "stopped")
			{
				$buttonLabel = "Start";
				$mappedFunction = "unChockRefreshForecasts";
			}
			elseif ($theStatus == "running")
			{	
				$buttonLabel = "Stop";
				$mappedFunction = "chockRefreshForecasts";
			}	
			
			echo("<input type='hidden' name='function' value='$mappedFunction'>\n");
			echo("<input type='submit' name='theButton' value='$buttonLabel'>\n");
			
		echo("</form>\n");
		
		echo("</td>");
		
		echo("</tr>\n");
		
		echo("</table>\n");

		
		
		echo("</body>\n");
		
		echo("</html>\n");
		
		
		
		
		
		
	}
	elseif ($function == "chockGenerateNewPattern")
	{
		
		//echo("in function chockGenerateNewPattern<br>");
		
			//write finished questioning
			$fp5 = fopen($theUtility->generateNewPatternChockFile, 'w');
			
			//echo("opened file<br>");
			
			fwrite($fp5,  'done');
			
			//echo("wrote file<br>");
			
			fclose($fp5);
			
			//echo("closed file<br>");
			
			//return to list
			header ("Location: servicesAction.php?function=list");    
	}
	elseif ($function == "unChockGenerateNewPattern")
	{
		
		echo("in function unChockGenerateNewPattern<br>");
		
			//write finished questioning
			unlink($theUtility->generateNewPatternChockFile);
			
			//return to list
			header ("Location: servicesAction.php?function=list"); 
	}	
	elseif ($function == "chockCreateTrainingFiles")
	{
			//write finished questioning
			$fp5 = fopen($theUtility->createTrainingFileChockFile, 'w');
			fwrite($fp5,  'done');
			fclose($fp5);
			
			//return to list
			header ("Location: servicesAction.php?function=list");    
	}
	elseif ($function == "unChockCreateTrainingFiles")
	{
			//write finished questioning
			unlink($theUtility->createTrainingFileChockFile);
			
			//return to list
			header ("Location: servicesAction.php?function=list"); 
	}
	elseif ($function == "chockCreateEmergentScript")
	{
			//write finished questioning
			$fp5 = fopen($theUtility->createEmergentScriptChockFile, 'w');
			fwrite($fp5,  'done');
			fclose($fp5);
			
			//return to list
			header ("Location: servicesAction.php?function=list");    
	}
	elseif ($function == "unChockCreateEmergentScript")
	{
			//write finished questioning
			unlink($theUtility->createEmergentScriptChockFile);
			
			//return to list
			header ("Location: servicesAction.php?function=list"); 
	}
	elseif ($function == "chockRunEmergentScript")
	{
			//write finished questioning
			$fp5 = fopen($theUtility->runEmergentScriptChockFile, 'w');
			fwrite($fp5,  'done');
			fclose($fp5);
			
			//get rid of emergentScript and jobTicket
			//unlink('/tmp/runEmergent1.sh');
			//unlink('/tmp/jobTicket.txt');
			//unlink('/tmp/emergentUnderway.txt');
			//unlink('/tmp/chockFileFound.txt');
			
			unlink($theUtility->tempWriteablePath."runEmergent1.sh");
			unlink($theUtility->tempWriteablePath."jobTicket.txt");
			unlink($theUtility->tempWriteablePath."emergentUnderway.txt");
			unlink($theUtility->tempWriteablePath."chockFileFound.txt");
			
			
			//unlink('/tmp/finishedTraining.txt');
			
			//echo exec('sudo rm /tmp/runEmergent1.sh')."<br>";
			
			//return to list
			header ("Location: servicesAction.php?function=list");    
	}
	elseif ($function == "unChockRunEmergentScript")
	{
			//write finished questioning
			unlink($theUtility->runEmergentScriptChockFile);
			
			//return to list
			header ("Location: servicesAction.php?function=list"); 
	}
		elseif ($function == "chockCheckEmergent")
	{
			//write finished questioning
			$fp5 = fopen($theUtility->checkEmergentChockFile, 'w');
			fwrite($fp5,  'done');
			fclose($fp5);
			
			//return to list
			header ("Location: servicesAction.php?function=list");    
	}
	elseif ($function == "unChockCheckEmergent")
	{
			//write finished questioning
			unlink($theUtility->checkEmergentChockFile);
			
			//return to list
			header ("Location: servicesAction.php?function=list"); 
	}
	elseif ($function == "chockRefreshForecasts")
	{
			//write finished questioning
			$fp5 = fopen($theUtility->refreshForecastsChockFile, 'w');
			fwrite($fp5,  'done');
			fclose($fp5);
			
			//return to list
			header ("Location: servicesAction.php?function=list");    
	}
	elseif ($function == "unChockRefreshForecasts")
	{
			//write finished questioning
			unlink($theUtility->refreshForecastsChockFile);
			
			//return to list
			header ("Location: servicesAction.php?function=list"); 
	}
		
?>