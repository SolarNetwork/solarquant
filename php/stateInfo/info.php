<html>
<head>

<title>solarquant.Admin</title>
<link href='../../css/solarStyle.css' type='text/css' rel='stylesheet'>
<link href='../../css/bootstrap.min.css' type='text/css'
	rel='stylesheet'>
<link href='../../css/bootstrap-theme.min.css' type='text/css'
	rel='stylesheet'>
<script src='../../js/bootstrap.min.js'></script>
<link href='../../includes/calendar.css' rel='stylesheet'
	type='text/css' />
<script language='javascript' src='../../includes/calendar.js'></script>
<script src="sorttable.js"></script>



<style>
table.sortable thead {
    background-color:#eee;
    color:#666666;
    font-weight: bold;
    cursor: default;
}
</style>
</head>
<body>
<table  cellpadding='10' cellspacing='10' class='table table-striped sortable'
		border='0'>
	<thead>
		<tr class='solar4' bgcolor='#ffffff'>
			<th align='center'>
			
			<th>State</th>
			<th>Start Date</th>
			<th>Completion Date</th>

		</tr>
 </thead>
<tbody>



<?php
$servername = "localhost";
$username = "solarquant";
$password = "solarquant";
$dbname = "solarquant";

$conn = new mysqli($servername, $username, $password, $dbname);


$query = "SELECT * FROM training_state_time WHERE REQUEST_ID = ".$_REQUEST["reqId"]." ORDER BY STATE ASC";

$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {


    echo "<tr>";
    echo "<td></td>";
    echo "<td>" . getStatusByNumber($row['STATE'], $row['REQUEST_ID']) . "</td>";   
    echo "<td>" . $row['START_DATE'] . "</td>";
    echo "<td>".$row['COMPLETION_DATE']."</td>";
 
    echo "</tr>";
echo "</tbody>";
}
	
function getStatusByNumber($num,$reqId)
{
    $status = "";
    $type = "";
    switch ($num) {
        case 1:
            $status = "Initial";
	    $type = "btn-warning";
            break;
        case 2:
            $status = "Retrieving Data";
 	    $type = "btn-success";
            break;
        case 3:
            $status = "Training";
 	    $type = "btn-success";
            break;
        case 4:
            $status = "Finished";
	    $type = "btn-primary";
            break;
        case 5:
            $status = "Error";
            break;
    }
    return $status;
}
?>

</table>
<?php
	echo "<div> Coefficient of Variation: ";
	$query = "SELECT LOSS FROM training_evaluation WHERE REQUEST_ID = ".$_REQUEST["reqId"];

	$result = $conn->query($query);

	$row = $result->fetch_assoc();
	echo $row['LOSS'];

?>

	
	
