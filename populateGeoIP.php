<?php

use Pure360\Config;
use Pure360\MyDB;
use Pure360\MyZipArchive;
use Pure360\GeoIP;

require('vendor/autoload.php');

$config = Config::load_json(__DIR__.'/config.json');

$dbConfig = $config['dbConfig'];

$str_tmpdir = '/tmp';
$str_zipout = $str_tmpdir.'/'.'GeoIPCountryWhois.csv';

$str_url = 'http://geolite.maxmind.com/download/geoip/database/GeoIPCountryCSV.zip';

// connect to db
$obj_db = new MyDB($dbConfig['host'], $dbConfig['user'], $dbConfig['pass']); // uses inherited __construct method from mysqli class

if (($result = $obj_db->select_db($dbConfig['db'])) === false) {
    throw new \Exception("warning: \$obj_db->select_db() failed!");
}

$tableRows = $obj_db->tableRows('GeoIP');

if ($tableRows  == '0') {
} else {
    throw new \Exception("info: table GeoIP already contains {$tableRows} rows");
}

// download and unzip archive
MyZipArchive::create()->downloadUnzip($str_url, $str_tmpdir);

GeoIP::populateGeoIP($obj_db, $str_zipout);

// tidy tmp files
@unlink($str_zipout);

$geoIPRows = $obj_db->tableRows('GeoIP');
$countryRows = $obj_db->tableRows('Country');

// display sucess/error message
echo "success: populateIPCountry.php {$geoIPRows} IP and {$countryRows} country rows inserted";
