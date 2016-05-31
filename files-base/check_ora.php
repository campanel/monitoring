#!/usr/bin/php -q
<?php
/*
Autor: cleber.motta@opservices.com.br
Data: 14-05-2013

*/
putenv("ORACLE_HOME=/usr/lib/oracle/11.1/client");
putenv("TNS_ADMIN=/usr/lib/oracle/11.1/client/lib");
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");

function array_group_by($arr, $key, $metric, $val) {
	$result = array();
	$res = array();
	$id = $arr[0][$key];
	foreach($arr as $k => $v){
		unset($res);
		//print"id: $id k: $v[$key]\n";
		if($id != $v[$key]) unset($arr1);
		$id = $arr[$k][$key];
		foreach($v as $x => $y){
			//print"\t k:$k x: $x y: $y \n";
			if($x != $key)$res[$x] = $y;
			if($x == $key)$res['label'] = $y;
		}
		$arr1[] = $res;
		$result[$arr[$k][$key]] = $arr1;
	}
	return $result;
} 



$debug = 0;
$basename = str_replace(".php", "", basename($_SERVER[PHP_SELF]));
 
function main() {
	global $debug;
	global $basename;
	
	$options = get_options();
	//var_dump($options);
	
	switch($options['mode']) 
	{ 
		/**/case 1: { $query = "SELECT DATA, QTD FROM BRM_WT_QTDCHAMADOS_1_DP_VW " ; break; }
		case 2: { $query = "SELECT DATA, STATUS, QTD FROM BRM_WT_QTDCHAMSTATUS_1_DP_VW " ; break; }
		case 3: { $query = "SELECT STATUS, QTD FROM BRM_WT_QTDCHAMACUSTA_1_DP_VW " ; break; }
		/**/
		/*case 1: { $query = "SELECT DATA, QTD FROM BRM_WT_MIT_DP_QTDCHAMADOS_VW " ; break; }
		case 2: { $query = "SELECT DATA, STATUS, QTD FROM BRM_WT_MIT_DP_QTDCHAMSTATUS_VW " ; break; }
		case 3: { $query = "SELECT STATUS, QTD FROM BRM_WT_MIT_DP_QTDCHAMACUSTA_VW " ; break; }
		*/	
		default: { help(); }
	} 
	
	$retorno = invokeQuery($query,$options);
	var_dump($retorno);
	
	$d = array();
foreach($retorno[QTD] as $k => $v) {
	$d[] = array('QTD' => $v, 'label' => $retorno[DATA][$k]);
}
//var_dump($d);

	//$mostra = tableHtml($retorno);

	output($d);


	quit($mostra,3);
		
    
}

function output($array){
//	$out = array('custom' => 'custom value '.date('H:i:s'),'host_name' => 'host teste', 'service_description' => 'service teste', 'display_status' => '0', 'metrics' => array('data' => array(), 'summarize' => array()));
$out = array('display_status' => '0');
$metrics = array();
foreach($array[0] as $k => $v){
	if ($k != 'label') {
		$metrics[] = array('value' => $v,'metric'=>$k);
	}
}
$data = array('data' => $array);
$out['metrics']['data'] = $metrics;
$out['metrics']['summarize'] = $data;

print json_encode($out);
//var_dump($out);

} 


function invokeQuery($query,$options) {
// print "$query \n";

// Conexão com Oracle usando ORA
	if (!$conn=oci_connect("$options[user]","$options[password]","$options[connection]")) {
		$err = OCIError();
	  quit("Erro ao conectar ao Oracle-> " .$err[message],3);
		
	}
	$stid = oci_parse($conn, $query);
	oci_execute($stid);
	$nrows = oci_fetch_all($stid, $res);
	
	//var_dump($res);
	
//	$ret['num'] = $nrows;
//	$ret['dados'] = $res;

	oci_free_statement($stid);
	oci_close($conn);
	return $res;
}

function get_options() {
 	$shortopts  = "c:u:p:m:hd:";
	$longopts  = array(
		"connection:",
	 	"user:",
	 	"password:",
	 	"mode:",
	 	"debug:",
	 	"help"
	 	); 	
 
 	$options = getopt($shortopts, $longopts);
 	
	if($options[c]) $options['connection'] = $options[c]; unset($options[c]);
	if($options[u]) $options['user'] = $options[u]; unset($options[u]);
	if($options[p]) $options['password'] = $options[p]; unset($options[p]);
	if($options[m]) $options['mode'] = $options[m]; unset($options[m]);
	if($options[d]) $options['debug'] = $options[d]; unset($options[d]);
	
	if(count($options) == 0) help();
	
	if(isset($options['help']) || isset($options['h'])){
		help();
	}
	
 	return $options;
}
 

function help($arg) {
	global $basename;
	$msg = "Usage: $name.pl [OPTION]...
-h, --help
-c, --connection [connection tnsname]
-p, --port
-u, --user
-p, --password
-m, --mode 
\t1 => CHAMADOS TOTAIS DO DIA
\t2 => CHAMADOS TOTAIS DO DIA POR STATUS
\t3 => ACUMULADO DO MÊS POR STATUS

Uso: ./$basename.pl -T tnsname -U user -P senha -m numero
";
	quit($msg,3);
 }

function quit($text, $code) {
        echo $text."\n";
        exit($code);
}

function tableHtml($retorno){
	$html = $retorno['num']. " linhas <\br>\n";
	$html .= "<table border='1'>\n";
	foreach ($retorno[dados] as $col) {
		  $html .= "<tr><\br>\n";
		  foreach ($col as $item) {
		      $html .= "    <td>".($item !== null ? htmlentities($item, ENT_QUOTES) : "")."</td>\n";
		  }
		  $html .= "</tr>\n";
	}
	$html .= "</table>\n";
	return $html;
}
	
main();
?>
