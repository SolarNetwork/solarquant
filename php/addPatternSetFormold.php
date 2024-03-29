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

<body bgcolor='#ffffff'>

	
<span class='solar4'>Add a new PatternSet</span><br><br>

<!--- add Entity --->
<form method=POST action="./patternSetAdded.php">

<?php
	//get class into the page
require_once('../classes/tc_calendar.php');
require_once ("../classes/SolarUtility.php");

//TODO make this reflect the patternset
$defaultEngineId = 2;

$nodeId = $_REQUEST['nodeId'];

if (isset($nodeId))
{
	$defaultNodeId = $nodeId;
	
	//determine if actual 
	if ($defaultNodeId <= 1000)
	{
		$theNodeType = "actual";
		
	}
	else //or virtual
	{
		$theNodeType = "virtual";
	}
}
else 
{
	$defaultNodeId = -1;
	//only show actual nodes for now
	$theNodeType = "actual";
	
}
//debug
//echo("nodeId:" . $nodeId."<br><br>");
//echo("theNodeType:" . $theNodeType."<br><br>");

?>

<?php
	/* if there isn't an existing link */
	if ($link = " "){
			/* create a link to the database*/
			
			//centralize authentication
			$theUtility = new SolarUtility;
			//$link = mysql_connect ($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword) or die ("Could not connect1");
			$link = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
			
	}
?>
<table cellpadding='15' width='700' cellspacing='15' class='table table-striped' border='0'>

<tr>
	<td ><span class="solar4">Analysis Engine</span></td>
	<td>
	
	<select class="solar4" name="analysisEngine"
					id="analysisEngine">
						<option value="Tensorflow">Tensorflow</option>
						<option value="Emergent">Emergent</option>
				</select>
	</td>
	
</tr>

<tr>
	<td ><span class="solar4">Nodes to include</span></td>
	<td>
	
	<?php
	

	
		require_once('../classes/node.php');
		$theNode = new Node;
	
			//call list function
		$theNode->listAll("checkBox",$defaultNodeId,$theNodeType);
	?>
	</td>
	
</tr>
<tr class='solar4'>
	<td ><span class="solar4">Start Date</span></td>
	<td>
		<?php

		$now = new DateTime;
		//echo("now year:".$now->format('Y')."<br>") ;
		//echo("now month:".$now->format('m')."<br>") ;
		//echo("now day:".$now->format('d')."<br>") ;
		
//instantiate class and set properties
$myCalendar = new tc_calendar("startDate", true);
$myCalendar->setIcon("images/iconCalendar.gif");


//$myCalendar->setDate(1, 8, 2014);
$myCalendar->setDate($now->format('d'), $now->format('m'), $now->format('Y'));

//output the calendar
$myCalendar->writeScript();	 
	
?>
	</td>
	
</tr>
<tr class='solar4'>
	<td ><span class="solar4">End Date</span></td>
	<td>
	
	<?php

//instantiate class and set properties
$myCalendar = new tc_calendar("endDate", true);
$myCalendar->setIcon("images/iconCalendar.gif");
//$myCalendar->setDate(1, 8, 2014);
$myCalendar->setDate($now->format('d'), $now->format('m'), $now->format('Y'));

//output the calendar
$myCalendar->writeScript();	 
	
?>
	</td>
	
</tr>
<tr>
	<td ><span class="solar4">PatternSet Name</span></td>
	<td><input class="solar4" type="text" name="patternSetName" value="" size="40"></td>
	
</tr>
<tr>
	<td ><span class="solar4">PatternSet Status Id <br>(0 = not processed queued for processing)</span></td>
	<td><input class="solar4" type="text" name="statusId" value="0" size="4"></td>
	
</tr>
<tr>
	<td ><span class="solar4">PatternSet Type Id <br>(1 = consumption, 2 = generation, 3 = both consumption and generation, 4 = forecast)</span></td>
	<td>
		
	<select class="solar4"  name="patternSetTypeId" id="patternSetTypeId">
	<option value="1">Consumption</option>
	<option value="2">Generation</option>
	<option value="3">Not Used</option>
	<option value="4">Forecast</option>
	</select>
	
	
	</td>
	
</tr>
<tr>
	<td><span class="solar4">Notes:</span></td>
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
