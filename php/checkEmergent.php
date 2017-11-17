<?php

exec("pgrep java", $output, $return);
//exec("pmap 8995", $output, $return);
//exec("pmap `pgrep emergent` | grep total", $output, $return);

//pmap `pgrep emergent` | grep total
echo "sizeof(output): ".sizeof($output)."<br>";

echo "output[0]: ".$output[0]."<br>";
echo "output[1]: ".$output[1]."<br>";
echo "return: ".$return."<br>";
 
if ($return == 0) {
    echo "Ok, emergent process is running\n";
}
else
{
	echo "Ok, emergent process is NOT running\n";	
}

?>
