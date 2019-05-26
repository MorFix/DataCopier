<?php
require(__DIR__ . "/../vendor/autoload.php");
require(__DIR__ . "/../src/load.php");
$p = new MsAccessDataProvider($_ENV["ACCESSDB_FILE_PATH"]);
$t = $p->GetTablesNames();
?>

<html>
    <head>
        <meta charset="utf-8" />

        <style type="text/css">
            body {
                font-family: Tahoma, sans-serif;
            }

            label {
                font-weight: bold;
            }

            .row {
                display: flex;
                flex-direction: row;
            }

            .column {
                display: flex;
                flex-direction: column;
            }

            .center {
                align-items: center;
                justify-content: center;
            }

            .margin {
                margin: 10px;
            }
        </style>
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
                </div>
            </div>

            <div class="row margin" id="file_container">
                <label for="file">File:</label>
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
                </div>
            </div>

            <input type="submit" value="Copy" />

            <div class="margin" id="copying" style="display: none;">
                Copying...
            </div>

            <div class="margin" id="result"></div>
        </form>
    </div>

    </body>

    <script type="text/javascript">
        function maybeInitTables() {
            const isAccess = fromPrev && fromPrev.value === 'access';
            if (fromPrev && (!isAccess || document.copyForm.file.value)) {
                initTables(fromPrev.value, isAccess ? document.copyForm.file.value : '');
            }
        }
        
        function renderTables(tables) {
            const createTag = name => {
              const div = document.createElement('div');
              const elem = document.createElement('input');
              elem.name = 'source_tables[]';
              elem.value = name;
              elem.type = 'checkbox';

              const span = document.createElement('span');
              span.innerText = name;

              div.appendChild(elem);
              div.appendChild(span);

              return div;
            };

            const container = document.getElementById('tables');
            container.innerHTML = '';
            tables.map(createTag).forEach(x => container.appendChild(x))
        }

        function handleRadioChange() {
            maybeInitTables();

            document.getElementById('file_container').style.display = fromPrev && fromPrev.value === 'access'
                ? ''
                : 'none';
        }

        function registerRadioChange(elem, cb) {
           if (!elem.length) {
               elem.addEventListener('change', cb);

               return;
           }

            for (let i = 0; i < elem.length; i++) {
                elem[i].addEventListener('change', cb);
            }
        }

        function sendData() {
            document.getElementById('copying').style.display = 'block';

            const xhr = new XMLHttpRequest();
            xhr.open("POST", '/process.php', true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                    const parsed = JSON.parse(this.responseText);

                    document.getElementById('result').innerHTML = parsed;
                }

                document.getElementById('copying').style.display = 'none';
            };

            const tables = document.copyForm["source_tables[]"];
            const details = {
                from: fromPrev.value,
                to: toPrev.value,
                file: document.copyForm.file.value,
                copy: true,
                "source_tables[]": Object.keys(tables)
                                         .filter(key => tables[key].checked)
                                         .map(key => tables[key].value)
            };

            const formBody = Object.keys(details)
                .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(details[key]))
                .join('&');

            xhr.send(formBody);
        }

        function initTables(source, file) {
            let url = '/tables.php?source=' + encodeURIComponent(source);
            url += file ? '&file=' + encodeURIComponent(file) : '';

            const xhr = new XMLHttpRequest();
            xhr.open("GET", url, true);
            xhr.onreadystatechange = function() {
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                    const tables = JSON.parse(this.responseText);

                    renderTables(tables);
                }
            };

            xhr.send();
        }

        let fromPrev = null;
        let toPrev = null;
        registerRadioChange(document.copyForm.from, function() {
            if (this !== fromPrev) {
                fromPrev = this;
            }

            handleRadioChange();
        });

        registerRadioChange(document.copyForm.to, function() {
            if (this !== toPrev) {
                toPrev = this;
            }
        });

        handleRadioChange();

        document.copyForm.file.addEventListener('change', function () {
            maybeInitTables();
        });
        
        document.copyForm.addEventListener('submit', function(event) {
            event.preventDefault();

            if (!fromPrev || !toPrev ||
                (fromPrev.value === 'access' && !document.copyForm.file.value)) {
                alert ("Please fill all fields");

                return;
            }

            sendData();
        })
    </script>
</html>