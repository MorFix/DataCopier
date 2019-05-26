<?php

function get_provider($input) {
    switch ($input) {
        case "access":
            $file = (!empty($_REQUEST["file"]) && file_exists($_REQUEST["file"]))
                ? $_REQUEST["file"]
                : $_ENV['ACCESSDB_FILE_PATH'];

            return new MsAccessDataProvider($file);
            break;
        case "mysql":
            return new MySQLDataProvider($_ENV['MYSQL_HOST'], $_ENV['MYSQL_USERNAME'], $_ENV['MYSQL_PASSWORD'], $_ENV['MYSQL_DBNAME']);
            break;
        default:
            die(json_encode($input. " is not not supported yet"));
            exit;
    }
}