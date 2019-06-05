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
            $provider = new MsAccessDataProvider(!empty($db) ? $db : $_ENV['ACCESSDB_FILE_PATH']);
            break;
        case "mysql":
            $provider = new MySQLDataProvider($_ENV['MYSQL_HOST'],
                                         $_ENV['MYSQL_USERNAME'],
                                         $_ENV['MYSQL_PASSWORD'],
                                         !empty($db) ? $db : $_ENV['MYSQL_DBNAME'],
                                         intval($_ENV['INSERT_BATCH']));
            break;
        case "mssql":
            $provider = new MsSQLDataProvider($_ENV['MSSQL_HOST'],
                                         $_ENV['MSSQL_USERNAME'],
                                         $_ENV['MSSQL_PASSWORD'],
                                        !empty($db) ? $db : $_ENV['MSSQL_DBNAME'],
                                         intval($_ENV['INSERT_BATCH']));
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
    $general_error = 'An error has occurred';
    http_response_code(500);

    $err = array(
        'message' => intval($_ENV['SHOW_ERRORS'])
            ? $e->getMessage()
            : $general_error
    );

    $encoded = json_encode($err);
    if ($encoded === false) {
      $encoded = json_encode($general_error);
    }

    die($encoded);
}