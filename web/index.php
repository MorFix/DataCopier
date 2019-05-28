<?php
require(__DIR__ . "/../vendor/autoload.php");
require(__DIR__ . "/../src/load.php");
?>

<html>
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" type="text/css" href="assets/style.css" />
    </head>

    <body>

    <div class="column center">
        <h3>Copy Data</h3>
        <form name="copyForm" class="column center">
            <div class="row margin">
                <label>From:</label>
                <div class="row">
                    <input type="radio" name="from" value="access"/>
                    <span>Access</span>

                    <input type="radio" name="from" value="mysql"/>
                    <span>MySQL</span>
                </div>
            </div>

            <div class="row margin" id="mysql_src_db_container">
                <label>Database:</label>
                <input type="text" name="mysql_src_db" value="<?= $_ENV["MYSQL_DBNAME"]; ?>" />
            </div>

            <div class="row margin" id="file_container">
                <label>File:</label>
                <input type="text" name="file" value="<?= $_ENV["ACCESSDB_FILE_PATH"]; ?>" />
            </div>

            <div class="row margin">
                <label>Tables:</label>
                <div id="tables" class="column">

                </div>
            </div>

            <div class="row margin">
                <label>To:</label>
                <div class="row">
                    <input type="radio" name="to" value="mysql" />
                    <span>MySQL</span>

                    <input type="radio" name="to" value="mssql" />
                    <span>MsSQL</span>
                </div>
            </div>

            <div class="row margin" id="mysql_dest_db_container">
                <label>Database:</label>
                <input type="text" name="mysql_dest_db" value="<?= $_ENV["MYSQL_DBNAME"]; ?>" />
            </div>

            <div class="row margin" id="mssql_dest_db_container">
                <label>Database:</label>
                <input type="text" name="mssql_dest_db" value="<?= $_ENV["MSSQL_DBNAME"]; ?>" />
            </div>

            <input type="submit" value="Copy" />

            <div class="margin" id="copying" style="display: none;">
                Copying...
            </div>

            <div class="margin" id="result"></div>
        </form>
    </div>

    </body>

    <script type="text/javascript" src="assets/app.js"></script>
</html>