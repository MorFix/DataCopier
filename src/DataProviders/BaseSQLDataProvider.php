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
     * BaseSQLDataProvider constructor.
     *
     * @param $host
     * @param $username
     * @param $password
     * @param $db
     */
    public function __construct($host, $username, $password, $db)
    {
        parent::__construct($username, $password);

        $this->_host = $host;
        $this->_db = $db;
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
     * @param Table $table - The table
     */
    private function InsertData($table) {
        if (empty($table->GetData())) {
            return;
        }

        $primary_keys = $this->GetConnection()->MetaPrimaryKeys($table->GetName());
        $rows = array();

        foreach($table->GetData() as $row) {
            $values = array();

            foreach ($row as $col => $value) {
                if (!$this->IsPrimaryInsertAllowed() && in_array($col, $primary_keys)) {
                    continue;
                }

                if ($this->IsIntColumn($col, $table) && (is_numeric($value) || is_null($value))) {
                    $values[] = !empty($value) ? $value : 0;
                } else {
                    $values[] = $this->PrepareColumnValueForInsert($value);
                }
            }

            $rows[] = '(' . implode(", ", $values) . ')';
        }

        $columns = '(' . implode(",", $this->GetColumnsForInsert($table)) . ')';

        $insert = "INSERT INTO " . $table->GetName() . ' ' . $columns . " VALUES " . implode(', ', $rows);
        $this->GetConnection()->Execute($insert);
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
     * Whether a column is an integer
     *
     * @param string $name - The column
     * @param Table $table - The table to check against
     *
     * @return bool
     */
    private function IsIntColumn($name, $table) {
        /**
         * @var ADODB_DataDict $dictionary
         */
        $dictionary = $this->GetDataDictionary();
        $uppername = strtoupper($name);
        $numbers = array('INT', 'INTEGER', 'BIGINT', 'TINYINT', 'SMALLINT', 'NUMERIC', 'DOUBLE');

        foreach ($table->GetColumns() as $col) {
            if (strtoupper($col->GetName()) === $uppername &&
                in_array($dictionary->ActualType($col->GetType()), $numbers)) {
                return true;
            }
        }

        return false;
    }
}