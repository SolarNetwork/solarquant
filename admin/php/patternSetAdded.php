<?php
$servername = "localhost";
$username = "solarquant";
$password = "solarquant";
$dbname = "solarquant";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

$date  = new DateTime();
$node = trim($_REQUEST['nodeId'])
$source = trim($_REQUEST['sourceIds'])
$engine = $_REQUEST['analysisEngineId'];

$query = "INSERT INTO training_requests VALUES(NULL, $node, $source, 
				$date,1, $engine)
	

?>
