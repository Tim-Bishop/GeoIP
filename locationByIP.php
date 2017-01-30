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

if ($status) {
  if (array_key_exists('IP',$_GET)) {
    $str_ip = $_GET['IP'];
  } else {
    $status = false;
    $str_error = "warning: missing ip parameter!";
  }
}

if ($status) {
  if (preg_match('@^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$@',$str_ip)) {
  } else {
    $status = false;
    $str_error = "warning: invalid ip address!";
  }
}

// find ip in table
if ($status) {
  $str_lngint = sprintf('%u',ip2long($str_ip)); // convert ip to unsigned integer

  $str_query = <<<SQL
SELECT
  `Country`
FROM
  `{$str_table}`
WHERE
  {$str_lngint} BETWEEN `IPfromInt` AND `IPtoInt`
SQL;
  if (($obj_mysqli_result = $obj_db->query($str_query)) === false) {
    $status = false;
    $str_error = "warning: \$obj_db->query() failed!";
  }
}

if ($status) {
  if (($arr_row = $obj_mysqli_result->fetch_assoc()) === false) {
    $status = false;
    $str_error = "\$arr_row = \$obj_mysqli_result->fetch_assoc() failed!";
  }
}

if ($status) {
  if (is_null($arr_row)) {
    $status = false;
    $str_error = "warning: ip address not found!";
  }
}

if ($status) {
  if (array_key_exists('Country',$arr_row)) {
    echo $arr_row['Country']."\n";
  }
}

// display sucess/error message
if ($status) {
} else {
  if (empty($str_error)) $str_error = "warning: unknown error!";
  echo $str_error."\n";
}