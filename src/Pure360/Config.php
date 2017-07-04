<?php

namespace Pure360;

class Config
{
    public static function load_json($path)
    {
        if (($str_contents = file_get_contents($path)) === false) {
            throw new \Exception("warning: \$str_contents = file_get_contents(\$path) failed!");
        }

        if (($result = json_decode($str_contents, true)) === null) {
            throw new \Exception("warning: \$result = json_decode(\$str_contents, true) failed!");
        }
        return $result;
    }
}
