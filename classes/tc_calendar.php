<?php
//*********************************************************
// The php calendar component 
// written by TJ@triconsole
//
// version 1.5 (7 June 2008)


//bug fixed: Incorrect next month display show on 'February 2008'
//	- thanks Neeraj Jain for bug report
//
//bug fixed: Incorrect month comparable on calendar_form.php line 113
// - thanks Djenan Ganic, Ian Parsons, Jesse Davis for bug report
//
//add on: date on calendar form change upon textbox in datepicker mode
//add on: validate date enter from dropdown and textbox
//
//bug fixed: Calendar path not valid when select date from dropdown
// - thanks yamba for bug report
//********************************************************


class tc_calendar{
	var $icon;
	var $objname;
	var $txt;
	var $date_format;
	var $year_display_from_current;
	
	var $date_picker;
	var $path = '';
	
	var $day;
	var $month;
	var $year;	
	
	//calendar constructor
	function tc_calendar($objname, $date_picker = false){
		$this->objname = $objname;
		$this->txt = "Select";
		$this->date_format = "Y-m-d";
		$this->year_display_from_current = 30;
		$this->date_picker = $date_picker;
	}
	
	//check for leapyear
	function is_leapyear($year){
    	return ($year % 4 == 0) ?
    		!($year % 100 == 0 && $year % 400 <> 0)	: false;
    }
	
	//get the total day of each month in year
    function total_days($month,$year){
    	$days = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    	return ($month == 2 && $this->is_leapYear($year)) ? 29 : $days[$month-1];
    }
	
	function getDayNum($day){
		$headers = $this->getDayHeaders();
		return isset($headers[$day]) ? $headers[$day] : 0;
	}
	
	//get the day headers start from sunday till saturday
	function getDayHeaders(){
		return array("Sun"=>7, "Mon"=>1, "Tue"=>2, "Wed"=>3, "Thu"=>4, "Fri"=>5, "Sat"=>6);
	}
	
	function setIcon($icon){
		$this->icon = $icon;
	}
	
	function setText($txt){
		$this->txt = $txt;
	}
	
	//not currently in-used
	function setDateFormat($format){
		$this->date_format = $format;
	}

	//set default selected date
	function setDate($day, $month, $year){
		$this->day = $day;
		$this->month = $month;
		$this->year = $year;
	}
	
	//specified location of the calendar_form.php 
	function setPath($path){
		$last_char = substr($path, strlen($path)-1, strlen($path));
		if($last_char != "/") $path .= "/";
		$this->path = $path;
	}
	
	function writeScript(){
		$this->writeHidden();
		
		//check whether it is a date picker
		if($this->date_picker){
			$this->writeDay();
			$this->writeMonth();
			$this->writeYear();
			echo(" <a href=\"javascript:toggleCalendar('div_".$this->objname."');\">");
			if($this->icon){
				echo("<img src=\"".$this->icon."\" border=\"0\" align=\"absmiddle\" />");
			}else echo($this->txt);				
			echo("</a>");
			$div_display = "none";
			$iframe_position = "absolute";
			$dp=1;
		}else{
			$div_display = "block";
			$iframe_position = "relative";
			$dp=0;
		}
		
		//write the calendar container
		echo("<div id=\"div_".$this->objname."\" style=\"position:relative;display:".$div_display.";z-index:10000;\"><IFRAME id=\"".$this->objname."_frame\" style=\"DISPLAY:block; LEFT:0px; POSITION:".$iframe_position."; TOP:0px;\" src=\"".$this->path."calendar_form.php?objname=$this->objname&selected_day=".$this->day."&selected_month=".$this->month."&selected_year=".$this->year."\" frameBorder=\"0\" scrolling=\"no\" height=\"200\" width=\"181\"></IFRAME></div>");
	}
	
	//write the select box of days
	function writeDay(){
		echo("<select name=\"".$this->objname."_day\" id=\"".$this->objname."_day\" onChange=\"javascript:tc_setDay('".$this->objname."', this[this.selectedIndex].value, '$this->path');\">");
		echo("<option value=\"\">Day</option>");
		for($i=1; $i<=31; $i++){
			$selected = ((int)$this->day == $i) ? " selected" : "";
			echo("<option value=\"".str_pad($i, 2 , "0", STR_PAD_LEFT)."\"$selected>$i</option>");
		}
		echo("</select> ");
	}
	
	//write the select box of months
	function writeMonth(){
		echo("<select name=\"".$this->objname."_month\" id=\"".$this->objname."_month\" onChange=\"javascript:tc_setMonth('".$this->objname."', this[this.selectedIndex].value, '$this->path');\">");
		echo("<option value=\"\">Month</option>");
		for($i=1; $i<=12; $i++){
			$selected = ((int)$this->month == $i) ? " selected" : "";
			echo("<option value=\"".str_pad($i, 2, "0", STR_PAD_LEFT)."\"$selected>".date('F', mktime(0,0,0,$i,1,2000))."</option>");
		}
		echo("</select> ");
	}
	
	//write the year textbox
	function writeYear(){
		echo("<input type=\"textbox\" name=\"".$this->objname."_year\" id=\"".$this->objname."_year\" value=\"$this->year\" maxlength=4 size=5 onBlur=\"javascript:tc_setYear('".$this->objname."', this.value, '$this->path');\"> ");
	}
	
	//write hidden components
	function writeHidden(){
		$svalue = $this->year."-".$this->month."-".$this->day;
		echo("<input type=\"hidden\" name=\"".$this->objname."\" id=\"".$this->objname."\" value=\"$svalue\">");
		echo("<input type=\"hidden\" name=\"".$this->objname."_dp\" id=\"".$this->objname."_dp\" value=\"".$this->date_picker."\">");
	}
}
?>
