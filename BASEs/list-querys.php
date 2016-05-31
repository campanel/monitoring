<?php

	//	file_put_contents("/var/log/processlist.log", $beBase, FILE_APPEND);
	
$db = mysql_connect('127.0.0.1','root','') or die("Database error"); 

while (true) {
 $q = "show full processlist";
$ret = mysql_query($q);
	while ($row = mysql_fetch_row($ret)) {
		
		if($row[7] != "show full processlist" and $row[7] != ""){
			

			file_put_contents("/var/log/processlist.log", "---begin---\n", FILE_APPEND);
			file_put_contents("/var/log/processlist.log", $row, FILE_APPEND);
			file_put_contents("/var/log/processlist.log", "\n ---end--- \n", FILE_APPEND);
			var_dump($row);
		} 
	}
  flush();

}


?>

