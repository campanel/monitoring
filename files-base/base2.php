#!/usr/bin/php -q
<?php


function main() {


 	
}	


function get_options() {
    $shortopts  = "H:i:s:S:T:o:l:h";
    $longopts  = array(
                        "hostname:",
                        "adress:",
                        "hoststateid:",
                        "hoststate:",
                        "hoststatetype:",
                        "hostoutput:",
                        "lasthostcheck:",
                        "help"
                        );
    $options = getopt($shortopts, $longopts);

    if(count($options) == 0 || isset($options['h'])){
       help(); 
    }

    $long_opts  = array("H" => "hostname",
                        "i" => "adress",
                        "s" => "hoststateid",
                        "S" => "hoststate",
                        "T" => "hoststatetype",
                        "o" => "hostoutput",
                        "l" => "lasthostcheck"
                        );

    foreach ($options as $key => $value) {
        if(array_key_exists($key, $long_opts)){
            $options[$long_opts[$key]] = $value;
            unset($options[$key]);
        }
    }
    
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
