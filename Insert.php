<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once 'login.php';

$temperature = $_POST['temp'];
$humidity = $_POST['hum'];
$ID = $_POST['id'];

echo date('Y-m-d H:i:s') . "\n$temperature\n$humidity\n$ID\n";

$sql = new mysqli($hostName, $userName, $passWord, $dataBase);
if ($sql->connect_error) {
    die($sql->connect_error);
}

// Getting current year in the format YYYY-(temperature/humidity)
$year = date('Y');

/*
Table creation
To avoid having a potentially infinite number of rows as time passes,
the idea is to split records in years.
The table created contains just the column for the timestamp. The
columns for the different sensors will be created programmatically.
 */
$sensors = array("temp", "hum");
foreach ($sensors as &$s) {
// Check if said table exists
    $query = "SELECT *
				FROM information_schema.tables
				WHERE table_schema = '$dataBase'
				AND table_name = '$year-$s'
				LIMIT 1;";

    if (!($result = $sql->query($query))) {
        die("Could not check table existence: " . $sql->error);
    }

// If table does not exists, create it
    if (!$result->num_rows == 1) {
        $query = "CREATE TABLE `logger`.`$year-$s` (
					`minute` INT UNSIGNED NOT NULL,
					PRIMARY KEY (`minute`));";
        if (!$sql->query($query)) {
            die("Could not create new table: " . $sql->error);
        }
    }
}
/*
Check if column for this particular sensor ID is present.
If not, create it, this allows for dynamic addition of sensors
 */
foreach ($sensors as &$s) {
    // Checking column existence
    $query = "SHOW COLUMNS FROM `$year-$s` LIKE '$s-$ID';";
    if (!($result = $sql->query($query))) {
        die("Could not check column existence: " . $sql->error);
    }

    // If column does not exists, alter table to insert it
    if (!$result->num_rows == 1) {
        // Getting name of last column
        $query = "SELECT COLUMN_NAME
						FROM information_schema.COLUMNS
						WHERE TABLE_SCHEMA = 'logger'
						AND TABLE_NAME ='$year-$s'
						ORDER BY ORDINAL_POSITION DESC
						LIMIT 1;";
        if (!($result = $sql->query($query))) {
            die("Could not check last column name: " . $sql->error);
        }
        $colName = $result->fetch_row()[0];

        $query = "ALTER TABLE `logger`.`$year-$s`
				    ADD COLUMN `$s-$ID` FLOAT DEFAULT NULL AFTER `$colName`;";
        if (!$sql->query($query)) {
            die("Could not create new table: " . $sql->error);
        }
    }
}

// Insert the data
//Getting a time stamp of current minute
$now = new DateTime();
$format = $now->format('Y-m-d H:i') . ":00";
$then = new DateTime($format);
$epoch = $then->getTimestamp();
//$stamp = time();
/*
The first sensor in this minute will trigger the addition
of the line corresponding to the minute.
Other sensors will update that line.

ON DUPLICATE KEY UPDATE <------ miracle
 */
foreach ($sensors as &$s) {
    // Insert only if key is not present, otherwise update
    $query = "INSERT INTO logger.`$year-$s` (minute, `$s-$ID`) VALUES($epoch, $_POST[$s])
				ON DUPLICATE KEY UPDATE `$s-$ID`=$_POST[$s];";
    if (!$sql->query($query)) {
        die("Could not insert into database: " . $sql->error);
    }
}

$sql->close();
