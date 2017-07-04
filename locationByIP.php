<?php

use Pure360\Config;
use Pure360\MyDB;
use Pure360\GeoIP;

require('vendor/autoload.php');

$dbConfig = Config::load_json(__DIR__ . '/config.json')['dbConfig'];

// connect to db
$obj_db = new MyDB($dbConfig['host'], $dbConfig['user'], $dbConfig['pass']); // uses inherited __construct method from mysqli class

if (($result = $obj_db->select_db($dbConfig['db'])) === false) {
    throw new \Exception("warning: \$obj_db->select_db() failed!");
}

if (array_key_exists('IP', $_GET)) {
    $str_ip = $_GET['IP'];
} else {
    throw new \Exception("warning: missing IP parameter!");
}

if (($countryName = GeoIP::getCountry($obj_db, $str_ip)) === false) {
} else {
    echo $countryName;
}

// display sucess/error message
