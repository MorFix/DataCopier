<?php
require(__DIR__ . "../vendor/autoload.php");
require(__DIR__ . "../src/load.php");

$dest = new MySQLDataProvider($_ENV['MYSQL_HOST'], $_ENV['MYSQL_USERNAME'], $_ENV['MYSQL_PASSWORD'], $_ENV['MYSQL_DBNAME']);
$source = new MsAccessDataProvider($_ENV['ACCESSDB_FILE_PATH']);

//(new DataCopier($source, $dest))->CopyTable('orders');