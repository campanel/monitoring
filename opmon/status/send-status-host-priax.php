#!/usr/bin/php -q
<?php
/**
* 
* Autor cleber.campanel@intreop.com.br
*/

function main() {
    while(true){
    	$statusHosts = get_status_host_all();

        var_dump($statusHosts);
        if($statusHosts){
            foreach ($statusHosts as $key => $value) {
                $arrData = json_decode($value['description']);
                $arrData->lasthostcheck = date('Y-m-d H:i:s', $arrData->lasthostcheck);

                $arrData->save_id = $value['id'];
                $return_send = send_status_data_to_firebase(json_encode($arrData));
                //var_dump($return_send);
                if ($return_send) {
                    deleteStatusHostBySaveId( $value['id']);
                }
            }
        }else {
            sleep(2);
        }

        flush();
    }

    /*
    while(true){
    	$created_at = date('Y-m-d H:i:s');
    	$msg = "[".$created_at."] - Priax =>  :-P";
    	writeLog($msg);
    	sleep(1);
    }
    */
}

function get_status_host_all(){
        
    $file = dirname(__FILE__).'/status_hosts.db';
    $db = new PDO('sqlite:'.$file);

    $query = 'select * from status_hosts ORDER BY id asc';
    $results =  $db->query($query);

    $result = null;
    while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        $result[] = $row;
    }

    $db = null;
    return $result;
}

function deleteStatusHostBySaveId($id){
        
    $file = dirname(__FILE__).'/status_hosts.db';
    $db = new PDO('sqlite:'.$file);

    $query = 'delete from status_hosts where id = '.$id.'';
    $results =  $db->query($query);

    $db = null;
    return $result;
}

function send_status_data_to_firebase($data_string) {

    $ch = curl_init('https://opmons.firebaseio.com/netcentrics/statushosts.json');                                                                      
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);                                                                    
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($data_string))                                                                       
    );                                                                                                                   
                                                                                                                 
    $result = curl_exec($ch);
    return $result;
}

function writeLog($text) {
    $contents =  json_encode($text)."\n";
    $filename = "/var/log/priax-status_hosts.log";
    file_put_contents($filename, $contents, FILE_APPEND);
}

main();

?>
