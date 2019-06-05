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
    }

    /**
     * Creates the specified table in the destination
     *
     * @param Table $table - The table data to create
     */
    public function CreateTable($table)
    {
        $this->GetConnection()->Execute($this->GenerateDropStatement($table));
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

        $primary_keys = $this->GetConnection()->MetaPrimaryKeys($table->GetName());
        $int_cols = $this->GetIntCols($table->GetColumns());
        $rows = array();

        foreach($table->GetData() as $row) {
            $row_values = '(';
            $last_col = array_keys($row)[count($row) - 1];

            foreach ($row as $col => $value) {
                if (!$this->IsPrimaryInsertAllowed() && in_array($col, $primary_keys)) {
                    continue;
                }

                if (in_array(strtoupper($col), $int_cols) && (is_numeric($value) || empty($value))) {
                    $row_values .= !empty($value) ? $value : '0';
                } else {
                    $row_values .= $this->PrepareColumnValueForInsert($value);
                }

                if ($col !== $last_col) {
                    $row_values .= ', ';
                }
            }

            $row_values .= ')';

            $rows[] = $row_values;
        }

        $this->InsertBatch($table, $rows);
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
     * Gets int columns
     *
     * @param Column[] $columns - The column
     *
     * @return array
     */
    private function GetIntCols($columns) {
        /**
         * @var ADODB_DataDict $dictionary
         */
        $dictionary = $this->GetDataDictionary();
        $numbers = array('INT', 'INTEGER', 'BIGINT', 'TINYINT', 'SMALLINT', 'NUMERIC', 'DOUBLE');

        $int_cols = array();

        foreach ($columns as $col) {
            if (in_array($dictionary->ActualType($col->GetType()), $numbers)) {
                $int_cols[] = strtoupper($col->GetName());
            }
        }

        return $int_cols;
    }

    /**
     * Insert batch of rows into the table
     *
     * @param Table $table
     * @param array $rows
     */
    private function InsertBatch($table, $rows) {
        $columns = '(' . implode(",", $this->GetColumnsForInsert($table)) . ')';

        $collected_rows = array();

        while ($collected_rows[] = array_shift($rows)) {
            if (count($rows) > 0 && count($collected_rows) !== $this->_insertBatch) {
                continue;
            }

            $insert = "INSERT INTO " . $table->GetName() . ' ' . $columns . " VALUES " . implode(', ', $collected_rows);
            $this->GetConnection()->Execute($insert);
            $collected_rows = array();
        }
    }
}