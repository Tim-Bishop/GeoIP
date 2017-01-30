#!/usr/bin/php -q
<?php
include_once 'class.mydb.php';

$str_table = 'GeoIPCountry';

// connect to db
$status = true;
if ($status) {
  $obj_db = new mydb(); // uses inherited __construct method from mysqli class

  if ($obj_db->dberror) {
    $status = false;
    $str_error = $obj_db->dberror;
  }
}

// find ip in table
if ($status) {
  $str_query = <<<SQL
CREATE TABLE IF NOT EXISTS `{$str_table}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IPfrom` char(15) COLLATE latin1_general_ci NOT NULL,
  `IPto` char(15) COLLATE latin1_general_ci NOT NULL,
  `IPfromInt` int(10) unsigned NOT NULL,
  `IPtoInt` int(10) unsigned NOT NULL,
  `CountryCode` char(2) COLLATE latin1_general_ci NOT NULL,
  `Country` varchar(48) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IPfromInt` (`IPfromInt`),
  UNIQUE KEY `IPtoInt` (`IPtoInt`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci
SQL;
  if (($obj_mysqli_result = $obj_db->query($str_query)) === false) {
    $status = false;
    $str_error = "warning: \$obj_db->query() failed!";
  }
}

// display sucess/error message
if ($status) {
} else {
  if (empty($str_error)) $str_error = "warning: unknown error!";
  echo $str_error."\n";
}