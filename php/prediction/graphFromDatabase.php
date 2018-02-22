<?php

$servername = "localhost";
$username = "solarquant";
$password = "solarquant";
$dbname = "solarquant";
$file = '../log.txt';

$reqId =  $_REQUEST['reqId'];

$conn = new mysqli($servername, $username, $password, $dbname);
$query = "select WATT_HOURS, PREDICTED_WATT_HOURS, DATE from training_correlation where REQUEST_ID=".$reqId;
$result = $conn->query($query);

$out = array();
file_put_contents($file, "$query", FILE_APPEND);
while ($row = $result->fetch_assoc()) {
        $out[] = $row;
    }


$json_out = json_encode($out);   
echo $json_out;

?>
