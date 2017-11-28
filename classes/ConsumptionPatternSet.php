<?php

//require "../classes/node.php";
//require "../classes/ConsumptionDatum.php";
//require "../classes/Test1Datum.inc";
require_once "/var/www/html/solarquant/classes/SolarError.php";
require_once "/var/www/html/solarquant/classes/node.php";
require_once "/var/www/html/solarquant/classes/SolarUtility.php";

class ConsumptionPatternSet {

    var $id;
    var $trialName;
    var $patternSetId;
    //added 2017.09.17
    var $statusId;
    var $patternSetTypeId;
    
    var $name;
    var $notes;
    var $startDateTime;
    var $endDateTime;
    //datetime based weights
    var $timeOfDayWeight = 0;
    var $dayOfYearWeight = 0;
    var $isMondayWeight = 0;
    var $isTuesdayWeight = 0;
    var $isWednesdayWeight = 0;
    var $isThursdayWeight = 0;
    var $isFridayWeight = 0;
    var $isSaturdayWeight = 0;
    var $isSundayWeight = 0;
    //weather based weights
    var $barometricPressureWeight = 0;
    var $humidityOutsideWeight = 0;
    var $temperatureOutsideWeight = 0;
    //to be reviewed
    var $isConditionClearWeight = 0;
    var $isConditionClearNightWeight = 0;
    var $isConditionFewcloudsWeight = 0;
    var $isConditionFewcloudsNightWeight = 0;
    var $isConditionFogWeight = 0;
    var $isConditionOvercastWeight = 0;
    var $isConditionSevereAlertWeight = 0;
    var $isConditionShowersWeight = 0;
    var $isConditionShowersScatteredWeight = 0;
    var $isConditionSnowWeight = 0;
    var $isConditionStormWeight = 0;
    //general conditions
    var $isConditionGeneralHazeWeight = 0;
    var $isConditionGeneralWindyWeight = 0;
    var $isConditionGeneralWetWeight = 0;
    var $isConditionGeneralClearWeight = 0;
    var $isConditionGeneralCloudyWeight = 0;
    
    var $temperatureHotterWeight = 0;
    var $temperatureColderWeight = 0;
    var $kiloWattHoursWeight = 0;
    var $predictedKiloWattHoursWeight = 0;
    var $sse = 0;
    var $nodes = array();
    //added 2017.09.16
    var $nodeId;
    var $dbLink;
    
    function connectToDB()
    {
    	$theUtility = new SolarUtility;
    	$this->dbLink = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
    }




  
  
  function resetWeights()
    {
    $this->timeOfDayWeight = 0;
    $this->dayOfYearWeight = 0;
    $this->isMondayWeight = 0;
    $this->isTuesdayWeight = 0;
    $this->isWednesdayWeight = 0;
    $this->isThursdayWeight = 0;
    $this->isFridayWeight = 0;
    $this->isSaturdayWeight = 0;
    $this->isSundayWeight = 0;
    $this->barometricPressureWeight = 0;
    $this->humidityOutsideWeight = 0;
    $this->temperatureOutsideWeight = 0;
    //to be reviewed
    /*
    $this->isConditionClearWeight = 0;
    $this->isConditionClearNightWeight = 0;
    $this->isConditionFewcloudsWeight = 0;
    $this->isConditionFewcloudsNightWeight = 0;
    $this->isConditionFogWeight = 0;
    $this->isConditionOvercastWeight = 0;
    $this->isConditionSevereAlertWeight = 0;
    $this->isConditionShowersWeight = 0;
    $this->isConditionShowersScatteredWeight = 0;
    $this->isConditionSnowWeight = 0;
    $this->isConditionStormWeight = 0;
    */
    //general conditions
    $this->isConditionGeneralHazeWeight = 0;
    $this->isConditionGeneralWindyWeight = 0;
    $this->isConditionGeneralWetWeight = 0;
    $this->isConditionGeneralClearWeight = 0;
    $this->isConditionGeneralCloudyWeight = 0;
    
    $this->temperatureHotterWeight = 0;
    $this->temperatureColderWeight = 0;
    $this->kiloWattHoursWeight = 0;
    $this->predictedKiloWattHoursWeight = 0;
    $this->sse = 0;
    }
    
    //identify this trial with a unique string based on the node, startTime and endTime
    function prepareTrialName()
    {
    	    //set the 
    	    $theFormat = 'Y-m-d H:i:s';
    	    
    	    //debug
    	    //$objStartDateTime = DateTime::createFromFormat($theFormat, "2015-05-26 00:30:00");
    	    
    	    //create the DateTime objects based on startDateTime and endDateTime
    	    $objStartDateTime = DateTime::createFromFormat($theFormat, $this->startDateTime);
    	    $objEndDateTime = DateTime::createFromFormat($theFormat, $this->endDateTime);
    	    
    	    //debug    	    
    	    // $showStartDate = date("Ymd_His",$objStartDateTime);
    	    
    	    //create the string display versions of these value
    	     $showStartDate = $objStartDateTime->format('Ymd_His');
    	     $showEndDate = $objEndDateTime->format('Ymd_His');
	 
    	     //debug
    	     echo("before sql showStartDate:".$showStartDate."<br>");
    	     echo("before sql showEndDate:".$showEndDate."<br>");
	
    	     //det the nodeId to the default node
    	      $this->nodeId = $this->nodes[0];
    	     
    	     //set the trialName string
    	     $this->trialName = "N".$this->nodeId."_".$showStartDate."_".$showEndDate;
    	    
    }
    
    //add to the DBMS
    function add()
    {
    	    
    	    echo("before sql this->nodes[0]:".$this->nodes[0]."<br>");
    	    echo("before sql this->startDateTime:".$this->startDateTime."<br>");
    	    
    	   // $objStartDateTime = new DateTime($this->startDateTime);
    	    
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	
    	   //set the trialName property
    	   $this-> prepareTrialName();
	
	
    	    
    	   
    	    
    	    	/* setup sql*/
		$sql = "insert into consumption_input_pattern (
		trial_name,
		pattern_set_id, 
		node_id, 
		start_datetime, 
		end_datetime, 
		time_of_day_weight, 
		day_of_year_weight,
		is_monday_weight,
		is_tuesday_weight,
		is_wednesday_weight,
		is_thursday_weight,
		is_friday_weight,
		is_saturday_weight,
		is_sunday_weight,
		barometric_pressure_weight,
		humidity_outside_weight,
		temperature_outside_weight,";
		/*
		is_condition_clear_weight,
		is_condition_clear_night_weight,
		is_condition_fewclouds_weight,
		is_condition_fewcloudsnight_weight,
		is_condition_fog_weight,
		is_condition_overcast_weight,
		is_condition_severealert_weight,
		is_condition_showers_weight,
		is_condition_showers_scattered_weight,
		is_condition_snow_weight,
		is_condition_storm_weight,
		*/
		$sql .= "is_condition_general_haze_weight,
		is_condition_general_windy_weight,
		is_condition_general_wet_weight,
		is_condition_general_clear_weight,
		is_condition_general_cloudy_weight,
		temperature_hotter_weight,
		temperature_colder_weight,
		kilowatt_hours_weight,
		sse) 
		values (
		\"$this->trialName\",
		$this->patternSetId,
		$this->nodeId,
		\"$this->startDateTime\",
		\"$this->endDateTime\",
		$this->timeOfDayWeight,
		$this->dayOfYearWeight,
		$this->isMondayWeight,
		$this->isTuesdayWeight,
		$this->isWednesdayWeight,
		$this->isThursdayWeight,
		$this->isFridayWeight,
		$this->isSaturdayWeight,
		$this->isSundayWeight,
		$this->barometricPressureWeight,
		$this->humidityOutsideWeight,
		$this->temperatureOutsideWeight,";
		//to be reviewed
		/*
		$this->isConditionClearWeight,
		$this->isConditionClearNightWeight,
		$this->isConditionFewcloudsWeight,
		$this->isConditionFewcloudsNightWeight,
		$this->isConditionFogWeight,
		$this->isConditionOvercastWeight,
		$this->isConditionSevereAlertWeight,
		$this->isConditionShowersWeight,
		$this->isConditionShowersScatteredWeight,
		$this->isConditionSnowWeight,
		$this->isConditionStormWeight,
		*/
		$sql .= "$this->isConditionGeneralHazeWeight,
		$this->isConditionGeneralWindyWeight,
		$this->isConditionGeneralWetWeight,
		$this->isConditionGeneralClearWeight,
		$this->isConditionGeneralCloudyWeight,
		$this->temperatureHotterWeight,
		$this->temperatureColderWeight,
		$this->kiloWattHoursWeight,
		$this->sse)";

		echo("add consumptionPatternSet:". $sql. "<br>");
		
		//create utility
		$theUtility = new SolarUtility;

		/* execute sql*/
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("insert consumptionPatternSet sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
		
    }
    
    function updateKilowattHoursWeight()
    {
        	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
	    
	$sql = "UPDATE consumption_input_pattern SET kilowatt_hours_weight = ".$this->kiloWattHoursWeight." WHERE trial_name = '".$this->trialName."'"; 
	
	//debug
	echo("updateKilowattHoursWeight sql:". $sql. "<br>");
	
			//instantiate object
		$theError = new SolarError;
 		$theError->whenLogged = date("Y-m-d H:i:s");
		$theError->module = "ConsumptionPatternSet::updateKilowattHoursWeight";
		$theError->details = "updateKilowattHoursWeight sql: ".$sql;
	
	//create utility
	$theUtility = new SolarUtility;
	
	//execute sql
	//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("updateKilowattHoursWeight sql failed");
	$result = $this->dbLink->query($sql);
	//$result->data_seek(0);
    	    
    }
    
    function updatePredictedkWh()
    {
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    
    	    echo("updatePredictedkWh this->trialName:". $this->trialName. "<br>");
    	    
    	        //TODO switch to JOIN
    	        
    	        //first get consumption_input_pattern_id from trialName
    	        $sql = "SELECT consumption_input_pattern_id from consumption_input_pattern where trial_name = '".$this->trialName."'"; 
    	        
    	        //debug
    	        echo("updatePredictedkWh get consumption_input_pattern_id sql:". $sql. "<br>");
    	        
    	        //create utility
    	        $theUtility = new SolarUtility;
    	        
    	        /* execute sql*/
    	        //$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node construct sql failed");
    	        $result = $this->dbLink->query($sql);
    	        $result->data_seek(0);
		
		/* loop through results*/
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
			$this->id = $row["consumption_input_pattern_id"];
		}
    	        
		 //debug
    	        echo("after get consumption_input_pattern_id sql  this->id:". $this->id. "<br>");
		
    	        //then update inputpattern_extensions with predictedKiloWattHoursWeight
    	        /*
    	        $sql = "update inputpattern_extensions 
		set 
		predicted_kilowatt_hours_weight = ".$this->predictedKiloWattHoursWeight." 
		where consumption_input_pattern_id = ".$this->id;
		*/
		
		$sql = "insert into inputpattern_extensions (consumption_input_pattern_id, predicted_kilowatt_hours_weight) VALUES (".$this->id.",".$this->predictedKiloWattHoursWeight.")";
			
    	    
    	    	// OLD setup sql
    	    	/*
		$sql = "update consumption_input_pattern 
		set 
		predicted_kilowatt_hours_weight = ".$this->predictedKiloWattHoursWeight." 
		where trial_name = '".$this->trialName."'";
		*/
		
		echo("updatePredictedkWh sql:". $sql. "<br>");
		
		//instantiate object
		$theError = new SolarError;
 		$theError->whenLogged = date("Y-m-d H:i:s");
		$theError->module = "ConsumptionPatternSet::updatePredictedkWh";
		$theError->details = "updatePredictedkWh sql: ".$sql;
		
		//create utility
		$theUtility = new SolarUtility;
		
		// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node update sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
		
		/*
		if (!$result)
		{
			echo("updatePredictedkWh sql failed<br>");
			
		//instantiate object
		$theError = new SolarError;
 		$theError->whenLogged = date("Y-m-d H:i:s");
		$theError->module = "ConsumptionPattern";
		$theError->details = "updatePredictedkWh sql failed";
		
		}
		else
		{
			echo("updatePredictedkWh sql success<br>");
			
		//instantiate object
		$theError = new SolarError;
 		$theError->whenLogged = date("Y-m-d H:i:s");
		$theError->module = "ConsumptionPattern";
		$theError->details = "updatePredictedkWh sql success";
		}
		*/
    
    }
    function getStandardDeviation($patternSetId,$startDate,$endDate)
    {

    	    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	
    	    //TODO switch to JOIN
    	    $sql = "SELECT std(cip.kilowatt_hours_weight - ie.predicted_kilowatt_hours_weight) as standard_deviation FROM `consumption_input_pattern` cip
    	    INNER JOIN inputpattern_extensions ie
    	   ON cip.consumption_input_pattern_id = ie.consumption_input_pattern_id
    	    where cip.pattern_set_id = ".$patternSetId." AND cip.start_datetime >= '".$startDate."' AND cip.end_datetime <= '".$endDate."'";
    	    
    	    //$sql = "SELECT std(kilowatt_hours_weight - predicted_kilowatt_hours_weight) as standard_deviation FROM `consumption_input_pattern` where pattern_set_id = ".$patternSetId;
    	    
    	  //  echo("getStandardDeviation sql:". $sql. "<br>");
    	    
    	    //create utility
	    $theUtility = new SolarUtility;
    	    
    	// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("get standard dev sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		/* loop through results*/
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())	
		{
			$output = $row["standard_deviation"];
			
		}
		
		return $output;
    
		// echo("output:". $output. "<br>");
    
    }
    
    function constructFromPatternSetId()
    {
    	    echo("in constructFromPatternSetId<br>");
    	    
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	    
    	        //edited 2017.09.17
		$sql = "select pattern_set_id, pattern_set_type_id, status_id, start_date, end_date, pattern_set_name, notes from pattern_set where pattern_set_id = ".$this->patternSetId;
		
		echo("constructFromPatternSetId sql:". $sql. "<br>");
		
		//instantiate object
		$theError = new SolarError;
 		$theError->whenLogged = date("Y-m-d H:i:s");
		$theError->module = "ConsumptionPatternSet";
		$theError->details = "constructFromPatternSetId sql: ".$sql;
		
		//add the error
		$theError->add();
		
		//create utility
		$theUtility = new SolarUtility;

		/* execute sql*/
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node construct sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		
		/* loop through results*/
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
			$this->constructFromRow($row);
		}
    
		/* setup sql*/
		$sql = "select node_id from patternset_node_match where pattern_set_id = ".$this->patternSetId;
		
		//create utility
		$theUtility = new SolarUtility;
    
		/* execute sql*/
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node construct sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		//clear it out
		$this->nodes = array();
		
		/* loop through results*/
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
			$this->nodes[] = $row["node_id"];
			echo("added nodeId:".$row["node_id"]."<br>");
		}
		
		echo("after construction this->nodes[0]:".$this->nodes[0]."<br>");
		
    	   echo("out constructFromPatternSetId<br>");
    	    
    }
    function clearConsumptionNNInputWeights()
    {
    	    
    	    echo "in clearConsumptionNNInputWeights this->patternSetId :".$this->patternSetId."<br />"; 
    	    
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	
    	//clear out weighted patterns
		$deleteWeightedPatterns = "delete from consumption_input_pattern where pattern_set_id = ".$this->patternSetId;
		
				//create utility
		$theUtility = new SolarUtility;
		
		//execute sql
		//$deleteWeightsResult = mysql_db_query($theUtility->dbName,"$deleteWeightedPatterns") or die ("deleteWeightedPatterns failed");
		$deleteWeightsResult = $this->dbLink->query($deleteWeightedPatterns);
		//$deleteWeightsResult->data_seek(0);
		
    }
    function getWeatherDatum()
    {
    	    
    	    	echo "in getWeatherDatum this->patternSetId :".$this->patternSetId."<br />"; 
    	    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	
    	    	//count weatherDatum in this patternset
		$getWeatherDatumSQL = "select wd.* from weather_datum as wd INNER JOIN patternset_node_match as pnm ON wd.node_id = pnm.node_id where pnm.pattern_set_id = ".$this->patternSetId." and wd.when_logged > '".$this->startDate."' and wd.when_logged < '".$this->endDate."' order by when_logged ASC";
		
		echo("getWeatherDatumSQL:".$getWeatherDatumSQL."<br>");
		
		    			//instantiate object
		$theError = new SolarError;
		$theError->module = "ConsumptionPatternSet::getWeatherDatum";
		$theError->details = "getWeatherDatumSQL:".$getWeatherDatumSQL;
		$theError->add();
		
				//create utility
		$theUtility = new SolarUtility;
		
		//execute sql
		//$result = mysql_db_query($theUtility->dbName,"$getWeatherDatumSQL") or die ("getWeatherDatumSQL failed");
		$result = $this->dbLink->query($getWeatherDatumSQL);
		$result->data_seek(0);		
		
		//hand back result set
		return $result;
		
    }
    function getConsumptionDatum($theNodeId, $startDateTime, $endDateTime)
    {
    	    
    	    	echo "in getConsumptionDatum this->patternSetId :".$this->patternSetId."<br />"; 
    	       	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	
    	    	//count weatherDatum in this patternset
		$getConsumptionDatumSQL = "select * from consumption_datum where node_id = ".$theNodeId." and when_logged >= '".$startDateTime."' and  when_logged <= '".$endDateTime."'";
		
		echo("getConsumptionDatumSQL:".$getConsumptionDatumSQL."<br>");
		
				//create utility
		$theUtility = new SolarUtility;
		
		//execute sql
		//$result = mysql_db_query($theUtility->dbName,"$getConsumptionDatumSQL") or die ("getConsumptionDatumSQL failed");
		$result = $this->dbLink->query($getConsumptionDatumSQL);
		$result->data_seek(0);
		
		echo "out getConsumptionDatum this->patternSetId :".$this->patternSetId."<br />"; 
		
		//hand back result set
		return $result;
		
    }
    function constructFromRow($row)
    {
    	$this->startDate = $row["start_date"];
    	$this->endDate = $row["end_date"];
    	$this->name = $row["pattern_set_name"];
	$this->notes = $row["notes"];
	$this->patternSetTypeId = $row["pattern_set_type_id"];
	$this->statusId = $row["status_id"];

    }
    function processWeatherConditions($theWeatherCondition)
    {
    	 echo("in processWeatherConditions<br>");
    	 
    	 //TODO replace extra spaces
    	 //http://stackoverflow.com/questions/10810211/replace-multiple-spaces-and-newlines-with-only-one-space-in-php
    	 
    	 echo("theWeatherCondition:".$theWeatherCondition."<br>");
    	 //general_clear
    	 if (trim(strtoupper($theWeatherCondition)) == "CLEAR")
    	 {
    	 	 $this->isConditionGeneralClearWeight = 1;
    	 }
    	 elseif (trim(strtoupper($theWeatherCondition)) == "CLEARNIGHT")
    	 {
    	 	 $this->isConditionGeneralClearWeight = 1;
    	 }
    	 elseif (trim(strtoupper($theWeatherCondition)) == "SUNNY")
    	 {
    	 	 $this->isConditionGeneralClearWeight = 1;
    	 }
    	 elseif (trim(strtoupper($theWeatherCondition)) == "FINE")
    	 {
    	 	 $this->isConditionGeneralClearWeight = 1;
    	 }
    	 elseif (trim(strtoupper($theWeatherCondition)) == "FAIR AND WINDY")
    	 {
    	 	 $this->isConditionGeneralClearWeight = 1;
    	 	 $this->isConditionGeneralWindyWeight = 1;
    	 }
    	 elseif (trim(strtoupper($theWeatherCondition)) == "FAIR")
    	 {
    	 	 $this->isConditionGeneralClearWeight = 1;
    	 }
    	 //general_haze
    	 elseif (trim(strtoupper($theWeatherCondition)) == "BLOWING DUST")
    	 {
    	 	 $this->isConditionGeneralHazeWeight = 1;
    	 }
    	 elseif (trim(strtoupper($theWeatherCondition)) == "BLOWING DUST AND WINDY")
    	 {
    	 	 $this->isConditionGeneralHazeWeight = 1;
    	 }
    	 elseif (trim(strtoupper($theWeatherCondition)) == "FOG")
    	 {
    	 	 $this->isConditionGeneralHazeWeight = 1;
    	 }
    	 elseif (trim(strtoupper($theWeatherCondition)) == "HAZE")
    	 {
    	 	 $this->isConditionGeneralHazeWeight = 1;
    	 }
    	 elseif (trim(strtoupper($theWeatherCondition)) == "MIST")
    	 {
    	 	 $this->isConditionGeneralHazeWeight = 1;
    	 }
    	 elseif (trim(strtoupper($theWeatherCondition)) == "PARTIAL FOG")
    	 {
    	 	 $this->isConditionGeneralHazeWeight = 1;
    	 }
    	 elseif (trim(strtoupper($theWeatherCondition)) == "SHALLOW FOG")
    	 {
    	 	 $this->isConditionGeneralHazeWeight = 1;
    	 }
    	 elseif (trim(strtoupper($theWeatherCondition)) == "SMOKE")
    	 {
    	 	 $this->isConditionGeneralHazeWeight = 1;
    	 }
    	 elseif (trim(strtoupper($theWeatherCondition)) == "SNOW AND FOG")
    	 {
    	 	 $this->isConditionGeneralHazeWeight = 1;
    	 }
    	 elseif (trim(strtoupper($theWeatherCondition)) == "SNOW SHOWER")
    	 {
    	 	 $this->isConditionGeneralHazeWeight = 1;
    	 }
    	 //general_windy
    	 elseif (trim(strtoupper($theWeatherCondition)) == "WINDY")
    	 {
    	 	 $this->isConditionGeneralWindyWeight = 1;
    	 }
    	 //general_cloudy
    	 elseif (trim(strtoupper($theWeatherCondition)) == "CLOUDY")
    	 {
    	 	 $this->isConditionGeneralCloudyWeight = 1;
    	 }    	 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "CLOUDY AND WINDY")
    	 {
    	 	 $this->isConditionGeneralCloudyWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "MOSTLY CLOUDY")
    	 {
    	 	 $this->isConditionGeneralCloudyWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "MOSTLY CLOUDY AND WINDY")
    	 {
    	 	 $this->isConditionGeneralCloudyWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "PARTLY CLOUDY")
    	 {
    	 	 $this->isConditionGeneralCloudyWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "PARTLY CLOUDY AND WINDY")
    	 {
    	 	 $this->isConditionGeneralCloudyWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "THUNDER")
    	 {
    	 	 $this->isConditionGeneralCloudyWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "THUNDER AND WINDY")
    	 {
    	 	 $this->isConditionGeneralCloudyWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "THINDER IN THE VICINITY")
    	 {
    	 	 $this->isConditionGeneralCloudyWeight = 1;
    	 } 
    	 //general_wet
    	 elseif (trim(strtoupper($theWeatherCondition)) == "DRIZZLE")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "DRIZZLE AND WINDY")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "HEAVY DRIZZLE")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
     	 elseif (trim(strtoupper($theWeatherCondition)) == "HEAVY RAIN")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "HEAVY RAIN AND WINDY")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "HEAVY RAIN SHOWER")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "HEAVY RAIN SHOWER AND WINDY")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "HEAVY T-STORM")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "HEAVY T-STORM AND WINDY")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "LIGHT DRIZZLE")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "LIGHT DRIZZLE AND WINDY")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "LIGHT RAIN")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "LIGHT RAIN AND WINDY")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "LIGHT RAIN SHOWER")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 }   	 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "LIGHT RAIN SHOWER AND WINDY")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 }
    	 elseif (trim(strtoupper($theWeatherCondition)) == "LIGHT RAIN WITH THUNDER")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 }
    	 elseif (trim(strtoupper($theWeatherCondition)) == "LIGHT SNOW")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "HAIL")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "RAIN")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
     	 elseif (trim(strtoupper($theWeatherCondition)) == "RAIN AND WINDY")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "RAIN AND SLEET")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "RAIN AND SNOW")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "RAIN AND WINDY")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "RAIN SHOWER")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "RAIN SHOWER AND WINDY")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "SQUALLS")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "SHOWERS")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "SHOWERS IN THE VICINITY")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "FEW SHOWERS")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "T-STORM")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "T-STORM AND WINDY")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 elseif (trim(strtoupper($theWeatherCondition)) == "WINTRY MIX AND WINDY")
    	 {
    	 	 $this->isConditionGeneralWetWeight = 1;
    	 } 
    	 else
    	 {
    	 	 
    	 	 echo("new theWeatherCondition condition found <br>");
    	 	 
    	 	//instantiate object
		$theError = new SolarError;
    	 	 
    	 	 //TODO  collect all examples of unrecorded values
    	 	 
		$theError->whenLogged = date("Y-m-d H:i:s");
		$theError->module = "ConsumptionPatternSet";
		$theError->details = "new theWeatherCondition condition found: ".$theWeatherCondition;
		
		//add the error
		$theError->add();
		
		/*
		 
//general_haze		 
Blowing Dust
Blowing Dust  and  Windy
Fog
Haze
Mist
Partial Fog
Shallow Fog
Smoke
Snow  and  Fog
Snow Shower

//general_clear	
Clear
Clear Night
Sunny
Fine
Fair and Windy
Fair

//general_wind
Windy

//general_cloudy	
Cloudy
Cloudy  and  Windy
Mostly Cloudy
Mostly Cloudy  and  Windy
Mostly Cloudy and Windy
Partly Cloudy
Partly cloudy
Partly Cloudy  and  Windy
Partly Cloudy and Windy
Thunder
Thunder  and  Windy
Thunder in the Vicinity

//general_wet		
Drizzle
Drizzle  and  Windy
Hail
Heavy Drizzle
Heavy Rain
heavy Rain
Heavy Rain  and  Windy
heavy Rain and Windy
Heavy Rain Shower
Heavy Rain Shower  and  Windy
Heavy T-Storm
Heavy T-Storm  and  Windy
Light Drizzle
Light Drizzle  and  Windy
Light Rain
Light Rain  and  Windy
Light Rain Shower
Light Rain Shower  and  Windy
Light Rain Shower and Windy
Light Rain with Thunder
Light Snow
Hail
Rain
Rain  and  Windy
Rain and Sleet
Rain and Snow
Rain and Windy
Rain Shower
Rain Shower  and  Windy
Rain Shower and Windy
Squalls
Showers
Showers in the Vicinity
Few showers
T-Storm
T-Storm  and  Windy
Wintry Mix  and  Windy

//general_misc	
N/A
n/a
<blank>













Widespread Dust
Widespread Dust  and  Windy


		 
		*/
		
		
    	 }
    	 
    	 
    	 echo("out processWeatherConditions<br>");
    	 
    	 
    }
    
    function processDayOfWeek($theDate)
    {
    	 echo("in processDayOfWeek<br>");
    	 
    	 echo("theDate:".$theDate."<br>");
    	 
    	 //calculate day of week
    	 $theDayOfWeek = date('l', strtotime($theDate)); 
    	 
    	 echo("theDayOfWeek:".$theDayOfWeek."<br>");

    	 //set the right weight to 1
    	 if ($theDayOfWeek == "Monday")
    	 {
    	 	 $this->isMondayWeight = 1;	 
    	 }
    	 elseif ($theDayOfWeek == "Tuesday")
    	 {
    	 	 $this->isTuesdayWeight = 1;	 
    	 }    	 
    	 elseif ($theDayOfWeek == "Wednesday")
    	 {
    	 	 $this->isWednesdayWeight = 1;	 
    	 }  
    	 elseif ($theDayOfWeek == "Thursday")
    	 {
    	 	 $this->isThursdayWeight = 1;	 
    	 }
    	 elseif ($theDayOfWeek == "Friday")
    	 {
    	 	 $this->isFridayWeight = 1;	 
    	 }
    	 elseif ($theDayOfWeek == "Saturday")
    	 {
    	 	 $this->isSaturdayWeight = 1;	 
    	 }
    	 elseif ($theDayOfWeek == "Sunday")
    	 {
    	 	 $this->isSundayWeight = 1;	 
    	 }
    	 
    	 echo(" this->isMondayWeight:". $this->isMondayWeight."<br>");
    	 echo(" this->isTuesdayWeight:". $this->isTuesdayWeight."<br>");
    	 echo(" this->isWednesdayWeight:". $this->isWednesdayWeight."<br>");
    	 echo(" this->isThursdayWeight:". $this->isThursdayWeight."<br>");
    	 echo(" this->isFridayWeight:". $this->isFridayWeight."<br>");
    	 echo(" this->isSaturdayWeight:". $this->isSaturdayWeight."<br>");
    	 echo(" this->isSundayWeight:". $this->isSundayWeight."<br>");
    	 
    	 echo("out processDayOfWeek<br>");
    	 
    	 
    }
    
    function processDayOfYear($theDate)
    {
    	 echo("in processDayOfYear<br>");
    	 
    	 echo("theDate:".$theDate."<br>");
    	 
    	 //calculate date of year
    	 $theDayOfYear = date('z', strtotime($theDate));
    	 
    	 echo("theDayOfYear:".$theDayOfYear."<br>");

    	 //normalize this value
    	 $this->dayOfYearWeight = ($theDayOfYear/365);
    	 
    	 echo("this->dayOfYearWeight:".$this->dayOfYearWeight."<br>");
    	 
    	 echo("out processDayOfYear<br>");
    	 
    	 
    }
    
    function processTimeOfDay($theTimeOfDay)
    {
    	 echo("in processTimeOfDay<br>");
    	 
    	 echo("theTimeOfDay:".$theTimeOfDay."<br>");
    	 
    	 //calculate seconds from midnight
    	 $time = strtotime($theTimeOfDay); // Do some verification before this step
    	 $midnight = strtotime("00:00"); // Midnight measured in seconds since Unix Epoch
    	 $sinceMidnight = $time - $midnight; // Seconds since midnight
    	 
    	 echo("time:".$time."<br>");
    	 echo("midnight:".$midnight."<br>");
    	 echo("sinceMidnight:".$sinceMidnight."<br>");

    	 //normalize this value
    	 $this->timeOfDayWeight = ($sinceMidnight/86400);
    	 
    	 echo("this->timeOfDayWeight:".$this->timeOfDayWeight."<br>");
    	 
    	 echo("out processTimeOfDay<br>");
    	 
    	 
    }
    function processOutsideHumidity($theOutsideHumidity)
    {
    	 echo("in processOutsideHumidity<br>");
    	 
    	 echo("theOutsideHumidity:".$theOutsideHumidity."<br>");
    	 //TODO pull these out into a settable table for each node location
    	 $lowestHumidity = 0;
    	 $humidityRange = (100 - $lowestTemperature);

    	 //normalize this value
    	 $this->humidityOutsideWeight = (($theOutsideHumidity - $lowestHumidity) / $humidityRange);
    	 
    	 echo("this->humidityOutsideWeight:".$this->humidityOutsideWeight."<br>");
    	 
    	 echo("out processOutsideHumidity<br>");
    	 
    	 
    }
    function processOutsideTemperature($theOutsideTemperature)
    {
    	 echo("in processOutsideTemperature<br>");
    	 
    	 echo("theOutsideTemperature:".$theOutsideTemperature."<br>");
    	 //TODO pull these out into a settable table for each node location
    	 $lowestTemperature = -0.6;
    	 $temperatureRange = (32.4 - $lowestTemperature);

    	 //normalize this value
    	 $this->temperatureOutsideWeight = (($theOutsideTemperature - $lowestTemperature) / $temperatureRange);
    	 
    	 echo("this->temperatureOutsideWeight:".$this->temperatureOutsideWeight."<br>");
    	 
    	 echo("out processOutsideTemperature<br>");
    	 
    	 
    }
    
    //TODO we need a mode sent in that tells it to only generate kilowattHourWeight for example
    function generateNNInputWeights($mode)
    {
    	//mode options
    	//mode = actual is where we want to process the inout data for neural network weights for training from actual data
    	//mode = virtual is where we have only weather from a forecast and we do not have energy data
    	//mode = refreshEnergy is where we want to find real energy data for this patternSet and fill in the kilowattHourWeight column
    	  
        //debug
    	echo("in generateNNInputWeights<br>");
    	//echo("this->startDate:".$this->startDate."<br>");
    	
    	
    	
    			//instantiate object
		$theError = new SolarError;
		$theError->module = "ConsumptionPatternSet::generateNNInputWeights";
		$theError->details = "in generateNNInputWeights";
		$theError->add();
		
    	  
    	//get count of weatherDatum attached to this patternSetId
    	$weatherDatumCountResult = $this->getWeatherDatum();
    	
    	echo("in after getWeatherDatum<br>");
    	
    	    			//instantiate object
		$theError = new SolarError;
		$theError->module = "ConsumptionPatternSet::generateNNInputWeights";
		$theError->details = "after getWeatherDatum";
		$theError->add();
    	
    	//debug
    	//echo("totalWeatherDatum:".mysql_num_rows($weatherDatumCountResult)."<br>"); 
    	echo("totalWeatherDatum:".mysqli_num_rows($weatherDatumCountResult)."<br>"); 
    	
    	    			//instantiate object
		$theError = new SolarError;
		$theError->module = "ConsumptionPatternSet::generateNNInputWeights";
		$theError->details = "mysqli_num_rows(weatherDatumCountResult):".mysqli_num_rows($weatherDatumCountResult);
		$theError->add();
    	
	//construct the full node object
	$theNode = new Node();
	$theNode->id = $this->nodes[0];
	
	    	    			//instantiate object
		$theError = new SolarError;
		$theError->module = "ConsumptionPatternSet::generateNNInputWeights";
		$theError->details = "theNode->id:".$theNode->id;
		$theError->add();
	
	//debug
	echo("theNode->id:".$theNode->id."<br>");
	
	//have a reference value for what current draw could be for normalisation
	$theNode->getMaxAmps();
	
	//debug
	echo("theNode->maxAmps:".$theNode->maxAmps."<br>");
	
		    	    			//instantiate object
		$theError = new SolarError;
		$theError->module = "ConsumptionPatternSet::generateNNInputWeights";
		$theError->details = "theNode->maxAmps:".$theNode->maxAmps." for sourceId:";
		$theError->add();
    	  
    	//loop through all valid intervals of 30-min weatherdatum
	//while ($row = mysql_fetch_array ($weatherDatumCountResult))
	while ($row = $weatherDatumCountResult->fetch_assoc())
	{
		
				    	    			//instantiate object
		$theError = new SolarError;
		$theError->module = "ConsumptionPatternSet::generateNNInputWeights";
		$theError->details = "about to resetWeights";
		$theError->add();
		
		//reset weights for this row
		$this->resetWeights();
				
		//only process weather/context related rates if mode is acutal or virtual
		if (($mode == "actual") | ($mode == "virtual") )
		{
		
			//processWeatherConditions
			$this->processWeatherConditions($row["weather_condition"]);
			
			//debug
			echo("  after processWeatherConditions<br>-------------------------------<br>");
		  
			//processSkyConditions
			//$this->processSkyConditions();
			//echo("  after skyconditions<br>-------------------------------<br>");
		
			//processTimeOfDay
			$this->processTimeOfDay(substr($row["when_logged"], -8));
			
			//debug
			echo("  after processTimeOfDay<br>-------------------------------<br>");
	
			//processDayOfYear
			$this->processDayOfYear(substr($row["when_logged"], 0, 10));
			
			//debug
			echo("  after processDayOfYear<br>-------------------------------<br>");
	
			//processDayOfWeek
			$this->processDayOfWeek(substr($row["when_logged"], 0, 10));
			
			//debug
			echo("  after processDayOfWeek<br>-------------------------------<br>");
			
			//processOutsideHumidity
			$this->processOutsideHumidity($row["humidity"]);
			
			//debug
			echo("  after processOutsideHumidity<br>-------------------------------<br>");
	
			//processOutsideTemperature
			$this->processOutsideTemperature($row["temperature_celsius"]);
			
			//debug
			echo("  after processOutsideTemperature<br>-------------------------------<br>");
		
		}
		
		//get the futureDateTime of this weather segment (20 min
		//$date = '2011-04-8 08:29:49';
		$currentDate = strtotime($row["when_logged"]);
		$futureDate = $currentDate+(60*20);
		$formattedFutureDate = date("Y-m-d H:i:s", $futureDate);
		
		//set date ranges
		$this->startDateTime = date("Y-m-d H:i:s", $currentDate);
		$this->endDateTime = date("Y-m-d H:i:s", $futureDate);
		
		//log an logentry
		//$theError = new SolarError;
		//$theError->module = "ConsumptionPatternSet::generateNNInputWeights";
		//$theError->details = "when_logged row:".$row["when_logged"];
		//$theError->details .= "setting currentDate to:".$currentDate." and futureDate to:".$futureDate;
		//$theError->add();
		
		//debug
		echo("row[when_logged]:".$row["when_logged"]."<br>");
		echo("this->nodes[0]:".$this->nodes[0]."<br>");
		echo("currentDate:".$currentDate."<br>");
		echo("formattedFutureDate:".$formattedFutureDate."<br>");
		
		//only continue with energy if mode is actual or refresh energy
		if (($mode == "actual") | ($mode == "refreshEnergy") )
		{			
			
		
    	  	//for the single date range defined by this weatherDatum get all ConsumptionDatum 
    	  	$consumptionDatumSet = $this->getConsumptionDatum($this->nodes[0], $row["when_logged"], $formattedFutureDate);
    	  
    	  	//echo("totalconsumptionDatum:".mysql_num_rows($consumptionDatumSet)."<br>"); 
    	  	echo("totalconsumptionDatum:".mysqli_num_rows($consumptionDatumSet)."<br>"); 
    	  	
    	  	
    	  	//is this enough ConsumptionDatum to make a valid trial for training?
    	  	
    	  	//make this value set to the patternset's max + 5%
    	  	$maxAmps = ($theNode->maxAmps * 1.05);
    	  	$totalAmps = 0;
    	  	$validRows = 0;
    	  
    	  	//loop through these ConsumptionDatum
    	  	//while ($consumptionRow = mysql_fetch_array ($consumptionDatumSet))
    	  	while ($consumptionRow = $consumptionDatumSet->fetch_assoc())
    	  	{
    	  
    	  		//is this ConsumptionDatum valid?
    	  
    	  		//using datetime and weather parameters, create 24? weighted decimal values and 1 output value
				  echo("consumptionRow[amps]:".$consumptionRow["amps"]."<br>"); 
	    	  		
    	  		//filter out the low ones only if consumption
				if((($consumptionRow["amps"] > 0.1) && ($this->patternSetTypeId == 1)) || ($this->patternSetTypeId == 2))
    	  		{
					//get a total
					$totalAmps = $totalAmps + $consumptionRow["amps"];
					  
					//add another to validRows
    	  			$validRows++;
    	  		
    	  		}
    	  		
    	  		echo("maxAmps:".$maxAmps."<br>");
    	  		echo("totalAmps:".$totalAmps."<br>"); 
				echo("validRows:".$validRows."<br>"); 
				  

				//log an logentry
				$theError = new SolarError;
				$theError->module = "ConsumptionPatternSet::generateNNInputWeights";
				$theError->details = "found ".$validRows." valid rows";
				$theError->add();
    	  		
    	  		
    	  		if ($validRows > 0)
    	  		{
    	  			
    	  			//compute average and normalize it to the max
    	  			$this->kiloWattHoursWeight = (($totalAmps / $validRows) / $maxAmps);
    	  			
    	  			echo("this->kiloWattHoursWeight:".$this->kiloWattHoursWeight."<br>"); 

    	  		}
    	  		else
    	  		{
    	  			
					  echo("no valid rows this->kiloWattHoursWeight is zero<br>"); 
					  
					//log an logentry
					$theError = new SolarError;
					$theError->module = "ConsumptionPatternSet::generateNNInputWeights";
					$theError->details = "no valid rows this->kiloWattHoursWeight is zero";
					$theError->add();
    	  		
    	  		}
    	  
    	  
    	  
    	  	} //end while loop of ConsumptionDatum
    	  	
    	  	//if mode is actual
    	  	if ($mode == "actual")
    	  	{
    	  		
    	  		//log an logentry
				$theError = new SolarError;
				$theError->module = "ConsumptionPatternSet::generateNNInputWeights";
				$theError->details = "about to add() a ConsumptionPatternSet";
				$theError->add();
   	  	
    	  		//add this datum
    	  		$this->add();
    	  	}	
    	  	//if mode is refreshEnergy
    	  	elseif ($mode == "refreshEnergy")
    	  	{
    	  		
				//log an logentry
				$theError = new SolarError;
				$theError->module = "ConsumptionPatternSet::generateNNInputWeights";
				$theError->details = "about to prepareTrialName";
				$theError->add();
    	  	
				//set the trialName property
				$this->prepareTrialName();
						
				//log an logentry
				$theError = new SolarError;
				$theError->module = "ConsumptionPatternSet::generateNNInputWeights";
				$theError->details = "about to updateKilowattHoursWeight";
				$theError->add();
						
				//update this trial's kiloWattHoursWeight
				$this->updateKilowattHoursWeight();
    	  		
    	  	
    	  	} //refresjEnergy
    	  
    	  	} //only if mode = actual or refreshEnergy
    	  	
    	  	
    	  //not enough consumption datum so skip this weatherdatum

    	} //end loop weatherDatum
    	   


    	
    	
    	    
    	  echo("out generateNNInputWeights<br>");   
    }

}//end class

?>