<?php

abstract class BaseSQLDataProvider extends BaseDataProvider implements IDataDestination
{
    /**
     * @var string $_host - The DB server
     */
    protected $_host;

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
        $this->_host = $host;

        parent::__construct($username, $password);

        $this->MaybeCreateDatabase($db);
        $this->GetConnection()->SelectDB($db);
    }

    protected function OpenAdoConnection()
    {
        /**
         * @var ADOConnection $con
         */
        $con = ADONewConnection($this->GetAdoClassName());
        $con->Connect($this->_host, $this->_username, $this->_password);

        return $con;
    }

    protected abstract function GetAdoClassName();

    protected abstract function MaybeCreateDatabase($db);

    protected abstract function GenerateCreateStatement($table);

    protected abstract function GetDataDictionary();

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
     * Insert a table's data to the datasource
     *
     * @param Table $table - The table
     */
    private function InsertData($table) {
        if (empty($table->GetData())) {
            return;
        }

        $db = $this->GetConnection();
        $primary_keys = $this->GetConnection()->MetaPrimaryKeys($table->GetName());
        $primary_keys = is_array($primary_keys) ? $primary_keys : array();

        foreach($table->GetData() as $row) {

            $firstColumn = true;
            $cols = "";
            $vals = "";

            foreach ($row as $col => $value) {
                if (empty($value) ||
                    (!$this->IsPrimaryInsertAllowed() && in_array($col, $primary_keys))) {
                    continue;
                }

                if (!$firstColumn) {
                    $cols .= ",";
                    $vals .= ",";
                }

                $firstColumn = false;
                $cols .= $this->PrepareColumnNameForInsert(addslashes($col));

                if ($this->IsIntColumn($col, $table) && is_numeric($value)) {
                    $vals .= $value;
                } else {
                    $vals .= $this->PrepareColumnValueForInsert(addslashes($value));
                }
            }

            $insert = "INSERT INTO " . $table->GetName() . " (" . $cols . ") VALUES (" . $vals . ")";
            $db->Execute($insert);
        }
    }

    protected abstract function IsPrimaryInsertAllowed();

    protected abstract function PrepareColumnNameForInsert($col);

    protected abstract function PrepareColumnValueForInsert($val);

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