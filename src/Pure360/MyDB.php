<?php

use Pure360\Config;

namespace Pure360;

class MyDB extends \mysqli
{
    public function dbExists($db)
    {
        $result = false;
        $str_query = "SHOW DATABASES LIKE '{$db}'";

        if (($mysqli_result = $this->query($str_query)) === false) {
            throw new Exception("fatal: mysqli::query() failed!");
        }

        if (($row = $mysqli_result->fetch_assoc()) === false) {
            throw new Exception("fatal: mysqli::fetch_assoc() failed!");
        }

        if (count($row) > 0) {
            $result = true;
        }
        return $result;
    }

    public function dbCreate($db)
    {
        $str_query = "CREATE DATABASE IF NOT EXISTS {$db}";
        if (($mysqli_result = $this->query($str_query)) === false) {
            throw new Exception("fatal: mysqli::query() failed!");
        }
        $result = true;
        return $result;
    }

    public function tableRows($table)
    {
$str_query = <<<SQL
SELECT
    COUNT(*) cnt
FROM
    `{$table}`
SQL;
        if (($obj_mysqli_result = $this->query($str_query)) === false) {
            throw new Exception("warning: mysqli::query() failed!");
        }

        if (($arr_row = $obj_mysqli_result->fetch_assoc()) === false) {
            throw new Exception("warning: \$obj_mysqli_result->fetch_assoc() failed!");
        }
        return $arr_row['cnt'];
    }
}
