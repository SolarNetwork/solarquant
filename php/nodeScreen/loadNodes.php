
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

</head>
<body bgcolor='#ffffff'>
	<script
		src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

	<link rel="stylesheet"
		href="https://unpkg.com/leaflet@1.2.0/dist/leaflet.css"
		integrity="sha512-M2wvCLH6DSRazYeZRIm1JnYyh22purTM+FDB5CsyxtQJYeKq83arPe5wgbNmcFXGqiSH2XR8dT/fJISVA1r/zQ=="
		crossorigin="" />
	<script src="https://unpkg.com/leaflet@1.2.0/dist/leaflet.js"
		integrity="sha512-lInM/apFSqyy1o6s89K4iQUKg6ppXEgsVxT35HbzUupEVRh2Eu9Wdl4tHj7dZO0s1uvplcYGmt3498TtHq+log=="
		crossorigin=""></script>
	<script type="text/javascript">
	
function deleteNode(nodeId){	
 	$.ajax({
		type: "POST",
		url: "deleteNode.php",
		data:"value="+nodeId,
		success: function(data){
			location.reload();
		}
		});

}


</script>
	<table cellpadding='10' cellspacing='10' class='table table-striped'
		border='0'>
		<tr class='solar4' bgcolor='#ffffff'>
			<td align='center'>
			
			<th>Node Id</th>
			<th>Location</th>
			<th>TimeZone</th>
			<th>Action</th>
		</tr>

<?php
$servername = "localhost";
$username = "solarquant";
$password = "solarquant";
$dbname = "solarquant";

$conn = new mysqli($servername, $username, $password, $dbname);

$query = "SELECT * FROM registered_nodes";

$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td></td>";
    echo "<td>" . $row['NODE_ID'] . "</td>";
    echo "<td>" . $row['LOCATION'] . "</td>";
    echo "<td>" . $row['TIMEZONE'] . "</td>";
    echo "<td><button type='button' onClick='deleteNode(".$row['NODE_ID'].")' class='btn btn-danger'>Delete</button></td>";
    echo "</tr>";
}



?>
</table>
</body>
