<?php
	
    $path = substr($_SERVER['DOCUMENT_ROOT'], 0, strpos($_SERVER['DOCUMENT_ROOT'], 'htdocs') + 6)."/bd_Name.json";
	$json = file_get_contents($path);
	$json_object = json_decode($json, true);

	try {
		$m = new MongoClient('mongodb://' . $json_object['ip'] . ':' . $json_object['porta']);
	} catch(Exception $e) {
		$m = null;
	}	
   
?>