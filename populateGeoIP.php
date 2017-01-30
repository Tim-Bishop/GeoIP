#!/usr/bin/php -q
<?php
include_once 'class.mydb.php';
include_once 'class.myZipArchive.php';

$str_table = 'GeoIPCountry';

$str_tmpdir = '/tmp';
$str_zipout = $str_tmpdir.'/'.'GeoIPCountryWhois.csv';

$str_url = 'http://geolite.maxmind.com/download/geoip/database/GeoIPCountryCSV.zip';

// connect to db
$status = true;
if ($status) {
  $obj_db = new mydb(); // uses inherited __construct method from mysqli class

  if ($obj_db->dberror) {
    $status = false;
    $str_error = $obj_db->dberror;
  }
}

// check if entries exist
if ($status) {
  $str_query = <<<SQL
SELECT
  COUNT(*) cnt
FROM
  `{$str_table}`
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
  if ($arr_row['cnt'] == '0') {
  } else {
    $status = false;
    $str_error = "info: table {$str_table} already contains {$arr_row['cnt']} rows";
  }
}

// download and unzip archive
$obj_ZipArchive = new myZipArchive();

if ($status) {
  if (($str_error = $obj_ZipArchive->downloadUnzip($str_url,$str_tmpdir)) === false) {
  } else {
    $status = false;
  }
}

// populate table
if ($status) {
  if (($res_fpcsv = fopen($str_zipout,'r')) === false) {
    $status = false;
    $str_error = "\$res_fpcsv = fopen() failed!";
  }
}

if ($status) {
  $arr_GeoIPCountry = array();
  $str_query = <<<SQL
INSERT INTO `{$str_table}`
  (`IPfrom`,`IPto`,`IPfromInt`,`IPtoInt`,`CountryCode`,`Country`)
VALUES
  (?,?,?,?,?,?)
SQL;
  if (($obj_mysqli_stmt = $obj_db->prepare($str_query)) === false) {
    $status = false;
    $str_error = "warning: \$obj_mysqli_stmt = \$obj_db->prepare() failed!";
  }
}

if ($status) {
  $str_IPfrom = $str_IPto = $str_IPfromInt = $str_IPtoInt = $str_CountryCode = $str_Country = '';
  if (($obj_mysqli_stmt->bind_param('ssssss',$str_IPfrom,$str_IPto,$str_IPfromInt,$str_IPtoInt,$str_CountryCode,$str_Country)) === false) {
    $status = false;
    $str_error = "warning: \$obj_mysqli_stmt->bind_param() failed!";
  }
}

if ($status) {
  $int_rowcount = 0;
  while ($status and $arr_GeoIPCountry = fgetcsv($res_fpcsv,0,',','"')) {
    $str_IPfrom = $arr_GeoIPCountry[0];
    $str_IPto = $arr_GeoIPCountry[1];
    $str_IPfromInt = $arr_GeoIPCountry[2];
    $str_IPtoInt = $arr_GeoIPCountry[3];
    $str_CountryCode = $arr_GeoIPCountry[4];
    $str_Country = $arr_GeoIPCountry[5];

    if ($obj_mysqli_stmt->execute() === false) {
      $status = false;
      $str_error = "\$obj_mysqli_stmt->execute() failed";
    } else {
      $int_rowcount++;
    }
  } // end-while

  // close resources
  $obj_mysqli_stmt->close();
  fclose($res_fpcsv);
}

// tidy tmp files
@unlink($str_zipout);

// display sucess/error message
if ($status) {
  echo "success: populateIPCountry {$int_rowcount} rows inserted";
} else {
  if (empty($str_error)) $str_error = "warning: unknown error!";
  echo $str_error."\n";
}