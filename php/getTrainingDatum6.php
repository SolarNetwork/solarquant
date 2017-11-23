<?php

//require_once "/var/www/html/solarquant/classes/node.php";
require_once "/var/www/html/solarquant/classes/SolarUtility.php";

//if there isn't an existing link 
if ($link = " "){
	//centralize authentication
	$theUtility = new SolarUtility;
	//$link = mysql_connect ($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword) or die ("Could not connect1");
	$link = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
}

//catch POSTed vars
//
//if ($theTrainingFileId == "")
//{
//	$theTrainingFileId = trim($_REQUEST['id']);
//}

    		// Start the session
		session_start();
		
		$theTrainingFileId = $_SESSION["theTrainingFileId"];
    
		/* setup sql*/
		//$sql = "select training_datum_id, training_file_id, batch, epoch, when_logged, sse from training_datum where training_file_id = ".$theTrainingFileId;
		//$sql = "select epoch, sse from training_datum where training_file_id = ".$theTrainingFileId." order by batch, epoch";
		//$sql = "SELECT (batch * epoch) AS totalepoch, sse FROM training_datum where training_file_id = ".$theTrainingFileId." ORDER BY batch, epoch";
		
		//TODO - change this to when_logged for now at least we see duration of crunching...
		//$sql = "SELECT (training_datum_id) AS totalepoch, sse FROM training_datum where training_file_id = ".$theTrainingFileId." ORDER BY training_datum_id";
		//$sql = "SELECT training_datum_id, batch,epoch, (training_datum_id) AS totalepoch, sse FROM training_datum where training_file_id = ".$theTrainingFileId." ORDER BY training_datum_id";
		$sql = "SELECT training_datum_id, ((batch+1)*epoch) AS totalepoch, batch,epoch, sse FROM training_datum where training_file_id = ".$theTrainingFileId." ORDER BY totalepoch ASC";

		//echo("add training file sql:". $sql. "<br>");
		
		//create utility
		$theUtility = new SolarUtility;

		//break;
		/* execute sql*/
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("get training datum sql failed");
		$result = $link->query($sql);
		$result->data_seek(0);
		
		//$data = array();
		
		$outputText = "totalepoch,sse\n";
    
		//for ($x = 0; $x < mysql_num_rows($result); $x++) {
		//	$data[] = mysql_fetch_assoc($result);
		//}
    
		//echo json_encode($data); 
		

    
		/* loop through results*/
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
			$outputText .= $row["totalepoch"].",".$row["sse"]."\n";	
			
			$_SESSION["epoch"] = $row["epoch"];
			$_SESSION["batch"] = $row["batch"];
			
		}
		
		$outputText = "totalepoch,sse 740,0.00156431 1438,0.00163218 1956,0.00193285 2344,0.00154747 3932,0.00139178 4616,0.00176741 5534,0.00162751 6696,0.00153356 7138,0.00126259 7778,0.00156478 10930,0.00129318 11493,0.00120363 14116,0.00129366 16227,0.00129138 21000,0.00148741";
		echo $outputText;
		
		//echo $_SESSION["epoch"];
	
		
?>
