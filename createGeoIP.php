<?php

use Pure360\MyDB;
use Pure360\GeoIP;
use Pure360\Config;

require('vendor/autoload.php');

$dbConfig = Config::load_json(__DIR__.'/config.json')['dbConfig'];

// connect to db
$obj_db = new MyDB($dbConfig['host'], $dbConfig['user'], $dbConfig['pass']);

// check db exists
if ($obj_db->dbExists($str_db) === false) {
    // doesn't exist so create it
    $obj_db->dbCreate($str_db);
}

if (($result = $obj_db->select_db($dbConfig['db'])) === false) {
    throw new \Exception("warning: \$obj_db->select_db() failed!");
}

GeoIP::createTables($obj_db);

// display sucess/error message
echo "success: createGeoIP.php";
