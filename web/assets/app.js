function maybeInitTables() {
    if (!fromPrev) {
        return;
    }

    let db;
    switch (fromPrev.value) {
        case 'access':
            db = document.copyForm.file.value;
            break;
        case 'mysql':
        case 'mssql':
            db = document.copyForm[fromPrev.value + '_src_db'].value;
            break;
    }

    initTables(fromPrev.value, db);
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

function handleFromChange() {
    maybeInitTables();
    const dbs = ['mysql', 'mssql'];

    dbs.forEach(x => {
        const elem = document.getElementById(x + '_src_db_container');
        if (!elem) {
            return;
        }

        elem.style.display = x === (fromPrev && fromPrev.value) ? '' : 'none';
    });

    document.getElementById('file_container').style.display = fromPrev && fromPrev.value === 'access'
        ? ''
        : 'none';
}

function handleToChange() {
    const dbs = ['mysql', 'mssql'];

    dbs.forEach(x => {
        const elem = document.getElementById(x + '_dest_db_container');
        if (!elem) {
            return;
        }

        elem.style.display = x === (toPrev && toPrev.value) ? '' : 'none';
    });
}

function handleRadioChange() {
    handleFromChange();
    handleToChange();
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
    const resultContainer = document.getElementById('result');
    const processContainer = document.getElementById('copying');

    const xhr = new XMLHttpRequest();
    xhr.open("POST", 'process.php', true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE) {
            let result;
            try {
                result = JSON.parse(this.responseText);
            } catch {
                result = this.responseText || "An error has occured";
            }

            resultContainer.innerHTML = result;
            processContainer.style.display = 'none';
        }
    };

    const tables = document.copyForm["source_tables[]"] || [];
    const details = {
        from: fromPrev.value,
        to: toPrev.value,
        file: document.copyForm.file.value,
        src_db: document.copyForm[fromPrev.value + '_src_db'] ? document.copyForm[fromPrev.value + '_src_db'].value : '',
        dest_db: document.copyForm[toPrev.value + '_dest_db'] ? document.copyForm[toPrev.value + '_dest_db'].value : '',
        copy: true,
        "source_tables[]": Object.keys(tables)
            .filter(key => tables[key].checked)
            .map(key => tables[key].value)
    };

    const formBody = Object.keys(details)
        .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(details[key]))
        .join('&');

    resultContainer.innerHTML = '';
    processContainer.style.display = 'block';
    xhr.send(formBody);
}

function initTables(source, db) {
    let url = 'tables.php?source=' + encodeURIComponent(source);
    url += db ? '&src_db=' + encodeURIComponent(db) : '';

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

    handleFromChange();
});

registerRadioChange(document.copyForm.to, function() {
    if (this !== toPrev) {
        toPrev = this;
    }

    handleToChange();
});

handleRadioChange();

document.copyForm.file.addEventListener('change', function () {
    maybeInitTables();
});

document.copyForm.addEventListener('submit', function(event) {
    event.preventDefault();
    const dbs = ['mysql', 'mssql'];

    if (!fromPrev || !toPrev ||
        (fromPrev.value === 'access' && !document.copyForm.file.value) ||
        (dbs.includes(fromPrev.value) && !document.copyForm[fromPrev.value + '_src_db'].value) ||
        (dbs.includes(toPrev.value) && !document.copyForm[toPrev.value + '_dest_db'].value)) {
        alert ("Please fill all fields");

        return;
    }

    sendData();
});