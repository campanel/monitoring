<?php

function geraDados($mode=0){
	global $query;
	$retorno = invokeQuery($query[$mode]);
	$d = array();
	$m = array();
	$metrics = array();
	/*metricas*/
	foreach($retorno as $k => $v) {
		print"k: $k => valor: $v \n";
		foreach($retorno[$k] as $x => $y){
			print"\t $k k: $x => valor: $y \n";
		} 
	}

	if (in_array($mode,array(1,2))) {
	//var_dump($retorno[STATUS]);
	foreach($retorno[DATA] as $k => $v) {
		//print"k: $k => valor $v \n";
	}
	
		foreach($retorno['DATA'] as $k => $v) {
			if ($v != $kold){
		  	if(count($m) > 0) {
					$m['label'] = $v;
					$d[] = $m;
					$m = array(); 
		    }
			}
			
			
			$kold = $v;
			$m[$retorno['STATUS'][$k]] = $retorno['QTD'][$k];
			//var_dump($m);

		}
	 	$m['label'] = $v;
	  $d[] = $m;
	}else{
		foreach($retorno['QTD'] as $k => $v) {
			$d[] = array('QTD' => $v, 'label' => $retorno['DATA'][$k]);
	  }
	}
	foreach($d[0] as $k => $v){
	  if ($k != 'label') {
			$metrics[] = array('metric' => $k, 'value' => $v);
	  }
	}
	output($d,$metrics);

}

function output($d,$m){
	$out = array('display_status' => '0');
	$data = array('data' => $d);
	//$out['metrics']['data'] = $m;
	$out['metrics']['summarize'] = $data;
	print json_encode($out);

} 
