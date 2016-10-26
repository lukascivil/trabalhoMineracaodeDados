<?php

	//MONGODB Connection
	include("bd_conecta.php");
	$db = $m->selectDB("MD");

	/**
	 * [utf8_converter description]
	 * @param  [Array] $array [description]
	 * @return [Array]        [description]
	 */
	function utf8_converter($array) {
		array_walk_recursive($array, function(&$item, $key) {
			if(!mb_detect_encoding($item, 'utf-8', true)) {
				$item = utf8_encode($item);
			}
		});
		return $array;
	}

	/**
	 * [scan description]
	 * @param  [String] $dir         	[description]
	 * @param  [String] $currentPath 	[description]
	 * @param  [Array] 	&$folder     	[description]
	 * @param  [Array] 	&$file       	[description]
	 * @param  [Int] 	$level       	[description]
	 * @return [type]              		[description]
	 */
	function scan($dir, $currentPath, &$folder, &$file, $level) {

		if (file_exists($dir)) {
			
			$level++;

			foreach(scandir($dir) as $f) {
			
				if (!$f || $f[0] == '.') {
					continue; // Ignore hidden files
				}

				if (is_dir($dir . '/' . $f)) { // Folder
				
					$levelpath = explode('/', $currentPath. '/' . $f);

					//It is a folder
					$folder[] = utf8_converter(array(
						"_id" => (string)(new MongoId()),			// Dynamic Mongodb Id
						"type" => "folder",					// Node type
						"name" => $f,						// Folder name
						"level"=> $level,					// Level height
						"level_path" =>  $currentPath. '/' . $f,		// Level path as String
						"level_path_array"=> $levelpath,			// Level path as array
						"childs" => sizeof(scandir($dir . '/' . $f)) - 2,	// Number of children
					));

					scan($dir . '/' . $f, $currentPath. '/' . $f, $folder, $file,  $level);

				} else { // File

					$levelpath = explode('/', $currentPath. '/' . $f);

					// It is a file
					$file[] = utf8_converter(array(
						"_id" => (string)(new MongoId()),		// Dynamic Mongodb Id
						"type" => "file",				// Node type
						"name" => $f,					// File name
						"level" => $level,				// Level height
						"level_path" => $currentPath. '/' . $f,		// Level path as String
						"level_path_array" => $levelpath,		// Level path as array
						"file_extension" => end(explode('.', $f)), 	// File extension
						"size" => filesize($dir . '/' . $f) 		// File size
					));
				}
			}
		}
		//return;
	}
	
	//variables
	$folder = array();
	$file = array();
	$collection_name = "directory_nodes";
	$dir = $currentPath = "coffee";
	
	//Init All
	scan($dir, $currentPath, $folder, $file, 0);

	//Folder Mongo insert
	foreach ($folder as $doc) {
		$db->$collection_name->insert($doc);
	}

	//File Mongo insert
	foreach ($file as $doc) {
		$db->$collection_name->insert($doc);
	}

	echo "End!";
