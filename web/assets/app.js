const SRC_FIELDS_PREFIX = 'src';
const DEST_FIELDS_PREFIX = 'dest';
const CHANGE_EVENT = 'change';
const DB = 'db';
const selectedRadios = {};

function maybeInitSourceTables() {
    if (!selectedRadios[SRC_FIELDS_PREFIX]) {
        return;
    }

    const selectedRadio = selectedRadios[SRC_FIELDS_PREFIX];

    initTables(selectedRadio.value, document.migrator[selectedRadio.value + '_' + SRC_FIELDS_PREFIX + '_' + DB].value);
}

function createTableTag(name) {
    const div = document.createElement('div');
    const checkbox = document.createElement('input');
    const span = document.createElement('span');

    Object.assign(checkbox, {
        type: 'checkbox',
        name: 'source_tables[]',
        value: name
    });
    span.innerText = name;

    div.appendChild(checkbox);
    div.appendChild(span);

    return div;
}

function renderTables(tables) {
    const container = document.getElementById('tables');
    container.innerHTML = '';
    tables.map(createTableTag).forEach(x => container.appendChild(x))
}

function getRadioValues(radioType) {
    return Array.from(document.migrator[radioType]).map(x => x.value)
}

function handleDbRadioChange(type) {
    getRadioValues(type)
        .forEach(x => {
            const dbnameContainer = document.getElementById(x + '_' + type + '_' + DB + '_container');
            if (!dbnameContainer) {
                return;
            }

            dbnameContainer.style.display = x === document.migrator[type].value ? '' : 'none';
        });
}

function triggerAllChangeEvents() {
    maybeInitSourceTables();
    handleDbRadioChange(SRC_FIELDS_PREFIX);
    handleDbRadioChange(DEST_FIELDS_PREFIX);
}

function registerRadioChange(dbType, cb) {
    const elem = document.migrator[dbType];
    const listener = function () {
        if (this !== selectedRadios[dbType]) {
            selectedRadios[dbType] = this;
        }

        cb();
    };

    if (!elem.length) {
        elem.addEventListener(CHANGE_EVENT, listener);

        return;
    }

    for (let i = 0; i < elem.length; i++) {
        elem[i].addEventListener(CHANGE_EVENT, listener);
    }
}

function createFormBody() {
    const SOURCE_TABLES = 'source_tables[]';

    const tables = document.migrator[SOURCE_TABLES] || [];

    const selectedSrcDbType = selectedRadios[SRC_FIELDS_PREFIX].value;
    const selectedDestDbType = selectedRadios[DEST_FIELDS_PREFIX].value;

    const srcDb = document.migrator[selectedSrcDbType.value + '_' + SRC_FIELDS_PREFIX + '_' + DB];
    const destDb = document.migrator[selectedDestDbType + '_' + DEST_FIELDS_PREFIX + '_' + DB];

    const details = {
        copy: true,
        src: selectedSrcDbType,
        dest: selectedDestDbType,
        src_db: (srcDb && srcDb.value) || '',
        dest_db: (destDb && destDb.value) || '',
        [SOURCE_TABLES]: Object.keys(tables)
            .filter(key => tables[key].checked)
            .map(key => tables[key].value)
    };

    return Object.keys(details)
        .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(details[key]))
        .join('&');
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

    resultContainer.innerHTML = '';
    processContainer.style.display = 'block';
    xhr.send(createFormBody());
}

function initTables(source, db) {
    let url = 'tables.php?' + SRC_FIELDS_PREFIX + '=' + encodeURIComponent(source);
    url += db ? '&' + SRC_FIELDS_PREFIX + '_' + DB + '=' + encodeURIComponent(db) : '';

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

function isSectionValid(dbType) {
    return selectedRadios[dbType] &&
        getRadioValues(dbType).includes(selectedRadios[dbType].value) &&
        !!document.migrator[selectedRadios[dbType].value + '_' + dbType + '_' + DB].value;
}

triggerAllChangeEvents();

document.migrator.addEventListener('submit', function(event) {
    event.preventDefault();

    if (!isSectionValid(SRC_FIELDS_PREFIX) || !isSectionValid(DEST_FIELDS_PREFIX)) {
        alert ("Please fill all fields");

        return;
    }

    sendData();
});

registerRadioChange(SRC_FIELDS_PREFIX, () => {
    maybeInitSourceTables();
    handleDbRadioChange(SRC_FIELDS_PREFIX)
});

registerRadioChange(DEST_FIELDS_PREFIX, () => handleDbRadioChange(DEST_FIELDS_PREFIX));

getRadioValues(SRC_FIELDS_PREFIX)
    .map(x => document.migrator[x + '_' + SRC_FIELDS_PREFIX + '_db'])
    .forEach(x => x.addEventListener(CHANGE_EVENT, maybeInitSourceTables));