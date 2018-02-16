<!DOCTYPE public "-//w3c//dtd html 4.01 transitional//en" 
		"http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
	<title>solarquant.Admin</title>
<link href='../css/solarStyle.css' type='text/css' rel='stylesheet'>	
<link href='../css/bootstrap.min.css' type='text/css' rel='stylesheet'>	
<link href='../css/bootstrap-theme.min.css' type='text/css' rel='stylesheet'>
<script src='../js/bootstrap.min.js'></script>
<script language="javascript" src="../includes/calendar.js"></script>
</head>

<body bgcolor='#BFBFBF'>

	
<span class='solar4'>Add a new node</span><br><br>

<!--- add Entity --->
<form method=POST action="addNode.php">

<?php




?>

<table cellpadding='15' width='700' cellspacing='15' class='table table-striped' border='0'>


<tr>
	<td ><span class="solar4">Node ID</span></td>
	<td><input class="solar4" type="text" name="nodeId" value="" size="6"  required></td>
</tr>
<tr>
	<td><span class="solar4">Node Type Id (1 = actual, 2 = virtual)</span></td>
	<td><input class="solar4" type="text" name="nodeTypeId" value="" size="6"  required></td>
</tr>
<tr>
	<td ><span class="solar4">Location</span></td>
	<td><input class="solar4" type="text" name="location" value="" size="40"  required></td>
	
</tr>
<tr>
	<td ><span class="solar4">Time Zone (e.g. Pacific/Auckland)</span></td>
	<td><input class="solar4" type="text" name="timeZone" value="" size="40"  required></td>
	
</tr>
<tr>
	<td ><span class="solar4">City</span></td>
	<td><input class="solar4" type="text" name="city" value="" size="40"  required></td>
	
</tr>
<tr>
	<td ><span class="solar4">Country</span></td>
	<td><input class="solar4" type="text" name="country" value="" size="40"  required></td>
	
</tr>
<tr>
	<td ><span class="solar4">Notes:</span></td>
	<td>
	<textarea cols="70" rows="5" name="notes"></textarea>
	</td>
</tr>







</table>

<input type="hidden" name="function" value="add">
<input type="hidden" name="displayMode" value="fullPage">
<button type='submit' class='btn btn-success'>Add</button><br><br>



</form>

</body>
</html>
