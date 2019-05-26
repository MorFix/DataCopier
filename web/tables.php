<?php
require(__DIR__ . "/../vendor/autoload.php");
require(__DIR__ . "/../src/load.php");

ini_set('display_errors', 0);

/**
 * @var IDataSource $provider
 */
$provider = get_provider($_REQUEST['source']);

if (!($provider instanceof IDataSource)) {
    die(json_encode(array()));
}

echo json_encode($provider->GetTablesNames());