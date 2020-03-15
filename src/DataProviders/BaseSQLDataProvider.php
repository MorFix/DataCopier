<?php

abstract class BaseSQLDataProvider extends BaseDataProvider implements IDataSource, IDataDestination
{
    /**
     * @var string $_host - The DB server
     */
    protected $_host;

    /**
     * @var string $_db - The Database name
     */
    protected $_db;

    /**
     * @var int $_insertBatch - How many rows should be inserted at once
     */
    private $_insertBatch;

    /**
     * BaseSQLDataProvider constructor.
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $db
     * @param int $insertBatch
     */
    public function __construct($host, $username, $password, $db, $insertBatch = 100)
    {
        parent::__construct($username, $password);

        $this->_host = $host;
        $this->_db = $db;
        $this->_insertBatch = $insertBatch;
    }

    /**
     * Gets all tables
     *
     * @return string[]
     */
    public function GetTablesNames()
    {
        return $this->GetConnection()->MetaTables();
    }

    /**
     * Gets all data of a specific table
     *
     * @param $name - The table name
     * @return Table - The table data
     * @throws Exception
     */
    public function GetTable($name)
    {
        return new Table($name, $this->GetColumns($name), $this->GetData($name));
    }

    public function Connect()
    {
        $this->GetConnection()->Connect($this->_host, $this->_username, $this->_password);
        $this->MaybeCreateDatabase($this->_db);
        $this->GetConnection()->SelectDB($this->_db);
        $this->GetConnection()->SetCharSet('UTF8');
    }

    /**
     * Creates the specified table in the destination
     *
     * @param Table $table - The table data to create
     * @param bool $drop - Should the table be dropped first
     */
    public function CreateTable($table, $drop = true)
    {
        if ($drop) {
            $this->GetConnection()->Execute($this->GenerateDropStatement($table));
        }

        $this->GetConnection()->Execute($this->GenerateCreateStatement($table));

        $this->InsertData($table);
    }

    /**
     * Creating the database if it doesn't exist
     *
     * @param $db
     * @return bool
     */
    protected abstract function MaybeCreateDatabase($db);

    protected abstract function GenerateDropStatement($table);

    protected abstract function GenerateCreateStatement($table);

    protected abstract function GetDataDictionary();

    /**
     * @param ADOFieldObject $col
     * @param ADODB_DataDict $data_dictionary
     * @return Column
     */
    protected abstract function CreateColumn($col, $data_dictionary);

    protected abstract function IsPrimaryInsertAllowed();

    protected abstract function PrepareColumnNameForInsert($col);

    protected abstract function PrepareColumnValueForInsert($val);

    /**
     * Gets the table's columns metadata
     *
     * @param string $table The table name
     * @return Column[] - All columns metadata
     */
    private function GetColumns($table) {
        $dictionary = $this->GetDataDictionary();
        $columns = [];

        foreach ($this->GetConnection()->MetaColumns($table) as $col) {
            $columns[] = $this->CreateColumn($col, $dictionary);
        }

        return $columns;
    }

    /**
     * @param $table
     * @return array
     */
    private function GetData($table) {
        /**
         * @var ADORecordSet $res
         */
        $res = $this->GetConnection()->Execute("SELECT * FROM " . $table);

        $data = array();
        while (!$res->EOF) {
            $data[] = get_object_vars($res->FetchObj());
            $res->MoveNext();
        }

        return $data;
    }

    /**
     * Insert a table's data to the data source
     *
     * This was written with as many resource saves as possible
     *
     * @param Table $table - The table
     */
    private function InsertData($table) {
        if (empty($table->GetData())) {
            return;
        }

        $batch_start = 0;
        $data_batch = array_slice($table->GetData(), $batch_start, $this->_insertBatch);

        while (!empty($data_batch)) {
            $this->InsertBatch($table, $data_batch);

            $batch_start += $this->_insertBatch;
            $data_batch = array_slice($table->GetData(), $batch_start, $this->_insertBatch);
        }
    }

    /**
     * Insert a batch of rows into the table
     *
     * @param Table $table - The table
     * @param array $data_batch - The batch of the rows
     */
    private function InsertBatch($table, $data_batch) {
        $columns = '(' . implode(",", $this->GetColumnsForInsert($table)) . ')';
        $primary_keys = $this->GetConnection()->MetaPrimaryKeys($table->GetName());
        $rows = array();

        foreach($data_batch as $row) {
            if (!$this->IsPrimaryInsertAllowed()) {
                $row = array_filter($row, function ($col_name) use ($primary_keys) {
                    return !in_array($col_name, $primary_keys);
                }, ARRAY_FILTER_USE_KEY);
            }

            $row_data = $this->GetRowData($row, $table->GetColumns());
            $rows[] = "(" . implode(',', $row_data) . ")";
        }

        $insert = "INSERT INTO " . $table->GetName() . ' ' . $columns . " VALUES " . implode(', ', $rows);
        $this->GetConnection()->Execute($insert);
    }

    /**
     * Sanitizes the data for all values in a specific row
     *
     * @param array $row - The row raw data
     * @param Column[] $columns - All Columns in the table
     *
     * @return array - The sanitized row data
     */
    private function GetRowData($row, $columns) {
        $row_data = array();

        foreach ($row as $column_name => $column_value) {
            $upper_col = strtoupper($column_name);
            $column = array_values(array_filter($columns, function ($current_col) use ($upper_col) {
                return strtoupper($current_col->GetName()) === strtoupper($upper_col);
            }))[0];

            $row_data[$column_name] = $this->GetColumnValue($column, $column_value);
        }

        return $row_data;
    }

    /**
     * Sanitizes a column value
     *
     * @param Column $column - The column
     * @param * $value - The column value

     * @return string|int - The column value
     */
    private function GetColumnValue($column, $value)
    {
        if ($this->IsIntColumn($column)) {
            if (empty($value)) {
                return '0';
            }

            if (is_numeric($value)) {
                return $value;
            }
        }

        return $this->PrepareColumnValueForInsert($value);
    }

    /**
     * Get the columns that should be used in the insert statement
     *
     * @param Table $table
     * @return string[]
     */
    private function GetColumnsForInsert($table) {
        $cols = array();

        foreach ($table->GetColumns() as $col) {
            if (!$this->IsPrimaryInsertAllowed() && $col->IsPrimary()) {
                continue;
            }

            $cols[] = $this->PrepareColumnNameForInsert(addslashes($col->GetName()));
        }

        return $cols;
    }

    /**
     * Check whether is numeric column
     *
     * @param Column $column - The column
     *
     * @return bool
     */
    private function IsIntColumn($column) {
        /**
         * @var ADODB_DataDict $dictionary
         */
        $dictionary = $this->GetDataDictionary();
        $numbers = array('INT', 'INTEGER', 'BIGINT', 'TINYINT', 'SMALLINT', 'NUMERIC', 'DOUBLE');

        return in_array($dictionary->ActualType($column->GetType()), $numbers);
    }
}