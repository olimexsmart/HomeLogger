<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once 'login.php';

$sql = new mysqli($hostName, $userName, $passWord, $dataBase);
if ($sql->connect_error) {
    die($sql->connect_error);
}

$query = "SELECT * FROM logger.sensornames;";
if (!($result = $sql->query($query))) {
    die("Could not retreive sensor names: " . $sql->error);
}

// Getting current year in the format YYYY-(temperature/humidity)
$year = date('Y');
$data = array();
// Loop on installed sensors
while ($row = $result->fetch_assoc()) {
    $query = "SELECT temp.`temp-{$row['id']}`, hum.`hum-{$row['id']}` FROM logger.`$year-hum` as hum
                join `$year-temp` as temp on hum.minute = temp.minute
                where temp.`temp-{$row['id']}` is not null and hum.`hum-{$row['id']}` is not null
                order by temp.minute desc
                limit 1";

    if (!($resultData = $sql->query($query))) {
        die("Could not retreive data: " . $sql->error);
    }
    $rowData = $resultData->fetch_row();
    $data[$row['name']] = array($rowData[0], $rowData[1]);
}

echo json_encode($data);
