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

$node = trim($_REQUEST['nodeId']);
$nodeType = trim($_REQUEST['nodeTypeId']);
$location = trim($_REQUEST['location']);
$timeZone = trim($_REQUEST['timeZone']);
$city = trim($_REQUEST['city']);
$country = trim($_REQUEST['country']);
$country = trim($_REQUEST['notes']);

file_put_contents($file, $node."\n", FILE_APPEND);



$query = "INSERT INTO registered_nodes VALUES($node, $nodeType, '$location','$timeZone','$city', '$country', '$notes')";
file_put_contents($file, $query, FILE_APPEND);
if($conn->query($query) === TRUE){
    file_put_contents($file, 'good', FILE_APPEND);
}else{
    file_put_contents($file, 'bad', FILE_APPEND);
}
$json = file_get_contents("https://data.solarnetwork.net/solarquery/api/v1/pub/range/sources?nodeId=$node");

$jsonData = json_decode($json);
foreach($jsonData->data as $value){
    $query = "INSERT INTO node_source VALUES($node, '$value', 1)";
    file_put_contents($file, $value, FILE_APPEND);
    $conn->query($query);
}


?>
