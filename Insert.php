<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once('login.php');

$temperature = $_POST['temp'];
$humidity = $_POST['hum'];
$ID = $_POST['id'];

echo date('Y-m-d H:i:s') . "\n$temperature\n$humidity\n$ID\n";


$sql = new mysqli($hostName, $userName, $passWord, $dataBase);
if ($sql->connect_error) {
    die($sql->connect_error);
}

// Getting current month in the format YYYY-s$ID
$tableName = date('Y') . "s$ID";

// Check if said table exists
$query = "SELECT * FROM information_schema.tables WHERE table_schema = '$dataBase' AND table_name = '$tableName' LIMIT 1;";
if (!($result = $sql->query($query))) {
    echo "Could not check table existence: " . $sql->error;
}

// If table does not exists, create it
if(!$result->num_rows == 1) { 
	$query = "CREATE TABLE `$tableName` (
	  `stamp` int(11) unsigned NOT NULL,
	  `temperature` float NOT NULL DEFAULT '0',
	  `humidity` tinyint(3) unsigned NOT NULL DEFAULT '0',
	  PRIMARY KEY (`stamp`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	if (!$sql->query($query)) {
		echo "Could not create new table: " . $sql->error;
	}
}

// Insert the data
$query = "insert into logger.`$tableName` values(NULL, NULL, $ID, $temperature, $humidity);";
if (!$sql->query($query)) {
    echo "Could not insert into database: " . $sql->error;
}

$sql->close();
