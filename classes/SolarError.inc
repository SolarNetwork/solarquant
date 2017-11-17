<?php

//TODO rename this class SolarLogEntry and add a type for error, warning, standard logentry

//imports
require_once "/var/www/html/solarquant/classes/SolarUtility.php";


//echo("in node.php after imports 2<br>");

class SolarError {

    var $id;
    var $whenLogged;
    var $module;
    var $details;
    var $dbLink;
    
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
    	
    	/* setup sql*/
		$sql = "select solar_error_id, when_logged, module, details from solar_error where solar_error_id = ".$this->id;
		
		//echo("sql:". $sql. "<br>");
		//create utility
		$theUtility = new SolarUtility;

		/* execute sql*/
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("node constructFromId sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		/* loop through results*/
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
			$this->constructFromRow($row);
		}
    
    
    }
    
    function constructFromRow($row)
    {
    	$this->id = $row["solar_error_id"];
    	$this->whenLogged = $row["when_logged"];
    	$this->module = $row["module"];
	$this->details = $row["details"];
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
    	
    	    //set whenLogged to now
    	    $nowDatetime = new DateTime();
    	    $this->whenLogged = $nowDatetime->format('Y-m-d H:i:s');
    
		/* setup sql*/
		$sql = "insert into solar_error (when_logged, module, details) values (\"$this->whenLogged\",\"$this->module\",\"$this->details\")";

		echo("solarError sql:". $sql. "<br>");
		
				//create utility
		//$theUtility = new SolarUtility;
		
		echo("after solarError new solarUtility<br>");
		

		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("insert add SolarError sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
		
		echo("after solarError query exec<br>");
		
		//$this->id = mysql_insert_id();
		//$this->id = $this->dbLink->insert_id;
		
    }
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
    	    
    
		/* setup sql*/
		$sql = "delete from solar_error";

		//echo("sql:". $sql. "<br>");
		
				//create utility
		$theUtility = new SolarUtility;

		//break;
		/* execute sql*/
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("delete sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
		
		
    }  
    function listModules($theModule)
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
    	
    	
echo("<select name='module' size='1'>\n");			

echo("<option value='All'>All\n");

    	    	/* setup the sql*/
		$sql = "SELECT DISTINCT module FROM solar_error order by module asc";
		
		//create utility
		$theUtility = new SolarUtility;

		/* execute the sql*/
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("listAll pattern_set select sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		/* loop through results*/
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
			
			$module = $row["module"];
			
			if ($module == $theModule)
			{
				echo("<option value='".$module."' selected>".$module."\n");
				
			}
			else
			{
				echo("<option value='".$module."'>".$module."\n");
			}
			
			
		}


echo("</select>\n");

			
  
    }
    function listAll($displayMode, $theModule)
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
    	
    	    if ($displayMode == "fullPage")
    	    {
    	    	    
    	    	    /* table of entities*/
    	    	    echo("<table cellpadding='15' cellspacing='15' class='table table-striped' border='0'>\n");
    	    	    
    	    	    echo("<tr class='solar4' bgcolor='#ffffff'>");
    	    	    
    	    	    echo("<td align='center'>\n");
    	    	    echo ("ID");
    	    	    echo("</td>\n");
    	    	    
    	    	    echo("<td  align='center'>\n");
    	    	    echo ("WHEN LOGGED");
    	    	    echo("</td>\n");
    	    	    
    	    	    echo("<td  align='center'>\n");
    	    	    echo ("MODULE");
    	    	    echo("</td>\n");
    	    	    
    	    	    echo("<td  align='center'>\n");
    	    	    echo ("DETAILS");
    	    	    echo("</td>\n");
    	    	    
    	    	    echo("</tr>\n");
    	    } //if fullpage

    	    	    
    	    	/* setup the sql*/
		$sql = "select solar_error_id, when_logged, module, details from solar_error";
		
		//if we specified a module
		if ($theModule != "All")
		{
			$sql .= " where module = '". trim($theModule)."' ";
	
		}
		
		// order descending or newest shown first
		$sql .= " order by solar_error_id desc";

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
			$theError = new SolarError();
			$theError->constructFromRow($row);

			if ($displayMode == "fullPage"){

			echo("<tr>");

				echo("<td>\n");
					echo ($theError->id);
				echo("</td>\n");
			
				echo("<td>\n");
					echo ($theError->whenLogged);
				echo("</td>\n");

				echo("<td>\n");
					echo ($theError->module);
				echo("</td>\n");

				echo("<td>\n");
					echo ($theError->details);
				echo("</td>\n");
				
			echo("</tr>\n");

						
		} //displaymode


    	    	    
    	    } //end while
    	    
    	    		if ($displayMode == "fullPage"){
				echo("</table>\n");
		}
		
    }
    
}

?>
