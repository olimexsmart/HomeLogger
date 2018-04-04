<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once 'login.php';
$sql = new mysqli($hostName, $userName, $passWord, $dataBase);
if ($sql->connect_error) {
    die($sql->connect_error);
}

$post = file_get_contents('php://input');
$params = json_decode($post, true);
/*
The parameters are:
- start: timestamp start
- end: timestamp end
- type: temperature or humidity
- sensors: [int array] ids of sensor required
 */
// Name of the file needed
$fileName = "buffer/{$params['start']}-{$params['end']}-{$params['type']}-" . oneHotEncode($params['sensors']) . ".csv";
if (file_exists($fileName)) { // This is already checked in frontend but you never know
    die('OK');
}
// Open file for writing and write headers
$first = $params['sensors'][0];
sort($params['sensors']); // To maintain order consistent
$query = "SELECT name FROM sensorname where id = $first";
for ($i = 1; $i < count($params['sensors']); $i++) {
    $u = $params['sensors'][$i];
    $query .= " or id = $u";
}
$query .= ";\n";
if (!($result = $sql->query($query))) {
    echo "Could not retreive names: " . $sql->error;
}
$data = $result->fetch_all();
$a = array('Time'); // x-values label
foreach ($data as &$d) {
    $a[] = $d[0];
}
$file = fopen($fileName, 'w');
fputcsv($file, $a);
unset($a);

// Loop through years if is that necessary
$startYear = getdate($params['start'])['year'];
$endYear = getdate($params['end'])['year'];
$type = '';
if ($params['type'] == 0) {
    $type = 'temperature';
} elseif ($params['type'] == 1) {
    $type = 'humidity';
}
for ($y = $startYear; $y <= $endYear; $y++) {
    // Join on stamp column of first sensor
    $query = "SELECT s$first.stamp";
    // Columns wanted
    foreach ($params['sensors'] as &$s) {
        $query .= ", s$s.$type";
    }
    // From
    $query .= "\nFROM `$y-s$first` as s$first\n";
    // Join
    for ($i = 1; $i < count($params['sensors']); $i++) {
        $u = $params['sensors'][$i];
        $query .= "JOIN `$y-s$u` as s$u on s$first.stamp = s$u.stamp\n";
    }
    // Where
    $query .= "WHERE s$first.stamp > {$params['start']} AND s$first.stamp < {$params['end']}\n";
    // Order
    $query .= "ORDER BY s$first.stamp ASC;\n";

    // Query data
    if (!($result = $sql->query($query))) {
        echo "Could not retreive data from database: " . $sql->error;
    }
    // Fetch data and write file
    $data = $result->fetch_all();
    foreach ($data as &$d) {
        $d[0] = date('Y/m/d H:i', $d[0]);
        fputcsv($file, $d, ',', ' ');
    }
}

echo 'Done';
fclose($file);
$sql->close();

//////////////////////////////////////////////////////////////////////////////////////////////////////
function oneHotEncode($intArray)
{
    $result = 0;

    foreach ($intArray as &$s) {
        $mask = 1;
        $result = $result | $mask << $s;
    }
    return $result;
}
