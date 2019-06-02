<?php
require(__DIR__ . "/../vendor/autoload.php");
require(__DIR__ . "/../src/load.php");

ini_set('display_errors', 0);

header("Content-Type: application/json; charset=UTF-8");
try {
    /**
     * @var IDataSource $provider
     */
    $provider = get_provider($_REQUEST['src'], $_REQUEST['src_db']);

    if (!($provider instanceof IDataSource)) {
        die(json_encode(array()));
    }

    die(json_encode($provider->GetTablesNames()));
} catch (Exception $e) {
    handleException($e);
}
