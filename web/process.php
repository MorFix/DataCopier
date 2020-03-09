<?php
require(__DIR__ . "/../vendor/autoload.php");
require(__DIR__ . "/../src/load.php");

ini_set('display_errors', 0);

$request = json_decode(file_get_contents("php://input"), true);

if (empty($request) || empty($request['copy'])) {
    exit;
}

header("Content-Type: application/json; charset=UTF-8");
try {
    $source = get_provider($request["src"], $request["src_db"]);
    $dest = get_provider($request["dest"], $request["dest_db"]);
    $drop = $request["drop"] ? true : false;

    $copier = new DataCopier($source, $dest);

    foreach($request["source_tables"] as $table) {
        $copier->CopyTable($table, null, $drop);
    }

    die(json_encode("Copied Successfully!"));
} catch (Exception $e) {
    handleException($e);
}