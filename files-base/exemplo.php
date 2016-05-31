<?php
/*
Autor: cleber.motta@opservices.com.br
Data: 14-05-2013
*/

//$mode = htmlspecialchars($_REQUEST['mode']);
putenv("ORACLE_HOME=/usr/lib/oracle/11.1/client");
putenv("TNS_ADMIN=/usr/lib/oracle/11.1/client/lib");
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");


 
function main() {

	$retorno = invokeQuery($query[$mode]);

	list($data,$metrics) = resultArrays($retorno, "DATA","STATUS", "QTD");

	//var_dump($metrics);

	//var_dump($data);
	$out = array('display_status' => '0');
	$out['metrics']['data'] = $metrics;
	$out['summarize']['data'] = $data;

	print json_encode($out);
	
	
	exit;
    
}

function resultArrays($arr, $key, $metric, $val) {

	foreach($arr as $k => $v){
		//print"id: $id k: $v[$key]\n";
		$result[] = array($arr[$k][$metric] => $arr[$k][$val], 'label' => $arr[$k][$key]);
		$metricas[] = $arr[$k][$metric];
	}
	$m = array_unique($metricas);
	foreach($m as $k => $v){
			$metrics[] = array('metric' => $v, 'value' => 0);
	}
	//var_dump($metrics);
	$res[0] = $result;
	$res[1] = $metrics;

	return $res;
}

	/*
	$id = $arr[0][$key];
	foreach($arr as $k => $v){
		unset($res);
		//print"id: $id k: $v[$key]\n";
		if($id != $v[$key]) unset($arr1);
		$id = $arr[$k][$key];
		foreach($v as $x => $y){
			//print"\t k:$k x: $x y: $y \n";
			//if($x != $key)$res[$x] = $y;
			if($x == $key){
				//$res['label'] = $y;
				$res[] = array($arr[$k][$metric] => $arr[$k][$val]);
				//$res['label'] = $y;
			}
			
			//$res['label'] = $y;
			//$res[] = array($arr[$k][$metric] => $arr[$k][$val], 'label' => $arr[$k][$key]);
			//$res[] = array($arr[$k][$metric] => $arr[$k][$val], 'label' => $arr[$k][$key]);
		}
		$res['label'] =  $arr[$k][$key];
		
		$arr1[] = $res;
		$result[$arr[$k][$key]] = $arr1;
		$metricas[] = $arr[$k][$metric];
	}*/

/***Executa query***/
function invokeQuery($mode) {
	$mode = 1;
	$query[0] = "SELECT TO_CHAR(DATA,'dd') DATA, QTD FROM BRM_WT_MIT_DP_QTDCHAMADOS_VW ";
	$query[1] = "SELECT TO_CHAR(DATA,'dd') DATA, STATUS, QTD FROM BRM_WT_MIT_DP_QTDCHAMSTATUS_VW";
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
