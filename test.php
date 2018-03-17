<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once('login.php');

$temperature = $_POST['temp'];
$humidity = $_POST['hum'];
$ID = $_POST['id'];

echo date('Y-m-d H:i:s') . "\n$temperature\n$humidity\n$ID\n";

$query = "insert into logger.template values(NULL, NULL, $ID, $temperature, $humidity)";
//file_put_contents('queries.txt', $query, FILE_APPEND);


$sql = new mysqli($hostName, $userName, $passWord, $dataBase);
if ($sql->connect_error) {
    die($sql->connect_error);
}


if (!$sql->query($query)) {
    echo "Could not insert into database: " . $sql->error;
}

$sql->close();
