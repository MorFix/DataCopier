<?php

function get_provider($input, $db = null) {
    switch ($input) {
        case "access":
            $file = (!empty($db) && file_exists($db))
                ? $db
                : $_ENV['ACCESSDB_FILE_PATH'];

            return new MsAccessDataProvider($file);
            break;
        case "mysql":
            return new MySQLDataProvider($_ENV['MYSQL_HOST'],
                                         $_ENV['MYSQL_USERNAME'],
                                         $_ENV['MYSQL_PASSWORD'],
                                         !empty($db) ? $db : $_ENV['MYSQL_DBNAME']);
            break;
        case "mssql":
            return new MsSQLDataProvider($_ENV['MSSQL_HOST'],
                                         $_ENV['MSSQL_USERNAME'],
                                         $_ENV['MSSQL_PASSWORD'],
                                        !empty($db) ? $db : $_ENV['MSSQL_DBNAME']);
            break;
        default:
            die(json_encode($input. " is not not supported yet"));
    }
}