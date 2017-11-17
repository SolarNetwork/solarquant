function toggleCalendar(objname){
	var DivDisplay = document.getElementById(objname).style;
	if (DivDisplay.display  == 'none') {
	  DivDisplay.display = 'block';
	}else{
	  DivDisplay.display = 'none';
	}
}

function setValue(objname, d){
	document.getElementById(objname).value = d;

	var dp = document.getElementById(objname+"_dp").value;
	if(dp == true){
		var date_array = d.split("-");
		document.getElementById(objname+"_day").selectedIndex = date_array[2];
		document.getElementById(objname+"_month").selectedIndex = date_array[1];
		document.getElementById(objname+"_year").value = date_array[0];
		
		toggleCalendar('div_'+objname);
	}
}

function tc_setDay(objname, dvalue, path){
	var obj = document.getElementById(objname);
	var date_array = obj.value.split("-");
	
	if(isDate(dvalue, date_array[1], date_array[0])){
		obj.value = date_array[0] + "-" + date_array[1] + "-" + dvalue;
		
		var obj = document.getElementById(objname+'_frame');
		obj.src = path+"calendar_form.php?objname="+objname.toString()+"&selected_day="+dvalue+"&selected_month="+date_array[1]+"&selected_year="+date_array[0];
	}else document.getElementById(objname+"_day").selectedIndex = date_array[2];
}

function tc_setMonth(objname, mvalue, path){
	var obj = document.getElementById(objname);
	var date_array = obj.value.split("-");
	
	if(isDate(date_array[2], mvalue, date_array[0])){
		obj.value = date_array[0] + "-" + mvalue + "-" + date_array[2];
	
		var obj = document.getElementById(objname+'_frame');
		obj.src = path+"calendar_form.php?objname="+objname.toString()+"&selected_day="+date_array[2]+"&selected_month="+mvalue+"&selected_year="+date_array[0];
	}else document.getElementById(objname+"_month").selectedIndex = date_array[1];
}

function tc_setYear(objname, yvalue, path){
	var obj = document.getElementById(objname);
	var date_array = obj.value.split("-");
	
	if(isDate(date_array[2], date_array[1], yvalue)){
		obj.value = yvalue + "-" + date_array[1] + "-" + date_array[2];
	
		var obj = document.getElementById(objname+'_frame');
		obj.src = path+"calendar_form.php?objname="+objname.toString()+"&selected_day="+date_array[2]+"&selected_month="+date_array[1]+"&selected_year="+yvalue;
	}else document.getElementById(objname+"_year").value = date_array[0];
}

// Declaring valid date character, minimum year and maximum year
var minYear=1900;
var maxYear=2100;

function isInteger(s){
	var i;
    for (i = 0; i < s.length; i++){   
        // Check that current character is number.
        var c = s.charAt(i);
        if (((c < "0") || (c > "9"))) return false;
    }
    // All characters are numbers.
    return true;
}

function stripCharsInBag(s, bag){
	var i;
    var returnString = "";
    // Search through string's characters one by one.
    // If character is not in bag, append to returnString.
    for (i = 0; i < s.length; i++){   
        var c = s.charAt(i);
        if (bag.indexOf(c) == -1) returnString += c;
    }
    return returnString;
}

function is_leapYear(year){
	return (year % 4 == 0) ?
		!(year % 100 == 0 && year % 400 != 0)	: false;
}

function daysInMonth(month, year){
	var days = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	return (month == 2 && is_leapYear(year)) ? 29 : days[month-1];
}
	
function DaysArray(n) {
	for (var i = 1; i <= n; i++) {
		this[i] = 31
		if (i==4 || i==6 || i==9 || i==11) {this[i] = 30}
		if (i==2) {this[i] = 29}
   } 
   return this
}

function isDate(strDay, strMonth, strYear){
	strYr=strYear
	if (strDay.charAt(0)=="0" && strDay.length>1) strDay=strDay.substring(1)
	if (strMonth.charAt(0)=="0" && strMonth.length>1) strMonth=strMonth.substring(1)
	for (var i = 1; i <= 3; i++) {
		if (strYr.charAt(0)=="0" && strYr.length>1) strYr=strYr.substring(1)
	}
	month=parseInt(strMonth)
	day=parseInt(strDay)
	year=parseInt(strYr)
	if (strMonth.length<1 || month<1 || month>12){
		alert("Please enter a valid month")
		return false
	}
	if (strDay.length<1 || day<1 || day>31 || day > daysInMonth(month, year)){
		alert("Please enter a valid day")
		return false
	}
	if (strYear.length != 4 || year==0 || year<minYear || year>maxYear){
		alert("Please enter a valid 4 digit year between "+minYear+" and "+maxYear)
		return false
	}
	return true
}