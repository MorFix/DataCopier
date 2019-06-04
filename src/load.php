<?php
// Environment variables
require(__DIR__ . '/load-dotenv.php');

// Interfaces
require(__DIR__ . '/Interfaces/IDataSource.php');
require(__DIR__ . '/Interfaces/IDataDestination.php');

// Data structures
require(__DIR__ . '/DataStructure/Table.php');
require(__DIR__ . '/DataStructure/Column.php');

// Data providers
require(__DIR__ . '/DataProviders/BaseDataProvider.php');
require(__DIR__ . '/DataProviders/BaseSQLDataProvider.php');
require(__DIR__ . '/DataProviders/MySQLDataProvider.php');
require(__DIR__ . '/DataProviders/MsSQLDataProvider.php');
require(__DIR__ . '/DataProviders/MsAccessDataProvider.php');

// Logic
require(__DIR__ . '/DataCopier.php');

// Helpers
require(__DIR__ . '/functions.php');

session_start();
if (!isset($_SESSION['verified']) || !$_SESSION['verified']) {
    header('Location: login.php');

    die();
}