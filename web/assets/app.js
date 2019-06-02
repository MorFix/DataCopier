const SRC_FIELDS_PREFIX = 'src';
const DEST_FIELDS_PREFIX = 'dest';
const CHANGE_EVENT = 'change';
const DB = 'db';
const selectedRadios = {};

function maybeInitSourceTables() {
    const tablesContainer = document.getElementById('tables_container');
    if (!selectedRadios[SRC_FIELDS_PREFIX]) {
        tablesContainer.style.display = 'none';
        return;
    }

    tablesContainer.style.display = '';
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

function renderTables(tables, container) {
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
    const SOURCE_TABLES = 'source_tables';

    // May be a RadioNode, a RadioNodeList object or null
    const tablesValue = document.migrator[SOURCE_TABLES + '[]'] || [];

    // Is this an array/list ?
    const tables = typeof tablesValue[Symbol.iterator] === 'function' ? tablesValue : [tablesValue];

    const selectedSrcDbType = selectedRadios[SRC_FIELDS_PREFIX].value;
    const selectedDestDbType = selectedRadios[DEST_FIELDS_PREFIX].value;

    const srcDb = document.migrator[selectedSrcDbType + '_' + SRC_FIELDS_PREFIX + '_' + DB];
    const destDb = document.migrator[selectedDestDbType + '_' + DEST_FIELDS_PREFIX + '_' + DB];

    return {
        copy: true,
        src: selectedSrcDbType,
        dest: selectedDestDbType,
        src_db: (srcDb && srcDb.value) || '',
        dest_db: (destDb && destDb.value) || '',
        [SOURCE_TABLES]: Object.keys(tables)
            .filter(key => tables[key].checked)
            .map(key => tables[key].value)
    };
}

function sendData() {
    const resultContainer = document.getElementById('result');
    const processContainer = document.getElementById('copying');

    const xhr = new XMLHttpRequest();

    xhr.open("POST", 'process.php', true);
    xhr.setRequestHeader("Content-Type", "application/json; charset=UTF-8");
    xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE) {
            processContainer.style.display = 'none';

            try {
                const parsed = JSON.parse(this.responseText);
                resultContainer.innerHTML = this.status === 200 ? parsed : parsed.message;
            } catch (e) {
                resultContainer.innerHTML = 'Fatal error occurred';
            }
        }
    };

    resultContainer.innerHTML = '';
    processContainer.style.display = 'block';
    xhr.send(JSON.stringify(createFormBody()));
}

function initTables(source, db) {
    const container = document.getElementById('tables');

    let url = 'tables.php?' + SRC_FIELDS_PREFIX + '=' + encodeURIComponent(source);
    url += db ? '&' + SRC_FIELDS_PREFIX + '_' + DB + '=' + encodeURIComponent(db) : '';

    const xhr = new XMLHttpRequest();
    xhr.open("GET", url, true);
    xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE) {
            const parsed = JSON.parse(this.responseText);

            if (this.status === 200) {
                container.innerHTML = '';
                renderTables(parsed, container);
            } else {
                container.innerHTML = parsed.message;
            }
        }
    };

    container.innerHTML = 'Loading tables...';
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
    .map(x => document.migrator[x + '_' + SRC_FIELDS_PREFIX + '_' + DB])
    .forEach(x => x.addEventListener(CHANGE_EVENT, maybeInitSourceTables));