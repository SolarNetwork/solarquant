<?php

		//echo("in solarquant nodeAction<br>");
		
		
		
//imports
require_once "/var/www/html/solarquant/classes/node.php";

//echo("nodeAction after node import");

require_once "/var/www/html/solarquant/classes/SolarUtility.php";


		//echo("IN nodeAction after all imports 2<br>");
		
//break;


/* if there isn't an existing link */
//if ($link = " "){
	
	//echo("link is blank");
	
	/* create a link to the database*/
	//$link = mysql_connect ("127.0.0.1","solar","solar") or die ("Could not connect1");
	
	//centralize authentication
	//$theUtility = new SolarUtility;

//echo("before link<br>");

	//$link = mysql_connect ($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword) or die ("Could not connect1");
	//$link = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
	
	//echo("after link<br>");
	
//}
//else
//{
	//echo("link not blank");
//}

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

//debug
//echo("function:".$function."<br>");

	/* IF EDIT SKILLS of a person MOVE THIS TO SKILLACTION???*/
	if ($theButton == "Edit Skills") {
	

	


	} /* end button=editskills*/

	else {

	/* function = list*/
	if ($function == "list")
	{


		//instantiate object
		$theNode = new Node;

		//echo("after node function list<br>");
			
		echo("<html>");
		
		echo("<head>\n");
		echo("<title>solarquant.Admin</title>\n"); 
		echo("<link href='../css/solarStyle.css' type='text/css' rel='stylesheet'>");
		echo("<link href='../css/bootstrap.min.css' type='text/css' rel='stylesheet'>");
		echo("<link href='../css/bootstrap-theme.min.css' type='text/css' rel='stylesheet'>");
		echo("<script src='../js/bootstrap.min.js'></script>");
		echo("</head>\n");
		
		echo("<body bgcolor='#ffffff'>");
		
		$nodeType = trim($_REQUEST['type']);
		
		//echo("nodeType:".$nodeType."<br>");
		
		if ($nodeType != null)
		{
			
			
		}
		else 
		{
			$nodeType = "all";
		}
		
		//call list function
		$theNode->listAll("fullPage",-1,$nodeType);
		
		echo("</body");
		echo("</html>");


	}
	
	//function = add
	elseif ($function == "add")	{
	
		//instantiate object
		$theNode = new Node;
		
		//catch POSTed vars
		$theNode->id = trim($_REQUEST['nodeId']);
		$theNode->nodeTypeId = trim($_REQUEST['nodeTypeId']);
		
		$theNode->location = trim($_REQUEST['location']);
		$theNode->timeZone = trim($_REQUEST['timeZone']);
		//$theNode->isSubscribedForTraining = trim($_REQUEST['isSubscribedForTraining']);
		//$theNode->weatherNodeId = trim($_REQUEST['weatherNodeId']);
		$theNode->city = trim($_REQUEST['city']);
		$theNode->country = trim($_REQUEST['country']);
		$theNode->notes = trim($_REQUEST['notes']);
		
		//run update
		$theNode->add();

		//return to list
		header ("Location: nodeAction.php?function=list&type=actual");
		

	}
	//function = viewTraining 
	elseif ($function == "viewProgress")
	{

	
		// Start the session
		session_start();
		
		$_SESSION["theNodeId"] = trim($_REQUEST['nodeId']);
		
		echo "session theNodeId :".$_SESSION["theNodeId"]."<br />\n";

 		//return to list
		header ("Location: trainingProgress1.html");               

	}
	
	
	//function = createPattern 
	elseif ($function == "createSinglePattern")
	{
		
				//return to list
		header ("Location: addPatternSetForm.php?nodeId=".$_REQUEST['nodeId']); 

		
		
	}
	/* function = generateInputWeights*/
	elseif ($function == "generateInputWeights")
	{
		
				echo("<html>");
		echo("<head>\n");
		echo("<title>solarNetwork.Admin</title>\n"); 
		echo("<link href='../css/solarStyle.css' type='text/css' rel='stylesheet'>");
		echo("</head>\n");
		echo("<body class='solar4' bgcolor='#BFBFBF'>");
		
		//instantiate object
		$theNode = new Node;
				
		//catch POSTed vars
		$theNode->id = $_REQUEST['nodeId'];

		$startDay = "2008-09-20";
		$endDay = "2008-09-22";
		$patternName = "auckland test 1";
		$patternNotes = "test";
		$nodes=Array($theNode->id); 
		
		
		
		$theNode->generateNNInputWeights($startDay,$endDay,$nodes,$patternSetId);
		
		echo("</body>\n");
		echo("</html>");
		
	}
	/* function = edit*/
	elseif ($function == "edit")
	{
	
		//instantiate object
		$theNode = new Node;
				
		//catch POSTed vars
		$theNode->id = $_REQUEST['nodeId'];
		
		//echo "theContact.id:".$theContact->id;
		
		//run the edit method
		$theNode->edit();
		

		

	}
	// getSources
	elseif ($function == "refreshSources")
	{
	
		//instantiate object
		$theNode = new Node;
				
		//catch POSTed vars
		$theNode->id = $_REQUEST['nodeId'];
		
		//echo "theContact.id:".$theContact->id;
		
		//run the getSources method
		$theNode->refreshSources();
		

		

	}
	elseif ($function == "update")
	{

				//instantiate object
		$theNode = new Node;
		
		//catch POSTed vars
		$theNode->id = trim($_REQUEST['nodeId']);
		$theNode->wcIdentifier = trim($_REQUEST['wcIdentifier']);
		$theNode->location = trim($_REQUEST['location']);
		$theNode->timeZone = trim($_REQUEST['timeZone']);
		$theNode->isSubscribedForTraining = trim($_REQUEST['isSubscribedForTraining']);
		$theNode->weatherNodeId = trim($_REQUEST['weatherNodeId']);
		$theNode->subscribedSourceIds = trim($_REQUEST['sourceIds']);
		
		//echo "_REQUEST sourceIds:".$_REQUEST['sourceIds']."<br>";
		
		//run update
		$theNode->update();

		//return to list
		header ("Location: nodeAction.php?function=list&type=actual");
	


	}
	/* function = delete*/
	elseif ($function == "delete")
	{

		//instantiate object
		$theNode = new Node;
		
		//catch POSTed vars
		$theNode->id = trim($_REQUEST['nodeId']);

		//run delete
		$theNode->delete();

		//return to list
		header ("Location: nodeAction.php?function=list&type=actual");

                

	}
/* delete*/
	


}	/* else theButton*/

	/* now close the dbconnection 
	mysql_close ($link);
	$link = " ";
	*/

?>
