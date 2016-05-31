<?php
/*
Autor: cleber.motta@opservices.com.br
Data: 14-05-2013
*/

//$mode = htmlspecialchars($_REQUEST['mode']);
putenv("ORACLE_HOME=/usr/lib/oracle/11.1/client");
putenv("TNS_ADMIN=/usr/lib/oracle/11.1/client/lib");

$mode = htmlspecialchars($_REQUEST['mode']);

 
function main() {
	global $mode;
	//$mode = 1;
	$retorno = invokeQuery($mode);

	if($mode == 0 ){
		list($data,$metrics) = resultArrays($retorno, "DATA","TOTAL", "QTD");
	}else{
		list($data,$metrics) = resultArrays($retorno, "DATA","STATUS", "QTD");
	}
	//var_dump($metrics);

	//var_dump($data);
	$out = array('display_status' => '0');
	$out['metrics'] = $metrics;
	$out['data'] = $data;

	print json_encode($out);
	
	
	exit;
    
}

function resultArrays($arr, $key, $metric, $val) {
global $mode;
	//var_dump($arr);

	$id = $arr[0][$key];

	foreach($arr as $k => $v){
		if($id != $v[$key]) unset($arr1);
		$id = $arr[$k][$key];
	
		$arr1['label'] = $arr[$k]['DATA'];
		foreach($v as $x => $y){
			if($x == $metric){
				$arr1[$arr[$k][$metric]] =  $arr[$k][$val];
				
			}
			
		}
		if($mode == 1) $arr1['TOTAL'] =  "10";
		$result[$id] = $arr1;
	}

	foreach($result[$id] as $k => $v){
		if ($k != 'label') {
			$metrics[] = array('value' => $v,'metric'=>$k);
		}
	}

	$res[0] = array_values($result);
	$res[1] = $metrics;

	return $res;
}


/***Executa query***/
function invokeQuery($mode) {
	//$mode = 2;
	$query[0] = "SELECT TO_CHAR(DATA,'dd') DATA, TO_CHAR('TOTAL') TOTAL, QTD FROM BRM_WT_MIT_DP_QTDCHAMADOS_VW ";
	//$query[1] = "SELECT TO_CHAR(DATA,'dd') DATA, STATUS, QTD FROM BRM_WT_MIT_DP_QTDCHAMSTATUS_VW where rownum<=6 ";
	$query[1] = "SELECT TO_CHAR(DATA,'dd') DATA, STATUS, QTD FROM BRM_WT_MIT_DP_QTDCHAMSTATUS_VW  ";
	$query[2] = "SELECT TO_CHAR('TOTAL') DATA, STATUS, QTD FROM BRM_WT_MIT_DP_QTDCHAMACUSTA_VW ";

	if (!$conn=oci_connect("brmdw","brmdw","dwbrmalls")) {
		$err = OCIError();
	  quit("Erro ao conectar ao Oracle-> " .$err[message],3);
		
	}
	$stid = oci_parse($conn, $query[$mode]);
	oci_execute($stid);
	while (($row = oci_fetch_array($stid, OCI_ASSOC))) {
    $arr[] = $row;
  }
	oci_free_statement($stid);
	oci_close($conn);
	//var_dump($arr);
	return $arr;
	
}

function quit($text, $code) {
        echo $text."\n";
        exit($code);
}
	
main();
?>
