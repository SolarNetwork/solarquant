
<?php

$id = $_REQUEST['reqId'];
$file = '/var/www/html/solarquant/php/log.txt';
$servername = "localhost";
$username = "solarquant";
$password = "solarquant";
$dbname = "solarquant";
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=".$id."_prediction.csv");

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
fputcsv($output, array('predictedWattHours', 'date'));

// fetch the data
$conn = new mysqli($servername, $username, $password, $dbname);
$query = 'SELECT PREDICTED_WATT_HOURS, DATE FROM prediction_output WHERE REQUEST_ID = '.$id;
$result = $conn->query($query);
file_put_contents($file, "gpoosdfsd", FILE_APPEND);
// loop over the rows, outputting them
while ($row = $result->fetch_assoc()) {
file_put_contents($file, "$row", FILE_APPEND);
fputcsv($output, $row);
file_put_contents($file, "$row", FILE_APPEND);

}


?>
