<?php

$file = '/var/www/html/solarquant/php/log.txt';



$current = date('Y-m-d H:i:s');

file_put_contents($file, $current."\n", FILE_APPEND);


$servername = "localhost";
$username = "solarquant";
$password = "solarquant";
$dbname = "solarquant";

$conn = new mysqli($servername, $username, $password, $dbname);

$val = 'Null';
$cDate  = date('Y-m-d H:i:s');

file_put_contents($file, $date."\n", FILE_APPEND);

$node = trim($_REQUEST['nodeId']);

$source = trim($_REQUEST['sourceId']);
$initState = "1";
$engine = $_REQUEST['analysisEngine'];
$start_day= $_REQUEST['startDate_day'];
$start_month= $_REQUEST['startDate_month'];
$start_year= $_REQUEST['startDate_year'];

$end_day = $_REQUEST['endDate_day'];
$end_month = $_REQUEST['endDate_month'];
$end_year = $_REQUEST['endDate_year'];
$name = $_REQUEST['patternSetName'];

$notes = $_REQUEST['notes'];

$end = date('d-m-Y', "$end_day-$end_month-$end_year");
$start = date('d-m-Y', "$start_day-$start_month-$start_year");

file_put_contents($file, $node."\n", FILE_APPEND);
file_put_contents($file, $source."\n", FILE_APPEND);
file_put_contents($file, $engine."\n", FILE_APPEND);

$time_start = strtotime("$start_day-$start_month-$start_year");
$time_end = strtotime("$end_day-$end_month-$end_year");
$end = date('Y-m-d H:i:s',$time_end);
$start = date('Y-m-d H:i:s',$time_start);

$batchSize = $_REQUEST['batchSize'];
$epochs = $_REQUEST['epochs'];

$dynamic = isset($_REQUEST['endDateToggle']);
file_put_contents($file, "$dynamic", FILE_APPEND);

$query = "INSERT INTO training_requests VALUES($val,'$name', $node, '$source','$cDate',$initState, '$engine', '$start', '$end', $dynamic, '$notes')";
$idquery = "SELECT REQUEST_ID FROM training_requests ORDER BY REQUEST_ID DESC LIMIT 1";

file_put_contents($file, $query, FILE_APPEND);
if($conn->query($query) === TRUE){
    	file_put_contents($file, 'good', FILE_APPEND);
}else{
	file_put_contents($file, 'bad', FILE_APPEND);
	file_put_contents($file, "$end", FILE_APPEND);
	file_put_contents($file, "$start", FILE_APPEND);
	file_put_contents($file, "$start_year", FILE_APPEND);
}

$result = $conn->query($idquery);
$row = $result->fetch_assoc();
$id = $row['REQUEST_ID'];
$query2 = "INSERT INTO training_parameters VALUES($id, $epochs, $batchSize)";

file_put_contents($file, "$query2", FILE_APPEND);
if($conn->query($query2) === TRUE){
	file_put_contents($file, 'good 2', FILE_APPEND);
}









?>
