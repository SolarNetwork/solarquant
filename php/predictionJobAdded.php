<?php

$file = './log.txt';



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
$name = $_REQUEST['name'];
$source = trim($_REQUEST['sourceId']);
$initState = "1";
$engine = $_REQUEST['analysisEngine'];

$start_day= $_REQUEST['startDate_day'];
$start_month= $_REQUEST['startDate_month'];
$start_year= $_REQUEST['startDate_year'];

$end_day = $_REQUEST['endDate_day'];
$end_month = $_REQUEST['endDate_month'];
$end_year = $_REQUEST['endDate_year'];
$dynamic = isset($_REQUEST['endDateToggle']);
if($dynamic == Null){

	$dynamic = 0;
}

$time_start = strtotime("$start_day-$start_month-$start_year");
$time_end = strtotime("$end_day-$end_month-$end_year");
$end = date('Y-m-d H:i:s',$time_end);
$start = date('Y-m-d H:i:s',$time_start);

file_put_contents($file, $node."\n", FILE_APPEND);
file_put_contents($file, $source."\n", FILE_APPEND);
file_put_contents($file, $engine."\n", FILE_APPEND);

$queryinfo = "SELECT * FROM trained_models WHERE NODE_ID = $node AND SOURCE_ID = '$source'";
$result = $conn->query($queryinfo);
file_put_contents($file, "$queryinfo", FILE_APPEND);
if($result->num_rows > 0){
		$query = "INSERT INTO prediction_requests VALUES($val,'$name', $node, '$source','$cDate',$initState, '$engine', '$start',$dynamic, '$end')";
		file_put_contents($file, $query, FILE_APPEND);
	if($conn->query($query) === TRUE){
		file_put_contents($file, 'good', FILE_APPEND);
	}else{
		file_put_contents($file, 'bad', FILE_APPEND);
	}
}else{
}
header("location: predictionScreen/loadPredictionQueue.php"); 
?>
