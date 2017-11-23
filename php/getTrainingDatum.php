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
if ($theTrainingFileId == "")
{
	$theTrainingFileId = trim($_REQUEST['id']);
}

    
    
		/* setup sql*/
		//$sql = "select training_datum_id, training_file_id, batch, epoch, when_logged, sse from training_datum where training_file_id = ".$theTrainingFileId;
		//$sql = "select epoch, sse from training_datum where training_file_id = ".$theTrainingFileId." order by batch, epoch";
		//$sql = "SELECT (batch * epoch) AS totalepoch, sse FROM training_datum where training_file_id = ".$theTrainingFileId." ORDER BY batch, epoch";
		$sql = "SELECT (training_datum_id) AS totalepoch, sse FROM training_datum where training_file_id = ".$theTrainingFileId." ORDER BY training_datum_id";

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
			
		}
		
		echo $outputText;
	
		
?>
