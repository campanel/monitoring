#!/usr/bin/php -q
<?php
/**
* Guardar status host em banco de dados sqlite
* plugin deve ser adicionado ao event handler do IC no nagios ou no opmon
* Autor cleber.campanel@intreop.com.br
*/

function main() {
   //writeLog("blablau");
	$options = get_options();
	insere_status_host( json_encode($options) );
    //writeLog(json_encode($options)."\n");

    print "fim\n";
}


function insere_status_host( $description ){
	/***
	CREATE TABLE status_hosts (id integer primary key autoincrement, created_at date , description text )
	*/
        
    $file = dirname(__FILE__).'/status_hosts.db';
    //$db = new SQLite3($file);
    $db = new PDO('sqlite:'.$file);
    chmod ($file, 0777);
    $db->query('CREATE TABLE if not exists status_hosts (id integer primary key autoincrement, created_at date , description text )');

    $created_at = date('Y-m-d H:i:s');
    //$description = htmlspecialchars($description, ENT_QUOTES);
    $query = "INSERT INTO status_hosts (created_at , description) VALUES ('$created_at', '$description')";
    $sqliteResult =  $db->query($query);
    if (!$sqliteResult ) {
        //var_dump("\n\n *************** Informar ao setor de DESENVOLVIMENTO *************** ");
        var_dump($query);
        var_dump($db->lastErrorMsg());
    }

    $db = null;
}

function get_options() {
    $shortopts  = "H:i:s:S:T:o:l:h";
    $longopts  = array(
                        "hostname:",
                        "address:",
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
                        "i" => "address",
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

function help() {
    $basename = str_replace(".php", "", basename($_SERVER[PHP_SELF]));
    $texto = "Version: 1.0 \n";
    $texto .= "Plugin save host status in sqlite database\n";
    $texto .= "Should be added to the event handler of IC in nagios or OpMon\n";
    $texto .= './$basename -H $HOSTNAME$ -i $HOSTADDRESS$ -s $HOSTSTATEID$ -S $HOSTSTATE$ -T $HOSTSTATETYPE$ -o \'$HOSTOUTPUT$\' -l $LASTHOSTCHECK$ ';
    quit($texto, 3);
}

function quit($text, $code) {
    echo $text."\n";
    exit($code);
}

function writeLog($text) {
    $contents =  json_encode($text)."\n";
    $filename = "/var/log/priax-status_hosts.log";
    file_put_contents($filename, $contents, FILE_APPEND);
}

main();

?>
