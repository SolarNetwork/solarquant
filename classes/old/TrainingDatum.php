<?php

//imports
require_once "/var/www/html/solarquant/classes/SolarUtility.php";


class TrainingDatum {

    var $id;
    var $trainingFileId;
    var $whenLogged;
    var $batch;
    var $epoch;
    var $sse;
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
		$sql = "select training_datum_id, training_file_id, batch, epoch, when_logged, sse from training_datum where training_datum_id = ".$this->id;
		
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
    	$this->id = $row["training_datum_id"];
    	$this->trainingFileId = $row["training_file_id"];
    	$this->whenLogged = $row["when_logged"];
    	$this->batch = $row["batch"];
	$this->epoch = $row["epoch"];
	$this->sse = $row["sse"];
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
		$sql = "insert into training_datum (training_file_id, batch, epoch, when_logged, sse) values ($this->trainingFileId, $this->batch, $this->epoch,\"$this->whenLogged\",$this->sse)";

		echo("sql:". $sql. "<br>");
		
				//create utility
		$theUtility = new SolarUtility;

		//break;
		/* execute sql*/
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("insert add trainingDatum sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
		
		//$this->id = mysql_insert_id();
		$this->id =  $this->dbLink->insert_id;
		
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
		$sql = "delete from training_datum";

		//echo("sql:". $sql. "<br>");
		
				//create utility
		$theUtility = new SolarUtility;

		//break;
		/* execute sql*/
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("delete sql failed");
		$result = $this->dbLink->query($sql);
		//$result->data_seek(0);
		
		
		
    }    
    function listAll($displayMode)
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
    	    	    echo ("TRAINING_FILE_ID");
    	    	    echo("</td>\n");
    	    	    
    	    	    echo("<td  align='center'>\n");
    	    	    echo ("WHEN LOGGED");
    	    	    echo("</td>\n");
    	    	    
    	    	    echo("<td  align='center'>\n");
    	    	    echo ("BATCH");
    	    	    echo("</td>\n");
    	    	    
    	    	    echo("<td  align='center'>\n");
    	    	    echo ("EPOCH");
    	    	    echo("</td>\n");

    	    	    echo("<td  align='center'>\n");
    	    	    echo ("SSE");
    	    	    echo("</td>\n");
    	    	    
    	    	    echo("</tr>\n");
    	    } //if fullpage
    	    	    
    	    	//setup the sql 
		$sql = "training_datum_id, training_file_id, batch, epoch, when_logged, sse from training_datum order by when_logged desc";
		//TODO use a method of only showing some of the rows
		//SELECT solar_error_id, MOD(solar_error_id,3) FROM `solar_error` where MOD(solar_error_id,3) = 2

		//create utility
		$theUtility = new SolarUtility;

		//execute the sql 
		//$result = mysql_db_query($theUtility->dbName,"$sql") or die ("listAll pattern_set select sql failed");
		$result = $this->dbLink->query($sql);
		$result->data_seek(0);
		
		/* loop through results*/
		//while ($row = mysql_fetch_array ($result))
		while ($row = $result->fetch_assoc())
		{
		

						
			//instantiate object
			$theTrainingDatum = new TrainingDatum();
			$theTrainingDatum->constructFromRow($row);

			if ($displayMode == "fullPage"){

			echo("<tr>");

				echo("<td>\n");
					echo ($theError->id);
				echo("</td>\n");
				
				echo("<td>\n");
					echo ($theError->trainingFileId);
				echo("</td>\n");	
				
				echo("<td>\n");
					echo ($theError->whenLogged);
				echo("</td>\n");

				echo("<td>\n");
					echo ($theError->batch);
				echo("</td>\n");

				echo("<td>\n");
					echo ($theError->epoch);
				echo("</td>\n");

				echo("<td>\n");
					echo ($theError->sse);
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
