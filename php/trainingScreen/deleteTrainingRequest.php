<?php
if(isset($_POST['value']))
{
    $reqId = $_POST['value'];
    
    $servername = "localhost";
    $username = "solarquant";
    $password = "solarquant";
    $dbname = "solarquant";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    $query = "DELETE FROM training_requests WHERE REQUEST_ID=".$reqId;
    
    $result = $conn->query($query);    
    
    $conn->close();
    exit;
}
?>