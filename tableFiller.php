<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once 'login.php';
$sql = new mysqli($hostName, $userName, $passWord, $dataBase);        
if ($sql->connect_error) {
	die($sql->connect_error);
}     


$timestamp = time();
for ($i = 0; $i < 43200; $i++) {
	$timestamp += 60; // For each minute
	for ($k = 1; $k < 6; $k++) {	
		$temperature = rand(15, 35);
		$humidity = rand(5, 95);	
		$query = "insert into logger.`2018-s". $k ."` values($timestamp, $temperature, $humidity);";	
		if (!$sql->query($query)) {
			echo "Could not insert into database: " . $sql->error;
		}
	}
}

$sql->close();