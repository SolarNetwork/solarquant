<?php

require_once "/var/www/html/solarquant/classes/SolarError.php";
//require_once "/var/www/html/solarquant/classes/node.php";
require_once "/var/www/html/solarquant/classes/SolarUtility.php";

class AnalysisEngine {

    var $id;
    var $name;


    function connectToDB()
    {
    	$theUtility = new SolarUtility;
    	$this->dbLink = new mysqli($theUtility->dbHost,$theUtility->dbUser,$theUtility->dbPassword,$theUtility->dbName);
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
    
    	    	// setup sql
		$sql = "select analysis_engine_id, analysis_engine from analysis_engine where analysis_engine_id = ".$this->id;
		
		//echo("node constructFromId sql:". $sql. "<br>");
		
		//create utility
		$theUtility = new SolarUtility;

		// execute sql 
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node constructFromId sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		// loop through results 
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
			$this->constructFromRow($row);
		}
    
    
    }

   function constructFromRow($row)
    {
    	$this->id = $row["analysis_engine_id"];
    	$this->name = $row["analysis_engine"];
    }

    function listAll($displayMode, $defaultEngineId)
    {
       	
    	//debug
    	//echo("in Node:ListAll before connectToDB<br>");
    	//echo("before dbLink:". $this->dbLink. "<br>");
    	
    	//try to get a database connection if there isn't one already open
    	if ($this->dbLink == "")
    	{
    		$this->connectToDB();
    	}
    	else
    	{
    		//echo("dbLink is not blank<br>");
    	}


		if ($displayMode == "selectBox")
		{
			echo("<select name='analysisEngineId' size='1'>\n");
			
			
		}

		
        //set sql
		$sql = "select analysis_engine_id, analysis_engine from analysis_engine";
		
	
		//create utility
		//$theUtility = new SolarUtility;
		
		//execute the sql 
		$result = $this->dbLink->query($sql);
		
		
		$result->data_seek(0);
		
	
		
		//setup vars
		$toggle = 0;
		$theColor = "#BFBFBF";

		//loop through results
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
		
						
						
			//instantiate object
			$theEngine = new AnalysisEngine;
			$theEngine->constructFromRow($row);

			if ($displayMode == "selectBox"){


				if ($theEngine->id == $defaultEngineId)
				{
					echo("<option value='".$theEngine->id."' selected>".$theEngine->name."\n");
				}
				elseif ($theEngine->id != $defaultEngineId) {
					echo("<option value='".$theEngine->id."'>".$theEngine->name."\n");
				}
			}
			
			
		
		} //end while 


		if ($displayMode == "selectBox"){
			echo("</select>\n");
		}

    
    }

}




?>