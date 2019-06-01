<?php

/**
 * @param $name
 * @param string $db - The database name/file
 * @return BaseDataProvider
 * @throws Exception - When an incorrect provider is requested
 */
function get_provider($name, $db = null) {
    switch ($name) {
        case "access":
            $file = (!empty($db) && file_exists($db))
                ? $db
                : $_ENV['ACCESSDB_FILE_PATH'];

            $provider = new MsAccessDataProvider($file);
            break;
        case "mysql":
            $provider = new MySQLDataProvider($_ENV['MYSQL_HOST'],
                                         $_ENV['MYSQL_USERNAME'],
                                         $_ENV['MYSQL_PASSWORD'],
                                         !empty($db) ? $db : $_ENV['MYSQL_DBNAME']);
            break;
        case "mssql":
            $provider = new MsSQLDataProvider($_ENV['MSSQL_HOST'],
                                         $_ENV['MSSQL_USERNAME'],
                                         $_ENV['MSSQL_PASSWORD'],
                                        !empty($db) ? $db : $_ENV['MSSQL_DBNAME']);
            break;
        default:
            throw new Exception($name. " is not not supported yet");
    }

    $provider->Connect();

    return $provider;
}

/**
 * Handles exceptions
 *
 * @param Exception $e
 */
function handleException($e) {
    http_response_code(500);

    $err = array(
        'message' => intval($_ENV['SHOW_ERRORS'])
            ? $e->getMessage()
            : 'An error has occurred'
    );

    die(json_encode($err, JSON_INVALID_UTF8_IGNORE ));
}