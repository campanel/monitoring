#!/usr/bin/php -q
<?php


function main() {


 	
}	


function get_options() {
	$shortopts  = "i:m:w:c:s:b:h:d:";
	$longopts  = array(
						"customers:",
						"mode:",
						"debug:",
						"help"
						); 	
	$options = getopt($shortopts, $longopts);

	if(count($options) == 0 || isset($options['help']) || isset($options['h']))	help();
	return $options;

}

function metric($value, $warn, $crit) {
	if($warn <= $crit){

		if ($value >= $crit) {
		    return 2;
		}else if ($value >= $warn) {
	        return 1;
		}else if ($value < $warn) {
	        return 0;
		}else {
		    $this->quit("Unable to check.",3);
		}
  	}else if ($warn > $crit){

		if ($value <= $crit) {
		   	return 2;
		}else if ($value <= $warn) {
	       	return 1;
		}else if ($value > $warn) {
	       	return 0;
		}else {
		   	$this->quit("Unable to check.",3);
		}
  }  
}

function help() {
	$basename = str_replace(".php", "", basename($_SERVER[PHP_SELF]));
	$texto = "./$basename -H... ";
	quit($texto, 3);	
}

function quit($text, $code) {
    echo $text."\n";
    exit($code);
}

main();
?>
