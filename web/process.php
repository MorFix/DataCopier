<?php
require(__DIR__ . "/../vendor/autoload.php");
require(__DIR__ . "/../src/load.php");

ini_set('display_errors', 0);

if (empty($_REQUEST['copy'])) {
    exit;
}

$source = get_provider($_REQUEST["from"], $_REQUEST["src_db"]);
$dest = get_provider($_REQUEST["to"], $_REQUEST["dest_db"]);

try {
    $copier = new DataCopier($source, $dest);

    foreach($_REQUEST["source_tables"] as $table) {
        $copier->CopyTable($table);
    }

    die(json_encode("Copied Successfully!"));
} catch (Exception $e) {
    die(json_encode($e));
}