<?php

require_once "/var/www/html/solarquant/classes/node.php";
require_once "/var/www/html/solarquant/classes/ConsumptionDatum.php";
require_once "/var/www/html/solarquant/classes/PowerDatum.php";
require_once "/var/www/html/solarquant/classes/SolarUtility.php";
require_once "/var/www/html/solarquant/classes/TrainingFile.php";
require_once "/var/www/html/solarquant/classes/SolarError.php";
require_once "/var/www/html/solarquant/classes/AnalysisEngine.php";
//require "../classes/WeatherDatum.php";

//patternSet statuses:

// -1 = flagged as incomplete (blue question mark)
// 0 = waiting - not processed queued for processing (notProcessed)
// 1 = currently in download process (could be stuck) (inProcess)
// 2 = finished data downloaded, (blue)                                                               
// 3 = NN weights created
// 4 = training file created (trainingFileCreated)
// 5 = training underway
// 6 = training completed successfully
// 7 = questioningFileUnderway
// 8 = questioning completed successfully

//patternSetTypes:
//  0 = not set
//  1 = just consumption
//  2 = just power
//  3 = both consumption and power
//  4 = future weather

//virtualWeatherTypes:
// 0 = Norwegian weather
// 1 = not Norwegian weather
//sourceIds are currently for one node only
//TODO need to expand this to per-node resolution

//sourceIds are currently for one node only
//TODO need to expand this to per-node resolution

class PatternSet {

    var $id;
    var $name;
    var $startDate;
    var $endDate;
    var $notes;
    var $nodes = array();
    var $trainingFiles = array();
    var $sourceIds;
	var $statusId;
	var $analysisEngineId;
    var $patternSetTypeId;
    var $virtualWeatherTypeId;
    var $dbLink;
    
    function connectToDB()
    {
    	$theUtility = new SolarUtility;
    	$this->dbLink = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
    }

    function generateDataTableContentsOld($showSaveTime)
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
    	
    	//evaluate variables
    	$fileHeaderComments = "Datafile for PatternSet ".$this->id."Generated on ".$showSaveTime;
    	$inputDataTableName = "inputData_PatternSet".$this->id;
    	$dataName = "data";
    	
    	//loop through weather input rows
    	$getWeatherInputRowsSql = "SELECT
    	node_id,
    	start_sample,
    	uv_index_weight,
    	temperature_hotter_weight,
    	temperature_colder_weight,
    	sky_conditions_weight,
    	humidity_weight,
    	visibility_weight,
    	barometric_high_pressure_weight,
    	barometric_low_pressure_weight,
    	barometer_falling_weight,
    	barometer_rising_weight,
    	barometer_steady_weight,
    	time_of_day_weight,
    	day_of_year_weight,
    	kilowatt_hours_weight 
    	FROM weather_input_pattern
    	where pattern_set_id = ".$this->id;
    	
    	
    			
		//create utility
		$theUtility = new SolarUtility;
    	
    	//execute sql
		//$weatherInputResult = mysql_db_query($theUtility->dbName,"$getWeatherInputRowsSql") or die ("getWeatherInputRowsSql failed");
		$weatherInputResult = $this->dbLink->query($getWeatherInputRowsSql);
		$weatherInputResult->data_seek(0);  
		
		//$totalInputRows = mysql_num_rows($weatherInputResult);
		$totalInputRows = mysqli_num_rows($weatherInputResult);
		
		
		/* loop through results*/
		//while ($row = mysql_fetch_array ($weatherInputResult))
		while ($row = $weatherInputResult->fetch_assoc())
		{
			
			$startDate = $row["startSample"];
			
		$startTime = strtotime($thePatternSet->startDate);
		$showStartDate = date("Ymd_His",$startTime);
		$endTime = strtotime($thePatternSet->endDate);
		$showEndDate = date("Ymd_His",$endTime);
			
			$nameColumnValues .= "\"N".$row["node_id"]."_".$showStartDate."_".$showEndDate."\"; ";
			
			$inputMatrixData .= 
			$row["uv_index_weight"].";".
			$row["temperature_hotter_weight"].";".
			$row["temperature_colder_weight"].";".
			$row["sky_conditions_weight"].";".
			$row["humidity_weight"].";".
			$row["visibility_weight"].";".
			$row["barometric_high_pressure_weight"].";".
			$row["barometric_low_pressure_weight"].";".
			$row["barometer_falling_weight"].";".
			$row["barometer_rising_weight"].";".
			$row["barometer_steady_weight"].";".
			$row["time_of_day_weight"].";".
			$row["day_of_year_weight"].";";
			
			$outputMatrixData .= $row["kilowatt_hours_weight"].";";
			

			
		}
    	
    	
    	
    	
    	//generate the file
		$theFile = "// ".$fileHeaderComments."
DataTable .projects[0].data.gp[0][0] { 
DataTableCols @.data = [3] {
String_Data @[0] { };
float_Data @[1] { };
float_Data @[2] { };
};
};
DataTable .projects[0].data.gp[0][0] {
name=\"".$inputDataTableName."\";
desc=;
data {
name=\"".$dataName."\";
desc=;
data {
name=\"data\";
el_typ=String_Data;
el_def=0;
String_Data @[0] {
name=\"Name\";
col_flags=SAVE_ROWS|SAVE_DATA;
is_matrix=0;
cell_geom{ 1;};
calc_expr {
expr=;
};
ar {
name=;
[".$totalInputRows."] ".$nameColumnValues."};
};
float_Data @[1] {
name=\"Input\";
col_flags=SAVE_ROWS|SAVE_DATA;
is_matrix=1;
cell_geom{ 13;1;};
calc_expr {
expr=;
};
ar {
name=;
[13 1 ".$totalInputRows."] ".$inputMatrixData."};
};
float_Data @[2] {
name=\"Output\";
col_flags=SAVE_ROWS|SAVE_DATA;
is_matrix=1;
cell_geom{ 1;1;};
calc_expr {
expr=;
};
ar {
name=;
[1 1 ".$totalInputRows."]".$outputMatrixData."};
};
};
data_flags=SAVE_ROWS|AUTO_CALC;
auto_load=NO_AUTO_LOAD;
auto_load_file=;
keygen 4 0=0;
};";
		
		return $theFile;
    }

     function generateDataTableContents($showSaveTime)
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
    	    	
    	//evaluate variables
    	$fileHeaderComments = "Datafile for PatternSet ".$this->id."Generated on ".$showSaveTime;
    	$inputDataTableName = "inputData_PatternSet".$this->id;
    	$dataName = "data";
    	
    	//loop through weather input rows
    	$getWeatherInputRowsSql = "SELECT
    	node_id,
    	start_sample,
    	end_sample,
    	uv_index_weight,
    	temperature_hotter_weight,
    	temperature_colder_weight,
    	sky_conditions_weight,
    	humidity_weight,
    	visibility_weight,
    	barometric_high_pressure_weight,
    	barometric_low_pressure_weight,
    	barometer_falling_weight,
    	barometer_rising_weight,
    	barometer_steady_weight,
    	time_of_day_weight,
    	day_of_year_weight,
    	kilowatt_hours_weight 
    	FROM weather_input_pattern";
    	
    	//TODO: check to see whether the pattern itself is actual or virtual
    	//if this is a forecast 
    	if ($this->patternSetTypeId == 4)
    	{
    		$getWeatherInputRowsSql .= " where kilowatt_hours_weight = 0 ";
    	}
    	//of this is a regular one we need to train on energy
    	elseif ($this->patternSetTypeId == 1) 
    	{
    		$getWeatherInputRowsSql .= " where kilowatt_hours_weight > 0 ";
    	}
    	
    	$getWeatherInputRowsSql .= " and pattern_set_id = ".$this->id;
    	
    			
		//create utility
		$theUtility = new SolarUtility;
    	
    	//execute sql
		//$weatherInputResult = mysql_db_query($theUtility->dbName,"$getWeatherInputRowsSql") or die ("getWeatherInputRowsSql failed");
		$weatherInputResult = $this->dbLink->query($getWeatherInputRowsSql);
		$weatherInputResult->data_seek(0); 
		
		echo(" getWeatherInputRowsSql:" . $getWeatherInputRowsSql."<br><br>");
		
		//$totalInputRows = mysql_num_rows($weatherInputResult); 
		$totalInputRows = mysqli_num_rows($weatherInputResult);
		
		echo(" totalInputRows:" . $totalInputRows."<br><br>");
		
		$theFileContents = "";
		
		$headerContents[] = "_H:";
		$headerContents[] = "$"."Name";
		$headerContents[] = "%Input[2:0,0]<2:13,1>";
		
		
		//generate header row
		for ($i = 1;$i<13;$i++)
		{
			$headerContents[] = "%Input[2:".$i.",0]";
		}
		
		$headerContents[] = "%Output[2:0,0]<2:1,1>";
		
		//add header to file content output
		$theFileContents .= implode("\t",$headerContents)."\n";
		
		/* loop through results*/
		//while ($row = mysql_fetch_array ($weatherInputResult))
		while ($row = $weatherInputResult->fetch_assoc())
		{
			
			$startDate = $row["start_sample"];
			
			$endDate = $row["end_sample"];
			
			echo(" startDate:" . $startDate."<br><br>");	
			echo(" endDate:" . $endDate."<br><br>");	
			
		$startTime = strtotime($startDate);
		$showStartDate = date("Ymd_His",$startTime);
		$endTime = strtotime($endDate);
		$showEndDate = date("Ymd_His",$endTime);
			
			$rowContents = array();
			$rowContents[] = "_D:";
			$rowContents[] = "\"N".$row["node_id"]."_".$showStartDate."_".$showEndDate."\"";
			
			$rowContents[] = $row["uv_index_weight"];
			$rowContents[] = $row["temperature_hotter_weight"];
			$rowContents[] = $row["temperature_colder_weight"];
			$rowContents[] = $row["sky_conditions_weight"];
			$rowContents[] = $row["humidity_weight"];
			$rowContents[] = $row["visibility_weight"];
			$rowContents[] = $row["barometric_high_pressure_weight"];
			$rowContents[] = $row["barometric_low_pressure_weight"];
			$rowContents[] = $row["barometer_falling_weight"];
			$rowContents[] = $row["barometer_rising_weight"];
			$rowContents[] = $row["barometer_steady_weight"];
			$rowContents[] = $row["time_of_day_weight"];
			$rowContents[] = $row["day_of_year_weight"];
			$rowContents[] =  $row["kilowatt_hours_weight"];
			
			//add header to file content output
			$theFileContents .= implode("\t",$rowContents)."\n";
			
		}
    	
    	
    	
    	
    	//generate the file
		
		
		return $theFileContents;
    }
    
    function generateConsumptionDataTableContents($showSaveTime)
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
    	    	
    	//evaluate variables
    	$fileHeaderComments = "Datafile for PatternSet ".$this->id."Generated on ".$showSaveTime;
    	$inputDataTableName = "inputData_PatternSet".$this->id;
    	$dataName = "data";
    	$inputDimensions = 17;
    	
    	//loop through weather input rows
    	$getWeatherInputRowsSql = "SELECT
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
    	temperature_hotter_weight,
    	temperature_colder_weight,
    	*/
    	$getWeatherInputRowsSql .=
    	"is_condition_general_haze_weight,
    	is_condition_general_windy_weight,
    	is_condition_general_wet_weight,
    	is_condition_general_clear_weight,
    	is_condition_general_cloudy_weight,
    	kilowatt_hours_weight
    	FROM consumption_input_pattern";
    	
    	/*
    	//TODO: check to see whether the pattern itself is actual or virtual
    	if ($this->id == 9)
    	{
    		$getWeatherInputRowsSql .= " where kilowatt_hours_weight = 0 ";
    	}
    	else 
    	{
    		$getWeatherInputRowsSql .= " where kilowatt_hours_weight > 0 ";
    	}
    	*/
    	
    	$getWeatherInputRowsSql .= " WHERE node_id = ".$this->nodes[0];
    	
    	//this needs to be omitted and we will select all the available data for this node
    	$getWeatherInputRowsSql .= " AND pattern_set_id = ".$this->id;

    	//if this is an actual consumption
    	if ($this->patternSetTypeId == 1)
    	{
    		$getWeatherInputRowsSql .= " AND kilowatt_hours_weight > 0";
		}    
		//if this is an actual generation 
		elseif ($this->patternSetTypeId == 2)
		{
			//$getWeatherInputRowsSql .= " AND kilowatt_hours_weight = 0";
		}	
    	//if this is a forecast 
    	elseif ($this->patternSetTypeId == 4)
    	{
    		$getWeatherInputRowsSql .= " AND kilowatt_hours_weight = 0";
    	}
    	
    			
		//create utility
		$theUtility = new SolarUtility;
    	
    	//execute sql
		//$weatherInputResult = mysql_db_query($theUtility->dbName,"$getWeatherInputRowsSql") or die ("getWeatherInputRowsSql failed");
		$weatherInputResult = $this->dbLink->query($getWeatherInputRowsSql);
		$weatherInputResult->data_seek(0);
		
		echo(" getWeatherInputRowsSql:" . $getWeatherInputRowsSql."<br><br>");
		
		//$totalInputRows = mysql_num_rows($weatherInputResult);
		$totalInputRows = mysqli_num_rows($weatherInputResult);
		
		
		echo(" totalInputRows:" . $totalInputRows."<br><br>");
		
		$theFileContents = "";
		
		$headerContents[] = "_H:";
		$headerContents[] = "$"."Name";
		//$headerContents[] = "%Input[2:0,0]<2:25,1>";
		$headerContents[] = "%Input[2:0,0]<2:".$inputDimensions.",1>";
		
		
		//generate header row
		for ($i = 1;$i<$inputDimensions;$i++)
		{
			$headerContents[] = "%Input[2:".$i.",0]";
		}
		
		$headerContents[] = "%Output[2:0,0]<2:1,1>";
		
		//add header to file content output
		$theFileContents .= implode("\t",$headerContents)."\n";
		
		/* loop through results*/
		//while ($row = mysql_fetch_array ($weatherInputResult))
		while ($row = $weatherInputResult->fetch_assoc())
		{
			
			$startDate = $row["start_datetime"];
			
			$endDate = $row["end_datetime"];
			
			echo(" startDate:" . $startDate."<br><br>");	
			echo(" endDate:" . $endDate."<br><br>");	
			
		$startTime = strtotime($startDate);
		$showStartDate = date("Ymd_His",$startTime);
		$endTime = strtotime($endDate);
		$showEndDate = date("Ymd_His",$endTime);
			
			$rowContents = array();
			$rowContents[] = "_D:";
			$rowContents[] = "\"N".$row["node_id"]."_".$showStartDate."_".$showEndDate."\"";
			
			$rowContents[] = $row["time_of_day_weight"];
			$rowContents[] = $row["day_of_year_weight"];
			$rowContents[] = $row["is_monday_weight"];
			$rowContents[] = $row["is_tuesday_weight"];
			$rowContents[] = $row["is_wednesday_weight"];
			$rowContents[] = $row["is_thursday_weight"];
			$rowContents[] = $row["is_friday_weight"];
			$rowContents[] = $row["is_saturday_weight"];
			$rowContents[] = $row["is_sunday_weight"];
			$rowContents[] = $row["barometric_pressure_weight"];
			$rowContents[] = $row["humidity_outside_weight"];
			$rowContents[] = $row["temperature_outside_weight"];
			//to be reviewed
			/*
			$rowContents[] = $row["is_condition_clear_weight"];
			$rowContents[] = $row["is_condition_clear_night_weight"];
			$rowContents[] = $row["is_condition_fewclouds_weight"];
			$rowContents[] = $row["is_condition_fewcloudsnight_weight"];
			$rowContents[] = $row["is_condition_fog_weight"];
			$rowContents[] = $row["is_condition_overcast_weight"];
			$rowContents[] = $row["is_condition_severealert_weight"];
			$rowContents[] = $row["is_condition_showers_scattered_weight"];
			$rowContents[] = $row["is_condition_clear_weight"];
			$rowContents[] = $row["is_condition_snow_weight"];
			$rowContents[] = $row["is_condition_storm_weight"];
			$rowContents[] = $row["temperature_hotter_weight"];
			$rowContents[] = $row["temperature_colder_weight"];
			*/
			
			//general conditions
			$rowContents[] = $row["is_condition_general_haze_weight"];
			$rowContents[] = $row["is_condition_general_windy_weight"];
			$rowContents[] = $row["is_condition_general_wet_weight"];
			$rowContents[] = $row["is_condition_general_clear_weight"];
			$rowContents[] = $row["is_condition_general_cloudy_weight"];
			
			$rowContents[] =  $row["kilowatt_hours_weight"];
			
			//add header to file content output
			$theFileContents .= implode("\t",$rowContents)."\n";
			
		}
    	
    	
    	
    	
    	//generate the file
		
		
		return $theFileContents;
    }
    
    function clearNNInputWeights()
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
    	
    	echo "in clearNNInputWeights this->id :".$this->id."<br />"; 
    	    
    	//clear out weighted patterns
		$deleteWeightedPatterns = "delete from weather_input_pattern where pattern_set_id = ".$this->id;
		
				
		//create utility
		$theUtility = new SolarUtility;
		
		//execute sql
		//$deleteWeightsResult = mysql_db_query($theUtility->dbName,"$deleteWeightedPatterns") or die ("deleteWeightedPatterns failed");
		$deleteWeightsResult = $this->dbLink->query($deleteWeightedPatterns);
		//$deleteWeightsResult->data_seek(0);
		
    }
    function cURLcheckBasicFunctions() 
    { 
  if( !function_exists("curl_init") && 
      !function_exists("curl_setopt") && 
      !function_exists("curl_exec") && 
      !function_exists("curl_close") ) return false; 
  else return true;
  }
    
  //this checks it's own patternSetTypeId and pulls the right datum depending on what kind of patternSetTypeId it is and which statusId it is at
  function getMyDatum()
  {
  	  
  	  
  	  
	//log an logentry
	$theError = new SolarError;
	$theError->module = "PatternSet::getMyDatum";
	$theError->details = "for patternset:".$this->id." patternSetTypeId:".$this->patternSetTypeId." and statusId:".$this->statusId ;
	$theError->add();
		


  	//if the status is 1 - brand new empty but flagged for processing
  	if ($this->statusId == 1) 
  	{
  	
  		
  	  	//if the type is 1,2,3
  	  	if ( ($this->patternSetTypeId == 1) | ($this->patternSetTypeId == 2) | ($this->patternSetTypeId == 3) )
  	  	{
  	  	
  	  	 
  	  	
			//log an logentry
			$theError = new SolarError;
			$theError->module = "PatternSet::getMyDatum";
			$theError->details = "for patternset:".$this->id." about to getDatumFromSolarNet";
			$theError->add();
	
  			//always pull weather but use the weatherNodeId for this node
  			$this->getDatumFromSolarNet("Weather");
  		
  			
		
  		}
		//if 4 futureConsumption
		elseif ($this->patternSetTypeId == 4)
		{
		 
		
			
			//log an logentry
			$theError = new SolarError;
			$theError->module = "PatternSet::getMyDatum";
			$theError->details = "for patternset:".$this->id." about to virtualWeatherDatum";
			$theError->add();
		
			//get virtualWeatherDatum
			$this->getVirtualWeatherFromForecast();
			
			//$this->getDatumFromSolarNet("Weather");
			
			
		
		}
		
		
		//debug
		echo("this->patternSetTypeId:".$this->patternSetTypeId." <br>");
		
		
		//just consumption
		if ($this->patternSetTypeId == 1) 
		{
			$this->getDatumFromSolarNet("Consumption");
		}
		//just power
		elseif ($this->patternSetTypeId == 2) 
		{
			//TODO check this out
			//$this->getDatumFromSolarNet("Power");
			$this->getDatumFromSolarNet("Consumption");
		}
		//both consumption and power
		elseif ($this->patternSetTypeId == 3) 
		{
			$this->getDatumFromSolarNet("Power");
			$this->getDatumFromSolarNet("Consumption");
		}
	
	}
	//if the status is like training has been done (8)
	elseif ($this->statusId == 8)
	{
		
		
			
			//log an logentry
			$theError = new SolarError;
			$theError->module = "PatternSet::getMyDatum";
			$theError->details = "for patternset:".$this->id." statusId == 8";
			$theError->add();
			
		//if we have a futureForecast patternSet
		if ($this->patternSetTypeId == 4) 
		{
			
			//log an logentry
			$theError = new SolarError;
			$theError->module = "PatternSet::getMyDatum";
			$theError->details = "for patternset:".$this->id." about to try to deleteConsumptionDatum";
			$theError->add();
			
	
			//construct the full node object
			$theNode = new Node();
			$theNode->id = $this->nodes[0];
			$theNode->constructFromId();

			//delete the existing consumption datum
			$theNode->deleteConsumptionDatum($this->startDate,$this->endDate);
			
			//log an logentry
			$theError = new SolarError;
			$theError->module = "PatternSet::getMyDatum";
			$theError->details = "for patternset:".$this->id." about to try to clearKilowattHourWeight";
			$theError->add();
				
			//clear my kilowattHourWeights in case they already have been filled in?
			$this->clearKilowattHourWeight();
			
			//log an logentry
			$theError = new SolarError;
			$theError->module = "PatternSet::getMyDatum";
			$theError->details = "for patternset:".$this->id." about to try to getDatumFromSolarNet consumption for startDate:".$this->startDate." and this endDate:".$this->endDate;
			$theError->add();
				
			//get my consumption what's there anyway
			$this->getDatumFromSolarNet("Consumption");
			
			//log an logentry
			$theError = new SolarError;
			$theError->module = "PatternSet::getMyDatum";
			$theError->details = "for patternset:".$this->id." after getDatumFromSolarNet consumption";
			$theError->add();
				
		} //patternSetTypeId == 4
		

 
	} //statusId is 8
  	  
	
  }
  
  
    function getDatumFromSolarNet($datumType)
    {
    	  // echo("cURL functions are valid:". this->cURLcheckBasicFunctions()."<br>");
    	  
    	  //set vars
    	  $noAuthenticationAvailable = false;
     	    
    	if (function_exists("curl_init")){
			echo("curl_init OK<br>");
		}
		else
		{
			echo("curl_init NOT OK<br>");;
		}
	
	   if (function_exists("curl_setopt")){
			echo("curl_setopt OK<br>");
		}
		else
		{
			echo("curl_setopt NOT OK<br>");;
		}
		
		if (function_exists("curl_exec")){
			echo("curl_exec OK<br>");
		}
		else
		{
			echo("curl_exec NOT OK<br>");;
		}
		if (function_exists("curl_close")){
			echo("curl_close OK<br>");
		}
		else
		{
			echo("curl_close NOT OK<br>");;
		}
		
		
		//create the GMT date in the right format
		$gmRightNow = gmdate('D, d M Y H:i:s \G\M\T');
		
		echo "gmRightNow :".$gmRightNow."<br />";
		
		
		
		//express start and end dates in YYYY-mm-dd format
		$theStartDate = $this->startDate;
		$theEndDate = $this->endDate;

		//test intercepting the start and end using time as well as date
		$theStartDate = "2014-01-01T00:00";
		$theEndDate = "2014-01-01T03:00";
		
		echo "hey theStartDate :".$theStartDate."<br />";
		echo "theEndDate :".$theEndDate."<br />";
		
		//create the parts of the URL
		$theProtocol = "https://";
		$theHost = "data.solarnetwork.net";
		//TODO: expand to multiple nodes within each patternSet
		$theNodeId = $this->nodes[0];
		
		
		//construct the full node object
		$theNode = new Node();
		$theNode->id = $theNodeId;
		$theNode->constructFromId();
		
		//get the sourceIds for this patternSet
		//TODO: get a solution that gets the sourceIds for multiple nodes
		$theSourceIds = $theNode->getSources($this->id);
		
		//note if there are no sourceIds found
		//if (strlen(trim($theSourceIds)) < 1)
		if (count($theSourceIds) < 1)
		{
			//log an logentry
			$theError = new SolarError;
			$theError->module = "PatternSet::getDatumFromSolarNet";
			$theError->details = "ERROR: sourceids NOT defined for patternset:".$this->id." count(theSourceIds):".count($theSourceIds);
			$theError->add();
		}
		
		//$theSourceIds = array();
		//$theSourceIds[] = "Phase1";
		
		
		echo "theNode->id :".$theNode->id."<br />";
		echo "theNode->weatherNodeId :".$theNode->weatherNodeId."<br />";
		//echo "theSourceIds :".$theSourceIds."<br />";
		
		for($z = 0; $z < sizeof($theSourceIds);$z++)
		{
			echo "theSourceIds[".$z."]:".$theSourceIds[$z]."<br />";
		}
		
		echo "theNode->id :".$this->id."<br />";
		
		//instantiate object
		$nodeAuthentication = array();
		$theUtility = new SolarUtility;
		
		
		
		if ($datumType == "Weather")
		{
			echo "datumType == Weather <br />";
			$nodeAuthentication = $theUtility->getSolarNetAuthentication($theNode->weatherNodeId);
		}
		else
		{
			echo "datumType not Weather <br />";
			$nodeAuthentication = $theUtility->getSolarNetAuthentication($theNodeId);
		}
		
		if (sizeof($nodeAuthentication) == 2)
		{
			$theToken = $nodeAuthentication[0];
			$theSecret = $nodeAuthentication[1];
		}
		else
		{
			echo "setting NO credentials <br />";
			$noAuthenticationAvailable = true;
		}
		
		//exit;
		
		
		//set first 3-hour chunk dates
		$chunkStartDate = $this->startDate."T00:00";
		
		$tempStartDate = $this->startDate." 00:00";
		$currentDate = strtotime($tempStartDate);
		$futureDate = $currentDate+(60*180);
		$chunkEndDate = date("Y-m-d\TH:i", $futureDate);
		
		echo "tempStartDate :".$tempStartDate."<br />";
		echo "chunkStartDate :".$chunkStartDate."<br />";
		echo "chunkEndDate :".$chunkEndDate."<br />";
		
		echo "strtotime(tempDate) :".strtotime($tempStartDate)."<br />";
		echo "futureDate :".$futureDate."<br />";
		echo "strtotime(this->endDate) :".strtotime($this->endDate)."<br />";
		
		$j = 0;
		
		
		 //start with the last chunkEndDate as chunkStartDate
		 $currentDate = strtotime($this->startDate);
		
		//while the chunkStartDate < theEndDate
		while ($currentDate < strtotime($this->endDate))
		{
		   

			 $futureDate = $currentDate+(60*180);
			 
			 $chunkStartDate = date("Y-m-d\TH:i", $currentDate);
			 $chunkEndDate = date("Y-m-d\TH:i", $futureDate);
			 
			 echo $datumType. "loop ".$j."<br />";
			 echo "chunkStartDate :".$chunkStartDate."<br />";
			 echo "chunkEndDate :".$chunkEndDate."<br />";
			 
			 //advance currentDate to the last futureDate
			 $currentDate = $futureDate;
			 
			 $j++;
			

		
		
		//calculate the next 3 hours segment
		
		//TODO define the URI based on query entity type
		if ($datumType == "Consumption")
		{

			$theUri = "/solarquery/api/v1/pub/datum/list?nodeId=".$theNodeId."&sourceIds=".implode(",", $theSourceIds)."&startDate=".$chunkStartDate."&endDate=".$chunkEndDate."&sorts%5B0%5D.sortKey=created&sorts%5B1%5D.sortKey=source&offset=0&max=570";
			
		
		}
		elseif ($datumType == "Power")
		{
			//using chunk dates
			$theUri = "/solarquery/api/v1/sec/datum/list?endDate=".$chunkEndDate."&nodeId=".$theNodeId."&startDate=".$chunkStartDate."&type=Power";
			
		}
		elseif ($datumType == "Weather")
		{
			//determine number of 20 minute intervals in date range - 72 per day
			$diff = abs(strtotime($theEndDate) - strtotime($theStartDate));

			$years = floor($diff / (365*60*60*24));
			$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
			$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

			echo "years :".$years."<br />";
			echo "months :".$months."<br />";
			echo "days :".$days."<br />";


			
			$theUri = "/solarquery/api/v1/pub/location/datum/list?locationId=301025&sourceIds=NZ%20MetService&offset=0&max=10&startDate=".$chunkStartDate."&endDate=".$chunkEndDate;
			
			
			
		}
	
		
		//start with a blank messageDigest
		$messageDigest = "";
		
		//now create the message contents
		$messageDigest .= "GET\n";
		$messageDigest .= "\n";
		$messageDigest .= "\n";
		$messageDigest .= $gmRightNow."\n";
		$messageDigest .= $theUri;
		
		echo "messageDigest :".$messageDigest."<br />"; 
		
		//write messageDigest to a file
		$fp1 = fopen($theUtility->localAbsolutePath.'emergent/output/messageDigest.txt', 'w');
		fwrite($fp1, $messageDigest);
		fclose($fp1);

		//only if we have authentication
		if ($noAuthenticationAvailable == false)
		{
			#hash the message content
			$hashedContent = base64_encode(hash_hmac('sha1', $messageDigest, $theSecret, true));
		}
		
		//$hashedContent = base64_encode(hash_hmac('sha1', $messageDigest, $theSecret, true));
		
		//$hashedContent = base64_encode(hash_hmac('sha1', $messageDigest, "'".$theSecret."'", true));
		
		//create the header array for the CURL call
		$headerArrayElement1 = "X-SN-Date: ".$gmRightNow;
		
		
		//only if we have authentication
		if ($noAuthenticationAvailable == false)
		{
			//dynamic token Works 
			$headerArrayElement2 = "Authorization: SolarNetworkWS $theToken:".$hashedContent;
		}
		
		//only if we have authentication
		if ($noAuthenticationAvailable == false)
		{
			//$headerArrayElement2 = "Authorization: SolarNetworkWS ".$theToken.":".$hashedContent;
			$headerArray = array($headerArrayElement1,$headerArrayElement2);
		}
		else
		{
			$headerArray = array($headerArrayElement1);
		}
		
		//set the file to output the resulting data		
		$file = $theUtility->localAbsolutePath."emergent/output/file1.csv";
		
		//create the URL to use in the header of the curl call
		$theUrl = $theProtocol.$theHost.$theUri;
		
				//only if we have authentication
		if ($noAuthenticationAvailable == false)
		{
			//create the raw cURL command line
			$theCurlCall = "curl -H '".$headerArray[0]."' -H '".$headerArray[1]."' '".$theUrl."'";
		}
		else
		{
			//create the raw cURL command line
			$theCurlCall = "curl -H '".$headerArray[0]."' '".$theUrl."'";	
		}
		

		
		//write the curl call to a file
		$fp2 = fopen($theUtility->localAbsolutePath.'emergent/output/theCurlCall.txt', 'w');
		fwrite($fp2, $theCurlCall);
		fclose($fp2);
		
			//log an logentry
			//$theError = new SolarError;
			//$theError->module = "PatternSet::getDatumFromSolarNet";
			//$theError->details = "theCurlCall:".$theCurlCall;
			//$theError->add();
		
		
		//download the data to the file
		//echo($this->cURLdownloadToFile($theUrl, $headerArray, $file));
		
		echo "about to call cURLdownload<br />"; 
		
		//$theJSONData = $this->cURLdownload($theUrl, $headerArray);
		$theJSONData = $theUtility->cURLdownload($theUrl, $headerArray);
		
		echo "after call cURLdownload<br />"; 
		
		echo "after cURLdownload theJSONData :".$theJSONData."<br />"; 
		
	
		$resultsArray = json_decode($theJSONData, true);
		
		echo "sizeof(resultsArray):".sizeof($resultsArray)."<br />"; 
		
		echo "after json_decode theJSONData :".var_dump($resultsArray)."<br />"; 
		
		//echo "after json_decode resultsArray[0] :".$resultsArray[0]."<br />"; 
		
		//$innerResults = $resultsArray[1];
		
		foreach ($resultsArray as $name => $value)
		{
			
			echo "name :".$name." value: ".$value."<br />"; 
			
			if ($name == "data")
			{
				//echo "found data array:".var_dump($value)."<br />"; 
				echo "found data array:<br />"; 
				

					
				//TODO branch based on entity type
				if ($datumType == "Consumption")
				{
				
					$sizeOfArray = sizeof($value);
					echo "Consumption sizeOfArray :".$sizeOfArray." chunkStartDate: ".$chunkStartDate." chunkEndDate:".$chunkStartDate."<br />"; 
					
					foreach ($value as $itemName => $itemValue)
					{
						if ($itemName == "results")
						{
							
							$sizeOfResultsArray = sizeof($itemValue);
							
							if ($sizeOfResultsArray < 1)
							{
								//log an logentry
								$theError = new SolarError;
								$theError->module = "PatternSet::getDatumFromSolarNet";
								$theError->details = "ERROR: no results found for patternset:".$this->id;
								$theError->add();
								
							}
							
							echo "sizeOfResultsArray :".$sizeOfResultsArray."<br />"; 
							
							echo "itemValue[0]watts:".$itemValue[0]['watts']."<br />";
							
							//loop through consumptionDatum
							for($i = 0; $i < $sizeOfResultsArray;$i++)
							{
								
								echo "itemValue[i]:".$itemValue[$i]."<br />";
								
								echo "itemValue[0]watts:".$itemValue[0]['watts']."<br />";
								
								
								echo "itemValue[0]watts:".$itemValue[0]['watts']."<br />";
								
								echo "itemValue[0]sourceId:".$itemValue[0]['sourceId']."<br />";
								echo "this->sourceIds:".$this->sourceIds."<br />";
								
								//echo "theSourceIds:".$theSourceIds."<br />";
								echo "sizeof(theSourceIds):".sizeof($theSourceIds)."<br />";
								
								for($z = 0; $z < sizeof($theSourceIds);$z++)
								{
									echo "theSourceIds[".$z."]:".$theSourceIds[$z]."<br />";
								}
								
								//TODO make sure we can use multiple values by looping through an array of sourceIds
								// use in_array()
								//if (trim($itemValue[$i]['sourceId']) == $this->sourceIds)
								if (in_array(trim($itemValue[$i]['sourceId']), $theSourceIds))
								{
									echo "item:".$i."found a correct sourceId:".$itemValue[$i]['sourceId']."<br />";

								
									//instantiate new ConsumptionDatum
									$thisConsumptionDatum = new ConsumptionDatum;
									
									//set values
									$thisConsumptionDatum->volts = 240;
									$thisConsumptionDatum->amps = ($itemValue[$i]['watts']/$thisConsumptionDatum->volts);
									$thisConsumptionDatum->nodeId = $this->nodes[0]; 
									$thisConsumptionDatum->sourceId = $itemValue[$i]['sourceId']; 
									//$thisConsumptionDatum->whenLogged = $value[$i]['localDate'].$value[$i]['localTime']."00";
									$thisConsumptionDatum->whenLogged = substr($itemValue[$i]['created'],0,19);
									
									$thisConsumptionDatum->whenEntered = substr($itemValue[$i]['posted'],0,19);
									
									echo "value[i]['created'] :".$itemValue[$i]['created']."<br />";
									
									echo "value[i]['posted'] :".$itemValue[$i]['posted']."<br />";
									
									echo "value[i]['watts'] :".$itemValue[$i]['watts']."<br />";
									
									echo "thisConsumptionDatum->amps :".$thisConsumptionDatum->amps."<br />";
									
									//echo "value[i]['localDate'] :".$value[$i]['localDate']."<br />";
									
									// "value[i]['localTime'] :".$value[$i]['localTime']."<br />";
									
									echo "thisConsumptionDatum->whenLogged ok :".$thisConsumptionDatum->whenLogged."<br />"; 
									
									//as long as we have a nodeId
									if ($thisConsumptionDatum->nodeId > 0)
									{
										
										//add this ConsumptionDatum to DB
										$thisConsumptionDatum->add();
										
										echo "just added row # :".$i."<br />"; 
										
									}
									else
									{
										echo "invalid data NodeId :".$nodeId."<br />"; 
									}
								
								}
								else //not the right sourceId
								{
									echo "not in theSourceIds:".$itemValue[$i]['sourceId']."<br />";
								}
								
								
							} //end for
					
						}
						
					} //end loop through 4
					

					//echo "value[0]sourceId:".$value[0]['sourceId']."<br />"; 
				
				} //Consumption
				elseif ($datumType == "Power")
				{
				
					$sizeOfArray = sizeof($value);
					echo "sizeOfArray :".$sizeOfArray."<br />"; 
					
					foreach ($value as $itemName => $itemValue)
					{
						if ($itemName == "results")
						{
							
							$sizeOfResultsArray = sizeof($itemValue);
							echo "sizeOfResultsArray :".$sizeOfResultsArray."<br />"; 
							
							echo "itemValue[0]watts:".$itemValue[0]['watts']."<br />";
							
							//loop through consumptionDatum
							for($i = 0; $i < $sizeOfResultsArray;$i++)
							{
								
								echo "itemValue[i]:".$itemValue[$i]."<br />";
								
								echo "itemValue[0]watts:".$itemValue[0]['watts']."<br />";
								
								
								echo "itemValue[i]watts:".$itemValue[$i]['watts']."<br />";
								
								
								
								//instantiate new ConsumptionDatum
								$thisPowerDatum = new PowerDatum;
								
								//set values
								$thisPowerDatum->volts = 240;
								$thisPowerDatum->amps = ($itemValue[$i]['watts']/$thisPowerDatum->volts);
								
								$thisPowerDatum->source = ($itemValue[$i]['sourceId']);
								
								$thisPowerDatum->nodeId = $this->nodes[0]; 
								//$thisConsumptionDatum->whenLogged = $value[$i]['localDate'].$value[$i]['localTime']."00";
								$thisPowerDatum->whenLogged = substr($itemValue[$i]['created'],0,19);
								
								$thisPowerDatum->whenEntered = substr($itemValue[$i]['posted'],0,19);
								
								echo "value[i]['created'] :".$itemValue[$i]['created']."<br />";
								
								echo "value[i]['posted'] :".$itemValue[$i]['posted']."<br />";
								
								//echo "value[i]['localDate'] :".$value[$i]['localDate']."<br />";
								
								// "value[i]['localTime'] :".$value[$i]['localTime']."<br />";
								
								echo "thisPowerDatum->whenLogged ok :".$thisPowerDatum->whenLogged."<br />"; 
								
								if ($thisPowerDatum->nodeId > 0)
								{
									
									//add this ConsumptionDatum to DB
									$thisPowerDatum->add();
									
									echo "just added row # :".$i."<br />"; 
									
								}
								else
								{
									echo "invalid data NodeId :".$nodeId."<br />"; 
								}
								
								
								
							} //end for
					
						}
						
					} //end loop through 4
					

					//echo "value[0]sourceId:".$value[0]['sourceId']."<br />"; 
				
				} //Consumption
				elseif ($datumType == "Weather")
				{
						
					$sizeOfArray = sizeof($value);
					echo "in weather sizeOfArray :".$sizeOfArray."<br />"; 
					
					
							foreach ($value as $name2 => $value2)
							{
								echo "name2 :".$name2." value2: ".$value2."<br />"; 
								
								if ($name2 == "results")
								{
									
									$sizeOfArray = sizeof($value2);
									
										//log an logentry
										$theError = new SolarError;
										$theError->module = "PatternSet::getDatumFromSolarNet";
										$theError->details = "sizeOfArray: ".$sizeOfArray." ";
										$theError->add();
										
									echo "in results sizeOfArray :".$sizeOfArray."<br />"; 
									
									//loop through weatherDatum
									for($i = 0; $i < $sizeOfArray;$i++)
									{
										
										echo "value[0]atm:".$value2[0]['atm']."<br />";
										
										
										//instantiate new ConsumptionDatum
										//$thisWeatherDatum = new Test1Datum;
										$thisWeatherDatum = new WeatherDatum;
						
										//set values
										$thisWeatherDatum->barometricPressure = $value2[$i]['atm'];
										//$thisWeatherDatum->skyConditions = $value2[$i]['skyConditions'];
										$thisWeatherDatum->skyConditions = $value2[$i]['sky'];
										//$thisWeatherDatum->weatherCondition = $value2[$i]['condition'];
										$thisWeatherDatum->weatherCondition = $value2[$i]['sky'];
										//$thisWeatherDatum->temperatureCelsius = $value2[$i]['temperatureCelsius'];
										$thisWeatherDatum->temperatureCelsius = $value2[$i]['temp'];
										$thisWeatherDatum->humidity = $value2[$i]['humidity'];
										$thisWeatherDatum->whenLogged = substr($value2[$i]['created'], 0, -8); ;
										$thisWeatherDatum->nodeId = $this->nodes[0];
										//TODO explicitly set this as actual weather vs. virtual
										
										echo "before weatherDatum add<br />"; 
										
										//log an logentry
										//$theError = new SolarError;
										//$theError->module = "PatternSet:getDatumFromSolarNet";
										//$theError->details = "value2 barometricPressure: ".$value2[$i]['atm']." ";
										//$theError->details .= "value2 1: ".$value2[$i][1]." ";
										//$theError->details .= "value2 2: ".$value2[$i][2]." ";
										//$theError->details .= "value2 3: ".$value2[$i][3]." ";
										//$theError->details .= "value2 4: ".$value2[$i][4]." ";
										//$theError->details .= "value2 5: ".$value2[$i][5]." ";
										//$theError->add();
										
										//double check that the datum is complete
										if ($thisWeatherDatum->humidity	!= "")
										{
											//add this ConsumptionDatum to DB
											$thisWeatherDatum->add();
										}
										
										echo "after weatherDatum add<br />"; 
										
										
										
									}
									
								}
							}
						
						//echo "value[1]barometricPressure:".$value[1]['barometricPressure']."<br />";
					
						//echo "value[i]barometricPressure:".$value[$i]['barometricPressure']."<br />";
						

						
				}
				
				
				//$dataArray = $value;
						
				
			}

			
			// This will loop three times:
			//     $name = inbox
			//     $name = sent
			//     $name = draft
			// ...with $value as the value of that property
    		}
    		
    		
		
    		} //end chunking loop
    		
		//foreach ($dataArray as $name => $value)
		//{
		//	echo "in dataArray name :".$name." value: ".$value."<br />"; 
		//}
    		
		//echo var_dump(json_decode($theJSONData);
		
		/*

			

		
		*/
	}
	
	//uses the input curl arguments to return an text string
	function cURLdownloadOld($url, $headerArray) 
	{ 
		
	echo "in cURLdownload url :".$url."<br />";
	echo "in cURLdownload headerArray :".$headerArray[0]."<br />"; 
	echo "in cURLdownload headerArray :".$headerArray[1]."<br />"; 
	echo "in cURLdownload file :".$file."<br />"; 
	
	 //ob_start();
	 
	  echo "after ob_start <br />"; 
	
	//initialize curl
  	 $ch = curl_init(); 
 
  	 echo "after curl_init :".$ch."<br />"; 
    
  	 	 
  	 	 if( !curl_setopt($ch, CURLOPT_URL, $url) ) 
  	 	 { 
  	 	 	 //fclose($fp); // to match fopen() 
  	 	 	 curl_close($ch); // to match curl_init() 
  	 	 	 return "FAIL: curl_setopt(CURLOPT_URL)<br>"; 
  	 	 } 
  	 	 
  	 	 
  	 	 //try to set the curl file
  	 	 //if( !curl_setopt($ch, CURLOPT_FILE, $fp) ) return "FAIL: curl_setopt(CURLOPT_FILE)"; 
  	 	 
  	 	 //echo "after CURLOPT_FILE <br />"; 
  	 	 
  	 	 //try to set the header output flag
  	 	 if( !curl_setopt($ch, CURLOPT_HEADER, 0) ) return "FAIL: curl_setopt(CURLOPT_HEADER)"; 
  	 	 
  	 	 //try to set the output
  	 	  if( !curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ) return "FAIL: curl_setopt(CURLOPT_RETURNTRANSFER)"; 
  	 	 
  	 	 
  	 	 echo "after CURLOPT_HEADER <br />"; 
  	 	 
  	 	 //try to write the header of the message
  	 	 if( !curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray ) ) return "FAIL: curl_setopt(CURLOPT_HTTPHEADER)"; 
  	 	 
  	 	 echo "after CURLOPT_HTTPHEADER <br />"; 
  	 	 
  	 	 //execute the curl call
  	 	 $curlOutput = curl_exec($ch);
  	 	   	 	  	 	 
   	 	 
  	 	 // echo "after curlOutput :".$curlOutput."<br />"; 
  	 	  
  	 	 //ob_end_clean(); 
  	 	 
  	 	 //close everything up
  	 	 curl_close($ch); 
  	 	 //fclose($fp); 
  	 	 return $curlOutput; 
  	 	 
 	 
 	} 
 	
	//uses the input curl arguments to fill up the file
	function cURLdownloadToFile($url, $headerArray, $file) 
	{ 
		
	echo "in cURLdownloadToFile url :".$url."<br />";
	echo "in cURLdownloadToFile headerArray :".$headerArray[0]."<br />"; 
	echo "in cURLdownloadToFile headerArray :".$headerArray[1]."<br />"; 
	echo "in cURLdownloadToFile file :".$file."<br />"; 
	
	//initialize curl
  	 $ch = curl_init(); 
 
  	 echo "after curl_init :".$ch."<br />"; 
 
  	 //open a file that would contain the results of the curl call
  	 $fp = fopen($file, "w"); 
    
  	 echo "after fopen :".$fp."<br />"; 
    
  	 //as long as we have a valid file handle
  	 if($fp) 
  	 { 
    
  	 	 echo "in fp OK :".$fp."<br />"; 
     
  	 	 
  	 	 if( !curl_setopt($ch, CURLOPT_URL, $url) ) 
  	 	 { 
  	 	 	 fclose($fp); // to match fopen() 
  	 	 	 curl_close($ch); // to match curl_init() 
  	 	 	 return "FAIL: curl_setopt(CURLOPT_URL)<br>"; 
  	 	 } 
  	 	 
  	 	 
  	 	 //try to set the curl file
  	 	 if( !curl_setopt($ch, CURLOPT_FILE, $fp) ) return "FAIL: curl_setopt(CURLOPT_FILE)"; 
  	 	 
  	 	 echo "after CURLOPT_FILE <br />"; 
  	 	 
  	 	 //try to set the header output flag
  	 	 if( !curl_setopt($ch, CURLOPT_HEADER, 0) ) return "FAIL: curl_setopt(CURLOPT_HEADER)"; 
  	 	 
  	 	 echo "after CURLOPT_HEADER <br />"; 
  	 	 
  	 	 //try to write the header of the message
  	 	 if( !curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray ) ) return "FAIL: curl_setopt(CURLOPT_HTTPHEADER)"; 
  	 	 
  	 	 echo "after CURLOPT_HTTPHEADER <br />"; 
  	 	 
  	 	 //execute the curl call
  	 	 if( !curl_exec($ch) ) return "FAIL: curl_exec()<br>"; 
  	 	 
  	 	 echo "after curl_exec <br />"; 
  	 	 
  	 	 //close everything up
  	 	 curl_close($ch); 
  	 	 fclose($fp); 
  	 	 return "SUCCESS: $file [$url]"; 
  	 } 
  	 else
  	 { 
  	 	 //file hanndle didn't work 
  	 	 echo "FAIL fopen :".$fp."<br />"; 
  	 	 return "FAIL: fopen()"; 
 	 }
 	 
 	 
 	} 
 	
    function getMostRecentTrainingFile()
    {

    	echo("in getMostRecentTrainingFile<br>");
    	    
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}
    	
    	//set vars
    	  
    	    
    	    //setup sql 
	    $sql = "select t.training_file_id from training_file t where t.pattern_set_id = ".$this->id." order by t.created_on desc";
	    
	    echo("getMostRecentTrainingFile sql:". $sql. "<br>");
	    
	    //create utility
	    $theUtility = new SolarUtility;
	    $theTrainingFile = new TrainingFile;
	    
	    echo("in getMostRecentTrainingFile about to query:<br>");

	    //execute sql 
	    //$result = mysql_db_query($theUtility->dbName,"$sql") or die ("getMostRecentTrainingFile sql failed");
	    $result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		echo("in getMostRecentTrainingFile after query:<br>");

	    
	    //loop through results
	    //while ($row = mysql_fetch_array ($result))
	    while ($row = $result->fetch_assoc())
	    {
	    	$theTrainingFile->id = $row["training_file_id"];
	    }
	    
	     echo("after query trainingFileID:". $theTrainingFile->id. "<br>");
	    
	    //if there is an id
	    if ($theTrainingFile->id > 0)
	    {
	    	
	    	echo("about to construct training file<br>");
	    	
	    	    //construct object
	    	    $theTrainingFile->constructFromId();
	    
	    	    echo("after construct training file<br>");
	    	    
	    }
	    else
	    {
		//log an logentry
		$theError = new SolarError;
		$theError->module = "PatternSet::getMostRecentTrainingFile";
		$theError->details = "could not construct trainingFile - no trainingFile id for patternset:".$this->id;
		$theError->add();
	    	    
	    }
	    
	    echo("after query theTrainingFile->filename:". $theTrainingFile->filename. "<br>");
	    
	    echo("out getMostRecentTrainingFile<br>");
	    
	    return $theTrainingFile;
	    
    	    
    }
    
    function constructFromId()
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
    	
    	    	//setup sql 
		$sql = "select pattern_set_id, start_date, end_date, pattern_set_name, notes, status_id, analysis_engine_id, pattern_set_type_id from pattern_set where pattern_set_id = ".$this->id;
		
		//echo("patternset construct sql:". $sql. "<br>");
		
		//log an logentry
		//$theError = new SolarError;
		//$theError->module = "PatternSet::constructFromId";
		//$theError->details = "construct sql:".$sql;
		//$theError->add();
			
		//create utility
		$theUtility = new SolarUtility;

		/* execute sql*/
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("PatternSet construct sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		/* loop through results*/
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
			$this->constructFromRow($row);
		}
    
		/* setup sql*/
		$sql = "select node_id, sourceId from patternset_node_match where pattern_set_id = ".$this->id;
		
				//log an logentry
		//$theError = new SolarError;
		//$theError->module = "PatternSet::constructFromId";
		//$theError->details = "node and source sql:".$sql;
		//$theError->add();
    
		// execute sql
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
			
					//echo("row[node_id]:". $row["node_id"]. "<br>");
					//echo("row[sourceId]:". $row["sourceId"]. "<br>");
		
			//set sourceIds once for this PatternSet
			//$this->sourceIds = $row["sourceIds"];
		
		}
		
		$sql = "select training_file_id from training_file where pattern_set_id = ".$this->id;
		
						//log an logentry
		//$theError = new SolarError;
		//$theError->module = "PatternSet::constructFromId";
		//$theError->details = "get training file id sql:".$sql;
		//$theError->add();
		
		//echo("get training file id sql:". $sql. "<br>");
		
		
		//execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node construct sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		//clear it out
		$this->trainingFiles = array();
		

		
		// loop through results
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
			$this->trainingFiles[] = $row["training_file_id"];
			
		//log an logentry
		//$theError = new SolarError;
		//$theError->module = "PatternSet::constructFromId";
		//$theError->details = "trainingFiles[0]:".$this->trainingFiles[0];
		//$theError->add();
			
					//echo("row[training_file_id]:". $row["training_file_id"]. "<br>");
					//echo("row[sourceId]:". $row["sourceId"]. "<br>");
		
			//set sourceIds once for this PatternSet
			//$this->sourceIds = $row["sourceIds"];
		
		}

/*		
		*/
		
    }
    
    
    function constructFromRow($row)
    {
    	$this->id = $row["pattern_set_id"];
    	$this->startDate = $row["start_date"];
    	$this->endDate = $row["end_date"];
    	$this->name = $row["pattern_set_name"];
	$this->notes = $row["notes"];
	$this->statusId = $row["status_id"];
	$this->analysisEngineId = $row["analysis_engine_id"];
	$this->patternSetTypeId = $row["pattern_set_type_id"]; 

    }
    
    function listAll($displayMode, $defaultPatternSetId)
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
    	    
    if ($displayMode == "fullPage"){

			/* table of entities*/
			echo("<table cellpadding='15' cellspacing='15' class='table table-striped' border='0'>\n");

			echo("<tr class='solar4' bgcolor='#ffffff'>");
		
			echo("<td align='center'>\n");
				echo ("ID");
			echo("</td>\n");
			
			echo("<td align='center'>\n");
				echo ("NAME");
			echo("</td>\n");
			
			echo("<td align='center'>\n");
				echo ("START DATE");
			echo("</td>\n");
			
			echo("<td align='center'>\n");
				echo ("END DATE");
			echo("</td>\n");

			echo("<td align='center'>\n");
				echo ("STATUS");
			echo("</td>\n");

			//echo("<td align='center'>\n");
			//	echo ("TYPE");
			//echo("</td>\n");
			
			echo("<td align='center'>\n");
				echo ("ACTION");
			echo("</td>\n");

			echo("</tr>\n");

		}
		elseif ($displayMode == "selectBox")
		{
			echo("<select name='patternSetId' size='1'>\n");
			
			//if -1
			if ($defaultPatternSetId <= 0)
			{
				echo("<option value='0'>Other\n");
			}
			
		}
		

		/* setup the sql*/
		$sql = "select pattern_set_id, start_date, end_date, pattern_set_name, status_id, pattern_set_type_id from pattern_set order by pattern_set_id desc";
		
		//$sql = "select ps.pattern_set_id, ps.start_date, ps.end_date, ps.pattern_set_name, ps.status_id, ps.pattern_set_type_id, tf.training_file_id from pattern_set ps inner join training_file tf on ps.pattern_set_id = tf.pattern_set_id order by tf.start_training desc";

				
		//create utility
		$theUtility = new SolarUtility;

		/* execute the sql*/
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("listAll pattern_set select sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		//setup vars
		$toggle = 0;
		$theColor = "#BFBFBF";

		/* loop through results*/
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
		

						
			//instantiate object
			$thePatternSet = new PatternSet();
			$thePatternSet->constructFromRow($row);
			
			//TODO get the other associated objects if they exist - trainingFile etc

			if ($displayMode == "fullPage"){

			echo("<tr class='solar4'>");

				echo("<td align='center'>\n");
					echo ($thePatternSet->id);
				echo("</td>\n");

				#echo("<td align='center'>\n");
				#	echo ($row["training_file_id"]);
				#echo("</td>\n");
				
				echo("<td align='center'>\n");
					echo ($thePatternSet->name);
				echo("</td>\n");

				echo("<td align='center'>\n");
					echo ($thePatternSet->startDate);
				echo("</td>\n");
				
				echo("<td align='center'>\n");
					echo ($thePatternSet->endDate);
				echo("</td>\n");
				
				echo("<td align='center'>\n");
					//echo ($thePatternSet->statusId);
					//TODO centralise these constants
					if ($thePatternSet->statusId == 0)
					{
						echo("<button type='button' class='btn btn-warning btn-xs'>Waiting</button>\n");	
					}
					elseif ($thePatternSet->statusId == 1)
					{
						echo("<button type='button' class='btn btn-success btn-xs'>Downloading</button>\n");	
					}					
					elseif ($thePatternSet->statusId == 2)
					{
						echo("<button type='button' class='btn btn-info btn-xs'>Data Downloaded</button>\n");	
					}
					elseif ($thePatternSet->statusId == 3)
					{
						echo("<button type='button' class='btn btn-info btn-xs'>NN Weights done</button>\n");	
					}
					elseif ($thePatternSet->statusId == 4)
					{
						echo("<button type='button' class='btn btn-info btn-xs'>Training File ready</button>\n");	
					}
					elseif ($thePatternSet->statusId == 5)
					{
						echo(" <a href='patternSetAction.php?function=viewTraining&patternSetId=");
						echo($thePatternSet->id);
						echo("'><button type='button' class='btn btn-success btn-xs'>Training Now</button>\n");
						echo(" </a>");
					}	
					elseif ($thePatternSet->statusId == 6)
					{
						echo(" <a href='patternSetAction.php?function=viewTraining&patternSetId=");
						echo($thePatternSet->id);
						echo("'><button type='button' class='btn btn-primary btn-xs'>Completed Training</button>\n");
						echo(" </a>");
					}
					elseif ($thePatternSet->statusId == 7)
					{
						echo("<button type='button' class='btn btn-info btn-xs'>Questioning Now</button>\n");	
					}
					elseif ($thePatternSet->statusId == 8)
					{
						echo(" <a href='patternSetAction.php?function=viewTraining&patternSetId=");
						echo($thePatternSet->id);
						echo("'><button type='button' class='btn btn-primary btn-xs'>Completed Training</button>\n");
						echo(" </a>");

						echo(" <a href='patternSetAction.php?function=viewCorrelation&patternSetId=");
						echo($thePatternSet->id);
						echo("'><button type='button' class='btn btn-primary btn-xs'>Correlation</button>\n");
						echo(" </a>");	
					}
				echo("</td>\n");

				//echo("<td align='center'>\n");
				//	echo ($thePatternSet->patternSetTypeId);
				//echo("</td>\n");
				
				echo("<td align='center'>\n");


					echo(" <a href='patternSetAction.php?function=edit&patternSetId=");
					echo($thePatternSet->id);
					echo("'><button type='button' class='btn btn-success btn-xs'>Edit</button></a>\n");


					
					echo(" <a href='patternSetAction.php?function=pullFromSolarNet&patternSetId=");
					echo($thePatternSet->id);
					echo("'><span class'solar4'><button type='button' class='btn btn-success btn-xs'>Download</button></a>\n");
					
					echo(" <a href='patternSetAction.php?function=generateConsumptionNNWeights&patternSetId=");
					echo($thePatternSet->id);
					echo("'><button type='button' class='btn btn-success btn-xs'>Weights</button></a>\n");
					
					//echo(" <a href='patternSetAction.php?function=generateNNWeights&patternSetId=");
					//echo($thePatternSet->id);
					//echo("'><span class'solar4'>Generate NN Weights</a>\n");

					echo(" <a href='patternSetAction.php?function=createConsumptionDataTableFile&patternSetId=");
					echo($thePatternSet->id);
					echo("'><button type='button' class='btn btn-success btn-xs'>Datafile</button></a>\n");
					
					echo(" <a href='patternSetAction.php?function=clear&patternSetId=");
					echo($thePatternSet->id);
					echo("'><button type='button' class='btn btn-warning btn-xs'>Clear</button></a>\n");
					
					echo(" <a href='patternSetAction.php?function=delete&patternSetId=");
					echo($thePatternSet->id);
					echo("'><button type='button' class='btn btn-danger btn-xs'>Delete</button></a>\n");
					echo(" ");
					
					//echo(" <a href='patternSetAction.php?function=createDataTableFile&patternSetId=");
					//echo($thePatternSet->id);
					//echo("'><span class'solar4'>Create DataTableFile</a>\n");
				
				echo("</td>\n");	
			
				echo("</tr>\n");
			}
			elseif ($displayMode == "selectBox"){


				if ($thePatternSet->id == $defaultPatternSetId)
				{
					echo("<option value='".$thePatternSet->id."' selected>".$thePatternSet->name.":".$thePatternSet->id."\n");
				}
				elseif ($thePatternSet->id != $defaultPatternSetId) {
					echo("<option value='".$thePatternSet->id."'>".$thePatternSet->name.":".$thePatternSet->id."\n");
				}
			}
		
		} /*end while*/

		if ($displayMode == "fullPage"){
				echo("</table>\n");
		}
		elseif ($displayMode == "selectBox"){
			echo("</select>\n");
		}

    
    }
    
    //TODO add a parameter to only clear trainingFile Data
    function clear()
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
    	
    	    echo("in patternset clear<br>");
    	    
    	   
    	    	//setup sql
		$clearConsumptionDatumSQL = "delete from consumption_datum where node_id = ".$this->nodes[0];

		echo("clearConsumptionDatumSQL:". $clearConsumptionDatumSQL. "<br>");

				
		//create utility
		$theUtility = new SolarUtility;
		
		//break;
		//execute sql
		//$result = mysql_db_query($theUtility->dbName,"$clearConsumptionDatumSQL") or die ("clearConsumptionDatumSQL failed");
		$result = $this->dbLink->query($clearConsumptionDatumSQL);
		//$result->data_seek(0);
		
		//set up clear weather sql
		$clearWeatherDatumSQL = "DELETE FROM `weather_datum` where node_id = ".$this->nodes[0];
		
		//execute sql
		//$result = mysql_db_query($theUtility->dbName,"$clearWeatherDatumSQL") or die ("clearWeatherDatumSQL failed");
		$result = $this->dbLink->query($clearWeatherDatumSQL);
		$result->data_seek(0);
		
		//get rid of all inputpattern_extensions related to these consumption patterns
		$clearConsumptionInputPatternExtensions = "delete from `inputpattern_extensions` where consumption_input_pattern_id in (select consumption_input_pattern_id from consumption_input_pattern where node_id = ".$this->nodes[0].")";
 
		//execute sql
		//$result = mysql_db_query($theUtility->dbName,"$clearConsumptionInputPatternExtensions") or die ("clearConsumptionInputPatternExtensions failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
		
		//setup sql
		$clearConsumptionInputPatternSQL = "delete from consumption_input_pattern  where pattern_set_id = ". $this->id;

		echo("clearConsumptionInputPatternSQL:". $clearConsumptionInputPatternSQL. "<br>");

		//break;
		//execute sql
		//$result = mysql_db_query($theUtility->dbName,"$clearConsumptionInputPatternSQL") or die ("clearConsumptionInputPatternSQL failed");
		$result = $this->dbLink->query($clearConsumptionInputPatternSQL);
		//$result->data_seek(0);
    	   
		//loop though training files
		
			//delete training file
			
		$theTrainingFile = $this->getMostRecentTrainingFile();
		$theTrainingFile->delete();
		
		//update status back to waiting
		$this->statusId = 0;
		$this->update();
		
		 /*
		*/
		
	  echo("out patternset clear<br>");
	  
	  
	  
	 // exit;
    	    
    }
    function add()
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
		
		
    
		/* setup sql*/
		$sql = "insert into pattern_set (pattern_set_name, start_date, end_date, status_id, analysis_engine_id, pattern_set_type_id, notes) values (\"$this->name\",\"$this->startDate\",\"$this->endDate\",$this->statusId,$this->analysisEngineId,$this->patternSetTypeId,\"$this->notes\")";

		echo("sql:". $sql. "<br>");

			//log an logentry
	$theError = new SolarError;
	$theError->module = "PatternSet::add";
	$theError->details = "sql:".$sql;
	$theError->add();
		
					
		//create utility
		$theUtility = new SolarUtility;

		//break;
		/* execute sql*/
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("insert sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
		
		//set my id
		//$this->id = mysql_insert_id();
		$this->id =  $this->dbLink->insert_id;
		
		//loop through nodes
		foreach ($this->nodes as $thisNode)
		{
			//get the default subscribedSourceId for this node
			$thisNodeConstructed = new Node;
			$thisNodeConstructed->id = $thisNode;
			$thisNodeConstructed->constructFromId();
			
			echo("thisNodeConstructed->subscribedSourceIds:". $thisNodeConstructed->subscribedSourceIds. "<br>");
			
			//make this the match
			$matchSql = "insert into patternset_node_match (pattern_set_id, node_id, sourceId) values ($this->id,$thisNode,\"$thisNodeConstructed->subscribedSourceIds\")";
			
			echo("matchSql:". $matchSql. "<br>");
			
			// execute sql
			//$result = mysql_db_query($theUtility->dbName,"$matchSql") or die ("insert match sql failed");
			$result = $this->dbLink->query($matchSql);
			//$result->data_seek(0);
			//log an logentry
	$theError = new SolarError;
	$theError->module = "PatternSet::clearKilowattHourWeight";
	$theError->details = "clearKilowattHourWeight sql:".$sql;
	$theError->add();
		}
    
    }
    
    function edit()
    {
    
		//construct from id
		$this->constructFromId();
		
		//echo("sourceIds:". $this->sourceIds. "<br>");
		
		//create a comma seperated text value from an array of nodeIds
		$nodeList = implode(",",$this->nodes);
		
		require_once('../classes/tc_calendar.php');
		
		/* generate edit form*/
		echo("<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>\n");

echo("<head>");
	echo("<title>solarquant.Admin</title>");
echo("<link href='../css/solarStyle.css' type='text/css' rel='stylesheet'>");
echo("<link href='../css/bootstrap.min.css' type='text/css' rel='stylesheet'>");	
echo("<link href='../css/bootstrap-theme.min.css' type='text/css' rel='stylesheet'>");
echo("<script src='../js/bootstrap.min.js'></script>");
echo("<script language='javascript' src='../includes/calendar.js'></script>");
echo("</head>");

		echo("<form method=POST action='patternSetAction.php'>\n");
		
		echo("<table class='solar4'>");
		echo("<tr>");
			echo("<td bgcolor='#ffffff'>Name</td>");
			echo("<td>$this->id</td>");
		echo("</tr>");
		echo("<tr>");
			echo("<td bgcolor='#ffffff'>Name</td>");
			echo("<td><input type='text' name='patternSetName' value='$this->name' size='60'></td>");
		echo("</tr>");
				echo("<tr>");
			echo("<td bgcolor='#ffffff'>Start Date</td>");
			echo("<td>");
			
//instantiate class and set properties
$myCalendar = new tc_calendar("startDate", true);
$myCalendar->setIcon("images/iconCalendar.gif");
$myCalendar->setDate(substr($this->startDate,8,2), substr($this->startDate,5,2), substr($this->startDate,0,4));

//output the calendar
$myCalendar->writeScript();	 

			echo("</td>");
		echo("</tr>");
				echo("<tr>");
			echo("<td bgcolor='#ffffff'>End Date</td>");
			echo("<td>");
			
				

//instantiate class and set properties
$myCalendar = new tc_calendar("endDate", true);
$myCalendar->setIcon("images/iconCalendar.gif");
$myCalendar->setDate(substr($this->endDate,8,2), substr($this->endDate,5,2), substr($this->endDate,0,4));

//output the calendar
$myCalendar->writeScript();	 
	


			echo("</td>");
		echo("</tr>");
		echo("<tr>");
			echo("<td bgcolor='#ffffff'>Notes</td>");
			echo("<td><textarea cols='70' rows='5' name='notes'>".$this->notes."</textarea></td>");
		echo("</tr>");
		echo("<tr>");
			echo("<td bgcolor='#ffffff'>Nodes</td>");
			echo("<td>");
				
			        //loop through allSources
			       // for($i = 0; $i < sizeof($this->nodes);$i++)
			       // {
					 
					
			        	//construct the full node object
			        	$theNode = new Node();
			        	//$theNode->id = $this->nodes[$i];
			        	$theNode->id = $this->nodes[0];
			        	$theNode->constructFromId();
			        	
			        	echo($theNode->id."<br>");
			        	echo("<input type='hidden' name='nodeId' value='".$theNode->id."'><br><br>\n");
			        	
			        	echo($theNode->listSources("checkBox", $this->id)."<br>");
			        	
			        	
			        //}
			echo("</td>");
			//echo("<td><input type='text' name='nodeList' value='$nodeList' size='20'></td>");
		echo("</tr>");		
		echo("<tr>");
			echo("<td bgcolor='#ffffff'>SourceIds</td>");
			//echo("<td><input type='text' name='sourceIds' value='$this->sourceIds' size='20'> a comma seperated list of sourceIds </td>");
			
			
		echo("</tr>");	
		echo("<tr>");
			echo("<td bgcolor='#ffffff'>statusId</td>");
			echo("<td><input type='text' name='statusId' value='$this->statusId' size='10'> 0=queued Not Processed, 1=Currently In Process. 2=finished properly 3 = NN weights created
 4 = training file created (trainingFileCreated) 5 = training underway  6 = training completed successfully 7 = questioningFileUnderway 8 = questioning completed successfully</td>");
		echo("</tr>");	

		echo("<tr>");
		echo("<td bgcolor='#ffffff'>AnalysisEngine</td>");
		echo("<td>");
		
		$theEngine = new AnalysisEngine;
		$theEngine->listAll("selectBox",$this->analysisEngineId);

		echo("</td>");
	echo("</tr>");	

		echo("<tr>");
			echo("<td bgcolor='#ffffff'>patternSetTypeId</td>");
			echo("<td><input type='text' name='patternSetTypeId' value='$this->patternSetTypeId' size='10'> 1=Consumption, 2=Generation, 3=Generation+Consumption    </td>");
		echo("</tr>");	
		
		echo("</table>");
		

		echo("<input type='hidden' name='patternSetId' value='".$this->id."'><br><br>\n");
		echo("<input type='hidden' name='function' value='update'><br><br>\n");
		echo("<input type='submit' name='theButton' value='Update'>\n");
		echo("</form>\n");
		echo("</body>\n");
		echo("</html>\n");
    
    
    
    }
    //TODO make the array of nodes an actual object array where nodes have sourceIds
    function update($sourceIds)
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
    	
    	
    	    echo("in patternset update sizeof(sourceIds):". sizeof($sourceIds). "<br>");
    	    
    	// setup sql
		$sql = "update pattern_set 
		set 
		pattern_set_name = '".$this->name."', 
		start_date = '".$this->startDate."', 
		end_date = '".$this->endDate."', 
		notes = '".$this->notes."',
		status_id = ".$this->statusId.",
		pattern_set_type_id = ".$this->patternSetTypeId." 
		where pattern_set_id = ".$this->id;

		
		
		
		//echo("sql:". $sql. "<br>");
		
				
		//create utility
		$theUtility = new SolarUtility;
		
		// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("patternSet update sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
		
		//TODO if we are updating to status unprocessed, update trainingFile as well to unprocessed
		
		
		//only if we are sending in new sourceIds
		if (sizeof($sourceIds) > 0)
		{
				
			//clear out match table
			$sql = "delete from patternset_node_match where pattern_set_id = ".$this->id;
			
			// execute sql
			//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("patternSet delete sql failed");
			$result = $this->dbLink->query($sql);
			//$result->data_seek(0);
			
			//loop through nodes
			//TODO: make this actually deal with multiple nodes
			foreach ($this->nodes as $thisNode)
			{
				
				
				foreach ($sourceIds as $thisSourceId)
				{
					//make this the match
					$matchSql = "insert into patternset_node_match (pattern_set_id, sourceId, node_id) values ($this->id,\"$thisSourceId\",$thisNode)";
					
					echo("matchSql:". $matchSql. "<br>");
					
					// execute sql
					//$result = mysql_db_query($theUtility->dbName,"$matchSql") or die ("insert match sql failed");
					$result = $this->dbLink->query($matchSql);
					//$result->data_seek(0);
					
					//add the sources for this node
				}
				
				
				
			}
		
		}
    }
    
    function clearKilowattHourWeight()
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
    	
    	//create utility
		$theUtility = new SolarUtility;
		
		$sql = "UPDATE consumption_input_pattern SET kilowatt_hours_weight = 0 where pattern_set_id = ".$this->id;
		
	//log an logentry
	$theError = new SolarError;
	$theError->module = "PatternSet::clearKilowattHourWeight";
	$theError->details = "clearKilowattHourWeight sql:".$sql;
	$theError->add();
    	
		// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("patternSet clearKilowattHourWeight sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
  
    }
    function delete()
    {
    	
    	echo("in PatternSet::delete<br>");
    	
       	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		echo("dbLink is not blank<br>");
    	}
    			
		//create utility
		$theUtility = new SolarUtility;
		
		
		
		$sql = "delete from patternset_node_match where pattern_set_id = ".$this->id;
    	
		echo("sql:".$sql."<br>");
		
    	// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("patternSet delete sql failed");
		$result = $this->dbLink->query($sql);
		
		echo("after query<br>");
		
		//$result->data_seek(0);
		
			echo("after delete<br>");
		
	 	$sql = "delete from weather_input_pattern where pattern_set_id = ".$this->id;
    	
    	// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("patternSet delete sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
		
		$sql = "delete from pattern_set where pattern_set_id = ".$this->id;
    	
    	// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("patternSet delete sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
		
		$sql = "delete from training_file where pattern_set_id = ".$this->id;
    	
    	// execute sql
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("patternSet delete sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
    
    }
    // write to the file system with a .DAT file that emergent can pick up
    function writeTrainingFile($fileContents)
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
    	
    	echo("in writeTrainingFile<br>");
    	    
    	  	echo(" fileContents:" . $fileContents."<br><br>");	
		
		//generate unique name based on datetime
		$startTime = strtotime($this->startDate);
		$showStartDate = date("Ymd",$startTime);
		$endTime = strtotime($this->endDate);
		$showEndDate = date("Ymd",$endTime);
		
				//set vars
		$saveTime = strtotime("now");
		$showSaveTime = date("Y-m-d H:i:S",$saveTime);
				
		//name the file
		$fileName = "ConsumptionPattern_".$this->id."_".$showStartDate."_".$showEndDate."_".$saveTime.".dat";
		//$fileName = "S".$thePatternSet->id."_".$saveTime.".dtbl";
		
		echo(" fileName:" . $fileName."<br><br>");	
		
		$fullFileNameWithPath = "/var/www/html/solarquant/emergent/inputFiles/".$fileName;
		//$fullFileNameWithPath = "/tmp/".$fileName;
		
		//$fp = fopen("/tmp/".$fileName, 'w');
		//$fp = fopen("/var/www/html/solarquant/emergent/inputFiles/".$fileName, 'w');
		$fp7 = fopen($fullFileNameWithPath, 'w');
		//$fp = fopen($fileName, 'w');
		
		//write the file
		fwrite($fp7, $fileContents);


		
		echo("wrote fileName:" . $fullFileNameWithPath." to file system <br><br>");
		
		//close the file
		fclose($fp7);  
		
		//2015.02.17 no point in doing this until right before we actually run the emergent job
		//get most recent TrainingFile
		$theLastTrainingFile = new TrainingFile;
		
		//get this node
		$thisNode = new Node();
		$thisNode->id = $this->nodes[0];
		$thisNode->constructFromId();
		
		//get the last processed training file
		$theLastTrainingFile = $thisNode->getMostProcessedRecentTrainingFile();

		//echo("theLastTrainingFile->id:" . $theLastTrainingFile->id." <br>");
		//echo("theLastTrainingFile->inputWeightsFileName:" . $theLastTrainingFile->inputWeightsFileName." <br>");
		//echo("theLastTrainingFile->outputWeightsFileName:" . $theLastTrainingFile->outputWeightsFileName." <br>");
		
		//instantiate object
		$theTrainingFile = new TrainingFile;
		
		
		$theTrainingFile->filename = $fileName;
		$theTrainingFile->createdOn = $showSaveTime;
		$theTrainingFile->title = $fileName;
		$theTrainingFile->patternSetId = $this->id;
		
		
		//set this input weights file as the last output
		$theTrainingFile->inputWeightsFileName = $theLastTrainingFile->outputWeightsFileName;
		
		//TODO make these statuses global static values in once place
		$theTrainingFile->statusId = 0;
		
	//log an logentry
	$theError = new SolarError;
	$theError->module = "PatternSet::writeTrainingFile";
	$theError->details = "for theTrainingFile statusId:".$theTrainingFile->statusId." patternSetTypeId:".$theTrainingFile->patternSetTypeId." and filename:".$theTrainingFile->filename." and inputWeightsFileName:".$theTrainingFile->inputWeightsFileName;
	$theError->add();
		
		//$theTrainingFile->notes = $notes;
		
		
			//run add
			$theTrainingFile->add();
			
			echo("after theTrainingFile add<br>");
		/*	
			
    	    */
    	    
		echo("out writeTrainingFile<br>");
		
		
    	   
    	    
    }
    
//call approprtiate virtual weather type
function getVirtualWeatherFromForecast()
{
	//echo "entering getVirtualWeatherFromForecast1 <br>";
	
	//get Norwegian virtual weather
	if ($this->virtualWeatherTypeId == 0)
	{
		
		//log an logentry
		$theError = new SolarError;
		$theError->module = "PatternSet::getVirtualWeatherFromForecast";
		$theError->details = "about to call getNorwegianWeatherFromForecast for :".$this->nodes[0];
		$theError->add();
	
		//echo "this virtualWeatherTypeId = 0 <br>";
		$this->getNorwegianWeatherFromForecast($this->nodes[0]);
	}
	// get another type of virtual weather
	else
	{
		echo "this virtualWeatherTypeId != 0 <br>";
	}
}

//get Norwegian virtual weather data
function getNorwegianWeatherFromForecast($theNodeId)
{
		echo "in virtual weather function<br>";
		echo "the node id".$theNodeId."<br>";
		//get db connection
		/*if ($link = " "){
			//centralize authentication
			$theUtility = new SolarUtility;
			$link = mysql_connect ($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword) or die ("Could not connect1");
		}
*/
//set vars

//create utility
$theUtility = new SolarUtility;

		//log an logentry
		$theError = new SolarError;
		$theError->module = "PatternSet::getNorwegianWeatherFromForecast";
		$theError->details = "before instantiate node - nodeId:".$theNodeId;
		$theError->add();

//instantiate the node
$theNode = new Node;
$theNode->id = $theNodeId;
$theNode->constructFromId();


//set the file as something unique
//$theFile = "/tmp/".time().".txt";
$theFile = $theUtility->tempWriteablePath.time().".txt";

//first pass array with 3-hour blocks with columns: 0: from 1:to  2:temperature 3:pressure 4: cloudiness 5: icon
$forecastArray = array(
        array(
            (from) => '',
            (to) => '',
            (temperature) => '',
            (humidity) => '',
            (pressure) => '',
            (cloudiness) => '',
            (precipitation) => '',
            (symbol) => ''
        )
);


//debug
//echo "sizeof forecastArray:". sizeof($forecastArray)."<br>";
//echo "zero from".$theArray[0]['from']."</td>";	

//second pass array with columns: 0: time 1:temperature 2:pressure 3: sky
//set the URL for forecast information
$theEndpoint = "http://api.met.no/weatherapi/locationforecast/1.9/?lat=".$theNode->latitude.";lon=".$theNode->longitude;
$ch = curl_init($theEndpoint);
//$ch = curl_init("http://api.met.no/weatherapi/locationforecast/1.9/?lat=-36.8;lon=174");
//open the file
$fp = fopen($theFile, "w");
//set curl settings
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
//execute curl
curl_exec($ch);
//close curl and file
curl_close($ch);
fclose($fp);

		//log an logentry
		$theError = new SolarError;
		$theError->module = "PatternSet::getNorwegianWeatherFromForecast";
		$theError->details = "before checking foor the file exists";
		$theError->add();
		
//as long as the file is there
if (file_exists($theFile)) {	
	
	
			//log an logentry
		$theError = new SolarError;
		$theError->module = "PatternSet::getNorwegianWeatherFromForecast";
		$theError->details = "inside file exists";
		$theError->add();
		
		
    //create xml object
    $xml = simplexml_load_file($theFile);
   //loop through time elements
   foreach ($xml->product->time as $theTime)
   {
   	   
   	   			//log an logentry
		$theError = new SolarError;
		$theError->module = "PatternSet::getNorwegianWeatherFromForecast";
		$theError->details = "found a time";
		$theError->add();
		
   	   //debug
   	   //echo "<br><br>found a time:".var_dump($theTime)."<br><br>";
   	   
   	   //debug
   	   //echo "searching from for :".$theStartDatetime."<br><br>";
   	    
   	   // at this point we already have something in our cache from parsing a TIME element
   	   // and it's time to slot those values into the right 
   	   
   	   //search for theStartDatetime
   	   $foundElement = array_search($theStartDatetime, array_column($forecastArray, 'from'));
   	   //debug
   	   //echo "foundElement:".$foundElement." for theStartDatetime:".$theStartDatetime."<br><br>";
   	   
   	   //if we found something
   	   if (is_int($foundElement))
   	   {
   	   	//debug
/*   	   	
   	   	 echo "about to update element:".$foundElement."<br><br>";
   	   	 echo "theStartDatetime:".$theStartDatetime."<br>";
   	   	 echo "theEndDatetime:".$theEndDatetime."<br>";
   	   	 echo "theTemperatureValue:".$theHumidityValue."<br>";
   	   	 echo "theHumidityValue:".$theTemperatureValue."<br>";
   	   	 echo "thePressureValue:".$thePressureValue."<br>";
   	   	 echo "theCloudinessValue:".$theCloudinessValue."<br>";
   	   	 echo "thePrecipitationValue:".$thePrecipitationValue."<br>";
   	   	 echo "theSymbolValue:".$theSymbolValue."<br><br><br>";
   	   	 
   	   	 
*/   	   	
   	   
   	   			//log an logentry
		$theError = new SolarError;
		$theError->module = "PatternSet::getNorwegianWeatherFromForecast";
		$theError->details = "found a temp".$forecastArray[$foundElement]['temperature'];
		$theError->add();
		
   	   	//update that found array element with values in cache
   	   	$forecastArray[$foundElement]['temperature'] = $theTemperatureValue;
   	   	$forecastArray[$foundElement]['humidity'] = $theHumidityValue;
   	   	$forecastArray[$foundElement]['pressure'] = $thePressureValue;
   	   	$forecastArray[$foundElement]['cloudiness'] = $theCloudinessValue;
   	   	$forecastArray[$foundElement]['precipitation'] = $thePrecipitationValue;
   	   	$forecastArray[$foundElement]['symbol'] = $theSymbolValue;   	   	   	   	   	   	
   	   }
   	   
   	   else  //else we haven't got this row yet
   	   {
   	   	   //debug
/*   	   	
   	   	   echo "not an int<br><br>";
   	   	   echo "theStartDatetime:".$theStartDatetime."<br>";
   	   	   echo "theEndDatetime:".$theEndDatetime."<br>";
   	   	   echo "theTemperatureValue:".$theTemperatureValue."<br>";
   	   	   echo "thePressureValue:".$thePressureValue."<br>";
   	   	   echo "theCloudinessValue:".$theCloudinessValue."<br>";
   	   	   echo "thePrecipitationValue:".$thePrecipitationValue."<br>";
   	   	   echo "theSymbolValue:".$theSymbolValue."<br>";
  */ 	   	
   	   	   
   	   	    //create new weatherBlock
		    $newWeatherBlock = array(
		    (from) => $theStartDatetime,
		    (to) => $theEndDatetime,
		    (temperature) => $theTemperatureValue,
		    (humidity) => $theHumidityValue,
		    (pressure) => $thePressureValue,
		    (cloudiness) => $theCloudinessValue,
		    (precipitation) => $thePrecipitationValue,
		    (symbol) => $theSymbolValue
		    );
            
		    //debug
		    //echo "newWeatherBlock:".var_dump($newWeatherBlock)."<br>";
            //echo "sizeof newWeatherBlock:".sizeof($newWeatherBlock)."<br>";
            
   	   	    //add it to the end of the array
		    $forecastArray[] = $newWeatherBlock;
		    //sort the array by from
		    $theUtility = new SolarUtility;
		    $sortedForecastArray = $theUtility->array_sort($forecastArray, 'from', SORT_ASC);
		    //debug
		    //var_dump($sortedForecastArray);
		    //echo "sizeof forecastArray:". sizeof($forecastArray)."<br>";

   	   }  //not found a row
   	   	
   	   //debug print array
   	   //echo "<br><br>forecastArray:". var_dump($forecastArray)."<br>";
   	   	
   	   //reset all the values to blank
   	   
   	   //grab the FROM and TO of this forecast
   	   foreach ($theTime->attributes() as $key => $value)
   	   {
   	   	   //debug
   	   	   //echo "<br><br>found attribute:".$key." is ".$value."<br>";
   	   	   
   	   	   //if FROM
   	   	   if ($key == "from")
   	   	   {
   	   	   	   //set the forecast starttime
   	   	   	   $theStartDatetime = strval($value);
   	   	   }
   	   	   
   	   	   //if TO
   	   	   if ($key == "to")
   	   	   {
   	   	   	   //set the forecast starttime
   	   	   	   $theEndDatetime = strval($value);
   	   	   }   	   	   
   	   }	//end for
   	   
   	   //loop through details of this forecast set
   	   foreach ($theTime->location as $theLocation)
   	   {   
   	   	   //debug
   	   	   //echo "<br><br>found theLocation:".$theLocation."<br>";
   	   	   
   	   	   //grab temperature
   	   	   foreach ($theLocation->temperature as $theTemperature)
   	   	   {
   	   	   	   //loop through attributes of temperature element
   	   	   	   foreach ($theTemperature->attributes() as $key => $value)
   	   	   	   {
   	   	   	   	   //debug
   	   	   	   	   //echo "<br><br>found theTemperature attribute:".$key." is ".$value."<br>";
   	   	   	   	   
				   //if VALUE
				   if ($key == "value")
				   {
					   //set the forecast starttime
					   $theTemperatureValue = strval($value);
					   
					   //debug
					   //echo "theTemperatureValue:".$theTemperatureValue."<br>";					   
				   }    	   	   	    	    	
   	   	   	   }   	   	   	       	   	   	   
   	   	    }

   	   	   //grab humidity
   	   	   foreach ($theLocation->humidity as $theHumidity)
   	   	   {
   	   	   	    //loop through attributes of humidity element
   	   	   	    foreach ($theHumidity->attributes() as $key => $value)
   	   	   	    {
   	   	   	    	    //debug
   	   	   	    	    //echo "<br><br>found theHumidity attribute:".$key." is ".$value."<br>";
   	   	   	    	    
   	   	   	    	   //if VALUE
				   if ($key == "value")
				   {
					   //set the forecast starttime
					   $theHumidityValue = strval($value);
					   
					   //debug
					   //echo "theHumidityValue:".$theHumidityValue."<br>";
				   } 
   	   	   	    } 	   	   	       	   	   	   
   	   	    }
   	   	   
   	   	   //grab pressure
   	   	   foreach ($theLocation->pressure as $thePressure)
   	   	   {
   	   	   	    //loop through attributes of pressure element
   	   	   	    foreach ($thePressure->attributes() as $key => $value)
   	   	   	    {
   	   	   
   	   	   	    	    //echo "<br><br>found thePressure attribute:".$key." is ".$value."<br>";

   	   	   	    	   //if VALUE
				   if ($key == "value")
				   {
					   //set the forecast starttime
					   $thePressureValue = strval($value);
					   
					   //debug
					   //echo "thePressureValue:".$thePressureValue."<br>"; 
				   } 
   	   	   	    }
   	   	    }
   	   	    
   	   	    //grab cloudiness
   	   	    foreach ($theLocation->cloudiness as $theCloudiness)
   	   	    {
   	   	   	    //loop through attributes of pressure element
   	   	   	    foreach ($theCloudiness->attributes() as $key => $value)
   	   	   	    {
   	   	   
   	   	   	    	   //debug 	
   	   	   	    	   // echo "<br><br>found theCloudiness attribute:".$key." is ".$value."<br>";
   	   	   	    	    	
   	   	   	    	   //if VALUE
				   if ($key == "percent")
				   {
					   //set the forecast starttime
					   $theCloudinessValue = strval($value);
					   
					   //debug
					   //echo "theCloudinessValue:".$theCloudinessValue."<br>";
				   } 	   	   	    	    	
   	   	   	    }
   	   	    }
   	   	    
   	   	    //grab precipitation
   	   	    foreach ($theLocation->precipitation as $thePrecipitation)
   	   	    {
   	   	   	   
   	   	   	    foreach ($thePrecipitation->attributes() as $key => $value)
   	   	   	    {
   	   	   
   	   	   	    	   //debug 	
   	   	   	    	   // echo "<br><br>found thePrecipitation attribute:".$key." is ".$value."<br>";
   	   	   	    	    	
   	   	   	    	   //if VALUE
				   if ($key == "value")
				   {
					   //set the forecast starttime
					   $thePrecipitationValue = strval($value);
					   
					   //debug
					   //echo "thePrecipitationValue:".$thePrecipitationValue."<br>";					   
					   
				   }  	   	    	    	
   	   	   	    } 	   
   	   	    }
   	   	    
   	   	     //grab symbol
   	   	    foreach ($theLocation->symbol as $theSymbol)
   	   	    {
   	   	   	   
   	   	   	    foreach ($theSymbol->attributes() as $key => $value)
   	   	   	    {
   	   	   
   	   	   	    	   //debug 	
   	   	   	    	    //echo "<br><br>found theSymbol attribute:".$key." is ".$value."<br>";
   	   	   	    	    	
   	   	   	    	   //if VALUE
				   if ($key == "id")
				   {
					   //set the forecast starttime
					   $theSymbolValue = strval($value);
					   
					   //debug
					   //echo "theSymbolValue:".$theSymbolValue."<br>";  
				   } 	    	
   	   	   	    }
   	   	    }
   	   	    
   	   	    //TODO map Symbol to sky conditions
   	   	    
   	   	    //Create first array of 3-hour values alonged with weather datum
   	   	    
   	   	    //create second array with 30 min data
   	   }
   }
    
//show array
//$theArrayHtml = $theUtility->showArray($sortedForecastArray);
//echo "theArrayHtml:". $theArrayHtml."<br>";
		    
 //we now have the forecastArray and we need to create the futureWeatherDatumArray
 // by filling in the segments between
 
 //set vars
 $futureWeatherDatumArray = array();
 //$forecastIndex = 0;
 
 
 //loop through forecastArray
foreach ($sortedForecastArray as $k => $forecastSet)
{
	//as long we we're not on the first or the last one
	if (($k > 0) & ($k < (count($sortedForecastArray) - 1)))
	{	
		echo "k:".$k."<br>";
		//echo "temperature:".$forecastSet['temperature']."<br>";
		
		//get this current time and next time
		$currentDate = strtotime(substr($sortedForecastArray[$k]['from'],0,10));//." ".substr($sortedForecastArray[$k]['from'],11,8);
		$nextDate = strtotime(substr($sortedForecastArray[$k+1]['from'],0,10));//." ".substr($sortedForecastArray[$k + 1]['from'],11,8);
		$dateDifference = abs($currentDate - $nextDate);
		$currentTime = strtotime(substr($sortedForecastArray[$k]['from'],11,8));
		$nextTime = strtotime(substr($sortedForecastArray[$k + 1]['from'],11,8));
		$timeDifference = abs($currentTime - $nextTime)/1800; //converting seconds to half hours 60*30
		$total = strtotime(substr($sortedForecastArray[$k]['from'],0,10)." ".substr($sortedForecastArray[$k]['from'],11,8));
		$nextTotal = strtotime(substr($sortedForecastArray[$k+1]['from'],0,10)." ".substr($sortedForecastArray[$k + 1]['from'],11,8));
		$totalDifference = abs($total - $nextTotal)/1800;
		$halfHourDifference = $totalDifference;
		//$editedTime = substr($forecastSet['from'],0,10)." ".substr($forecastSet['from'],11,8);
		//$nextTime = strtotime($editedTime." + 30 minute");
		//echo " ".$currentTime."<br>";
		//echo " ".$nextTime."<br>";
		echo " ".$dateDifference."<br>";
		echo " ".$timeDifference."<br>";
		echo " ".$totalDifference."<br>";
		echo " ".$halfHourDifference."<br>";
		//echo " ".$toTimeText."<br>";
		//get this temperature and next temperature
		//divide difference by 6 to get temperatureStep
		$thisTemperature = $forecastSet['temperature'];
		$nextTemperature = $sortedForecastArray[$k+1]['temperature'];
		$temperatureStep = ($nextTemperature - $thisTemperature)/$halfHourDifference;

		//get this humidity and next humidity
		//divide difference by 6 to get humidityStep
		$thisHumidity = $forecastSet['humidity'];
		$nextHumidity = $sortedForecastArray[$k+1]['humidity'];
		$humidityStep = ($nextHumidity - $thisHumidity)/$halfHourDifference;
		
		//get this pressure and next pressure
		//divide difference by 6 to get pressureStep
		$thisPressure = $forecastSet['pressure'];
		$nextPressure = $sortedForecastArray[$k+1]['pressure'];
		$pressureStep = ($nextPressure - $thisPressure)/$halfHourDifference;
		
		//get this cloudiness and next cloudiness
		//divide difference by 6 to get cloudinessStep
		$thisCloudiness = $forecastSet['cloudiness'];
		$nextCloudiness = $sortedForecastArray[$k+1]['cloudiness'];
		$cloudinessStep = ($nextCloudiness - $thisCloudiness)/$halfHourDifference;

		//get this precipitation and next precipitation
		//divide difference by 6 to get precipitationStep
		$thisPrecipitation = $forecastSet['precipitation'];
		$nextPrecipitation = $sortedForecastArray[$k+1]['precipitation'];
		$precipitationStep = ($nextPrecipitation - $thisPrecipitation)/$halfHourDifference;
		
		$thisSymbol = $forecastSet['symbol'];
		$nextSymbol = $sortedForecastArray[$k+1]['symbol'];
		
		echo "thisTemperature:".$thisTemperature."<br>";
		echo "nextTemperature:".$nextTemperature."<br>";
		echo "temperatureStep:".$temperatureStep."<br>";
		
		echo "thisHumidity:".$thisHumidity."<br>";
		echo "nextHumidity:".$nextHumidity."<br>";
		echo "humidityStep:".$humidityStep."<br>";
		
		echo "thisPressure:".$thisPressure."<br>";
		echo "nextPressure:".$nextPressure."<br>";
		echo "pressureStep:".$pressureStep."<br>";

		echo "thisCloudiness:".$thisCloudiness."<br>";
		echo "nextCloudiness:".$nextCloudiness."<br>";
		echo "cloudinessStep:".$cloudinessStep."<br>";
		
		echo "thisPrecipitation:".$thisPrecipitation."<br>";
		echo "nextPrecipitation:".$nextPrecipitation."<br>";
		echo "precipitationStep:".$precipitationStep."<br>";
		
		echo "thisSymbol:".$thisSymbol."<br>";
		echo "nextSymbol:".$nextSymbol."<br>";
				
		//start with this timestamp
		$startingTimeStampText = substr($sortedForecastArray[$k]['from'],0,10)." ".substr($sortedForecastArray[$k]['from'],11,8);
		
		echo "startingTimeStampText:".$startingTimeStampText."<br>";
		
		//edit the raw data of this forecast set before adding it
		
		$editedForecastSetFrom = substr($forecastSet['from'],0,10)." ".substr($forecastSet['from'],11,8);
		
		$newToTimeStamp = strtotime($editedForecastSetFrom." + 30 minute");
		$newToTimeStampText = date('Y-m-d H:i:s', $newToTimeStamp);
			
		//$editedForecastSetTo = substr($forecastSet['to'],0,10)." ".substr($forecastSet['to'],11,8);
		
		$forecastSet['from'] = $editedForecastSetFrom;
		$forecastSet['to'] = $newToTimeStampText;
		
		//$newFromTimestamp = strtotime('2011-11-17 05:05 + 16 minute');
		
		//search array for any previous matches
		//if there are no previous matches then add base array
		//echo "search_base array........<br>";
		if ($theUtility->search_array($futureWeatherDatumArray, (from), $editedForecastSetFrom) == false)
		{
			echo "adding ".$editedForecastSetFrom." to the array. <br>";
			$futureWeatherDatumArray[] = $forecastSet; 
		}
				
		$startingTimeStampText = $newToTimeStampText;
					
		//loop for 5 steps
		for ($x = 0; $x < $halfHourDifference - 1; $x++)
		{
			echo "x:".$x."<br>";
			
			//create new from time stamp
			$newToTimeStamp = strtotime($startingTimeStampText." + 30 minute");
			$newToTimeStampText = date('Y-m-d H:i:s', $newToTimeStamp);
						
			echo "newToTimeStampText:".$newToTimeStampText."<br>";
			
			//create newTemperature, 
			$newTemperature = $thisTemperature + $temperatureStep;
			$newPressure = $thisPressure + $pressureStep;
			$newHumidity = $thisHumidity + $humidityStep;
			$newCloudiness = $thisCloudiness + $cloudinessStep;
			$newPrecipitation = $thisPrecipitation + $precipitationStep;
			
			echo "newTemperature:".$newTemperature."<br>";
			echo "newPressure:".$newPressure."<br>";
			echo "newHumidity:".$newHumidity."<br>";
			echo "newCloudiness:".$newCloudiness."<br>";
			echo "newPrecipitation:".$newPrecipitation."<br>";
			
			//create new weatherRow Array
			    $weatherRow = array(
			    (from) => $startingTimeStampText,
			    (to) => $newToTimeStampText,
			    (temperature) => $newTemperature,
			    (humidity) => $newHumidity,
			    (pressure) => $newPressure,
			    (cloudiness) => $newCloudiness,
			    (precipitation) => $newPrecipitation,
			    (symbol) => $thisSymbol
			    );
					
			//search array for previous time match
			//if there is a match, say found match: else add to the array
			if ($theUtility->search_array($futureWeatherDatumArray, (from), $startingTimeStampText) == false)
			{
				//echo "adding ".$startingTimeStampText." to the array. <br>";
				$futureWeatherDatumArray[] = $weatherRow;
			}
			
			//debug: View array
			//$testArray = showArray($futureWeatherDatumArray);
			//echo "here is is:".$testArray."<br>";
						
			//update starting values for next loop
			$startingTimeStampText = $newToTimeStampText;
			$thisTemperature = $newTemperature;
			$thisPressure = $newPressure;
			$thisHumidity = $newHumidity;
			$thisCloudiness = $newCloudiness;
			
			
			
		} //end loop through interpolated values
		
//$dateTime1 = new DateTime('2016-06-25T00:00:00Z');
//$dateTime1->modify('+5 minutes');
//echo date('Y-m-d H:i:s', $dateTime1);
	
	} //not on the last one

} 
 
//show array
//$theArrayHtml = $theUtility->showArray($futureWeatherDatumArray);
//echo "futureWeatherDatumArray:<br>". $theArrayHtml."<br>";
   
echo "about to create weatherdatum:<br>";

//loop through the future futureWeatherDatumArray
foreach ($futureWeatherDatumArray as $m => $forecastSet)
{

	echo "m:".$m."<br>";
	
	//create a new weatherDatum
	$theWeatherDatum = new WeatherDatum;
	
	$theWeatherDatum->nodeId = $theNodeId;
	echo "nodeId:".$theWeatherDatum->nodeId."<br>";
	
	//populate it with this weatherRow
	$theWeatherDatum = $theUtility->populateWeatherDatum($theWeatherDatum, $forecastSet);
	echo "nodeId:".$theWeatherDatum->nodeId."<br>";
	
	//add it
	$theWeatherDatum->add();	
}

} 
else  //file does not exist
{
    exit('Failed to open test.xml.');
}
echo "out virtual weather function<br>";
}


    
}//end class

?>