<?php
require(__DIR__ . "/../vendor/autoload.php");
require(__DIR__ . "/../src/load.php");

ini_set('display_errors', 0);

if (empty($_REQUEST['copy'])) {
    exit;
}

try {
    $source = get_provider($_REQUEST["src"], $_REQUEST["src_db"]);
    $source->Connect();

    $dest = get_provider($_REQUEST["dest"], $_REQUEST["dest_db"]);
    $dest->Connect();

    $copier = new DataCopier($source, $dest);

    foreach($_REQUEST["source_tables"] as $table) {
        $copier->CopyTable($table);
    }

    die(json_encode("Copied Successfully!"));
} catch (Exception $e) {
    handleException($e);
}