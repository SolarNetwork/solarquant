<?php
require_once('../classes/tc_calendar.php');

$thispage = $_SERVER['PHP_SELF'];

$sld = (isset($_REQUEST["selected_day"])) ? $_REQUEST["selected_day"] : 0;
$slm = (isset($_REQUEST["selected_month"])) ? $_REQUEST["selected_month"] : 0;
$sly = (isset($_REQUEST["selected_year"])) ? $_REQUEST["selected_year"] : 0;

//echo("date: $sly-$slm-$sld");

if(isset($_REQUEST["m"]))
	$m = $_REQUEST["m"];
else
	$m = ($slm) ? $slm : date('m');

if(isset($_REQUEST["y"]))
	$y = $_REQUEST["y"];
else
	$y = ($sly) ? $sly : date('Y');

$objname = (isset($_REQUEST["objname"])) ? $_REQUEST["objname"] : "";

$cobj = new tc_calendar("");
//$cobj->setDate($sld, $slm, $sly);

$total_thismonth = $cobj->total_days($m, $y);

if($m == 1){
	$previous_month = 12;
	$previous_year = $y-1;
}else{
	$previous_month = $m-1;
	$previous_year = $y;
}

if($m == 12){
	$next_month = 1;
	$next_year = $y+1;
}else{
	$next_month = $m+1;
	$next_year = $y;
}

$total_lastmonth = $cobj->total_days($previous_month, $previous_year);
$today = date('Y-m-d');

$startdate = $cobj->getDayNum(date('D', strtotime($y."-".$m."-1")));
$startwrite = $total_lastmonth - ($startdate - 1);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
<link href="../includes/calendar.css" rel="stylesheet" type="text/css" />
<script language="javascript">
<!--
function setValue(){
	var f = document.calendarform;
	var date_selected = f.selected_year.value + "-" + f.selected_month.value + "-" + f.selected_day.value;
	
	window.parent.setValue(f.objname.value, date_selected);
}

function selectDay(d){
	var f = document.calendarform;
	f.selected_day.value = d.toString();
	f.selected_month.value = f.m[f.m.selectedIndex].value;
	f.selected_year.value = f.y[f.y.selectedIndex].value;
	
	setValue();
	
	f.submit();
}

function hL(E, mo){
	//clear last selected
	if(document.getElementById("select")){
		var selectobj = document.getElementById("select");
		selectobj.Id = "";
	}
	
	while (E.tagName!="TD"){
		E=E.parentElement;
	}
	
	E.Id = "select";
}

function selectMonth(m){
	var f = document.calendarform;
	f.selected_month.value = m;
}

function selectYear(y){
	var f = document.calendarform;
	f.selected_year.value = y;
}

function move(m, y){
	var f = document.calendarform;
	f.m.value = m;
	f.y.value = y;
	
	f.submit();
}
//-->
</script>
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<table border="0" cellspacing="0" cellpadding="4" id="mycalendar">
  <tr>
    <td colspan="2" align="center"><form id="calendarform" name="calendarform" method="post" action="<?=$thispage;?>" style="margin: 0px;">
      <table border="0" cellspacing="0" cellpadding="1">
        <tr>
          <td><select name="m" onchange="javascript:this.form.submit();">
		  <?php
		  for($f=1; $f<=12; $f++){
		  	$selected = ($f == $m) ? " selected" : "";			
		  	echo("<option value=\"$f\"$selected>".date('F', mktime(0,0,0,$f,1,2000))."</option>");
		  }
		  ?>
          </select>
          </td>
          <td><select name="y" onchange="javascript:this.form.submit();">
		  <?php
		  $thisyear = date('Y');
		  $year_display = $cobj->year_display_from_current;
		  for($year=$thisyear-$year_display; $year<=$thisyear+$year_display; $year++){
		  	$selected = ($year == $y) ? " selected" : "";
		  	echo("<option value=\"$year\"$selected>".date('Y', mktime(0,0,0,1,1,$year))."</option>");
		  }
		  ?>
          </select>
          </td>
        </tr>
      </table>
		<input name="selected_day" type="hidden" id="selected_day" value="<?=$sld;?>" />
		<input name="selected_month" type="hidden" id="selected_month" value="<?=$slm;?>" />
		<input name="selected_year" type="hidden" id="selected_year" value="<?=$sly;?>" />
		<input name="objname" type="hidden" id="objname" value="<?=$objname;?>" />
    </form>
    </td>
  </tr>
  <tr>
    <td colspan="2" class="bg"><table border="0" cellspacing="1" cellpadding="3">
	<?php
	$day_headers = array_keys($cobj->getDayHeaders());
	
	echo("<tr>");
	//write calendar day header
	foreach($day_headers as $dh){
		echo("<td align=\"center\" class=\"header\">".$dh."</td>");
	}
	echo("</tr>");
		
	echo("<tr>");

	$dayinweek_counter = 0;
	$row_count = 0;
	
	//write previous month
	for($day=$startwrite; $day<=$total_lastmonth; $day++){
		echo("<td align=\"center\" class=\"othermonth\">$day</td>");
		$dayinweek_counter++;
	}
	
	$date_num = $cobj->getDayNum(date('D', strtotime($previous_year."-".$previous_month."-".$total_lastmonth)));
	if($date_num == 6){
		echo("</tr><tr>");
		$row_count++;
	}

	//write current month
	for($day=1; $day<=$total_thismonth; $day++){
		$date_num = $cobj->getDayNum(date('D', strtotime($y."-".$m."-".$day)));
		
		$is_today = strtotime($y."-".$m."-".$day) - strtotime($today);
		$class = ($is_today == 0) ? " class=\"today\"" : " class=\"general\"";
				
		$is_selected = strtotime($y."-".$m."-".$day) - strtotime($sly."-".$slm."-".$sld);
		$class = ($is_selected == 0) ? " class=\"select\"" : $class;
		
		echo("<td align=\"center\"$class><a href=\"javascript:selectDay('$day');\">$day</a></td>");
		if($date_num == 6){
			echo("</tr>");
			if($day < $total_thismonth) echo("<tr>");
			$row_count++;
			
			$dayinweek_counter = 0;
		}else $dayinweek_counter++;
	}	
	
	//write next other month
	$write_end_days = (6-$dayinweek_counter)+1;
	if($write_end_days > 0){
		for($day=1; $day<=$write_end_days; $day++){
			echo("<td class=\"othermonth\" align=\"center\">$day</td>");
		}
		 echo("</tr>");
		 $row_count++;
	}
	
	//write fulfil row to 6 rows
	for($day=$row_count; $day<=5; $day++){
		echo("<tr>");
		$tmpday = $write_end_days+1;
		for($f=$tmpday; $f<=($tmpday+6); $f++){
			echo("<td class=\"othermonth\" align=\"center\">$f</td>");
		}
		$write_end_days += 6;
		echo("</tr>");
	}
	?>
</table></td>
  </tr>
  <tr>
    <td class="btn" width="50%"><a href="javascript:move('<?=$previous_month;?>', '<?=$previous_year;?>');">&lt;&lt; Previous</a> </td>
    <td align="right" class="btn" width="50%"><a href="javascript:move('<?=$next_month;?>', '<?=$next_year;?>');">Next &gt;&gt;</a> </td>
  </tr>
</table>

</body>
</html>
