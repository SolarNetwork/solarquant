<?php
if(isset($_REQUEST['value']))
{
    $nodeId = $_REQUEST['value'];
    
    $servername = "localhost";
    $username = "solarquant";
    $password = "solarquant";
    $dbname = "solarquant";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    $query = "DELETE FROM registered_nodes WHERE NODE_ID=".$nodeId;
    
    $result = $conn->query($query);    
    
    $conn->close();
    exit;
}
?>
