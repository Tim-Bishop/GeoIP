<?php

namespace Pure360;

class GeoIP
{
    public static function createTables($obj_db)
    {
        // create table if it doesn't exist
        $str_query = <<<SQL
CREATE TABLE IF NOT EXISTS `GeoIP` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `IPfrom` char(15) COLLATE latin1_general_ci NOT NULL,
    `IPto` char(15) COLLATE latin1_general_ci NOT NULL,
    `IPfromInt` int(10) unsigned NOT NULL,
    `IPtoInt` int(10) unsigned NOT NULL,
    `CountryCode` char(2) COLLATE latin1_general_ci NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `IPfromInt` (`IPfromInt`),
    UNIQUE KEY `IPtoInt` (`IPtoInt`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci
SQL;
        if (($obj_mysqli_result = $obj_db->query($str_query)) === false) {
            throw new \Exception("warning: \$obj_db->query() failed!");
        }

        // create seperate country table
        $str_query = <<<SQL
CREATE TABLE IF NOT EXISTS `Country` (
    `Code` char(2) COLLATE latin1_general_ci NOT NULL,
    `Name` varchar(48) COLLATE latin1_general_ci NOT NULL,
    PRIMARY KEY (`Code`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci
SQL;
        if (($obj_mysqli_result = $obj_db->query($str_query)) === false) {
            throw new \Exception("warning: \$obj_db->query() failed!");
        }
    }

    public static function getCountry($obj_db, $str_ip)
    {
        $result = false;

        if (preg_match('@^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$@', $str_ip)) {
        } else {
            throw new \Exception("warning: invalid ip address!");
        }

        // find ip in table
        $str_lngint = sprintf('%u', ip2long($str_ip)); // convert ip to unsigned integer

        $str_query = <<<SQL
SELECT
    `Country`.`Name`
FROM
    `GeoIP`
JOIN
    `Country` ON `Country`.`Code`=`GeoIP`.`CountryCode`
WHERE
    {$str_lngint} BETWEEN `IPfromInt` AND `IPtoInt`
SQL;
        if (($obj_mysqli_result = $obj_db->query($str_query)) === false) {
            throw new \Exception("warning: \$obj_db->query() failed!");
        }

        if (($arr_row = $obj_mysqli_result->fetch_assoc()) === false) {
            throw new \Exception("warning: \$obj_mysqli_result->fetch_assoc() failed!");
        }

        if (is_array($arr_row) and array_key_exists('Name', $arr_row)) {
            $result = $arr_row['Name'];
        }
        return $result;
    }

    public function populateGeoIP($obj_db, $str_zipout)
    {
        $str_query = <<<SQL
INSERT INTO `GeoIP`
    (`IPfrom`,`IPto`,`IPfromInt`,`IPtoInt`,`CountryCode`)
VALUES
    (?,?,?,?,?)
SQL;
        if (($obj_mysqli_stmt1 = $obj_db->prepare($str_query)) === false) {
            throw new \Exception("warning: \$obj_db->prepare() failed!");
        }

        $str_query = <<<SQL
INSERT INTO `Country`
    (`Code`,`Name`)
VALUES
    (?,?)
SQL;
        if (($obj_mysqli_stmt2 = $obj_db->prepare($str_query)) === false) {
            throw new \Exception("warning: \$obj_db->prepare() failed!");
        }

        $str_IPfrom = $str_IPto = $str_IPfromInt = $str_IPtoInt = $str_CountryCode = $str_CountryName = '';

        if (($obj_mysqli_stmt1->bind_param('sssss', $str_IPfrom, $str_IPto, $str_IPfromInt, $str_IPtoInt, $str_CountryCode)) === false) {
            throw new \Exception("warning: \$obj_mysqli_stmt->bind_param() failed!");
        }

        if (($obj_mysqli_stmt2->bind_param('ss', $str_CountryCode, $str_CountryName)) === false) {
            throw new \Exception("warning: \$obj_mysqli_stmt2->bind_param() failed!");
        }

        if (($res_fpcsv = fopen($str_zipout, 'r')) === false) {
            throw new \Exception("fatal: fopen() failed!");
        }

        $arr_GeoIPCountry = [];
        while ($arr_GeoIPCountry = fgetcsv($res_fpcsv, 0, ',', '"')) {
            $str_IPfrom = $arr_GeoIPCountry[0];
            $str_IPto = $arr_GeoIPCountry[1];
            $str_IPfromInt = $arr_GeoIPCountry[2];
            $str_IPtoInt = $arr_GeoIPCountry[3];
            $str_CountryCode = $arr_GeoIPCountry[4];
            $str_CountryName = $arr_GeoIPCountry[5];

            if (($result = $obj_mysqli_stmt1->execute()) === false) {
                throw new \Exception("fatal: \$obj_mysqli_stmt1->execute() failed!");
            }
            if (($result = $obj_mysqli_stmt2->execute()) === false) {
                switch ($obj_mysqli_stmt2->errno) {
                    case 1062: // duplicate key
                        break;
                    default:
                        throw new \Exception("fatal: \$obj_mysqli_stmt2->execute() failed!");
                }
            }
        } // end-while

        // close resources
        $obj_mysqli_stmt1->close();
        $obj_mysqli_stmt2->close();
        fclose($res_fpcsv);
    }

}
