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

/*
	Retreive all the data requested into an array.
	Traslate this array in JSON
*/
/*
// Load sensors names
$query = "SELECT * FROM sensorname;";
if (!($result = $sql->query($query))) {
    echo "Could not retreive names: " . $sql->error;
}
$data = $result->fetch_all();
// Write header here
*/

// Loop through years if is that necessary
$startYear = getdate($params['start'])['year'];
$endYear = getdate($params['end'])['year'];
$type = '';
if ($params['type'] == 0) {
    $type = 'temp';
} elseif ($params['type'] == 1) {
    $type = 'hum';
}
for ($y = $startYear; $y <= $endYear; $y++) {
    // Query data
    $query = "SELECT minute";
    // Select each sensor in ascending order
    sort($params['sensors']);
    foreach ($params['sensors'] as &$id) {
    	$query .= ",`$type-$id`";
    }
    $query .= " FROM logger.`$y-$type`;";
    
    
    if (!($result = $sql->query($query))) {
 	  	echo "Could not retreive data: " . $sql->error;
		}
    $data = $result->fetch_all();
    
    // DO SOMETHING WITH IT
}

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
