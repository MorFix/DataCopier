<?php

abstract class BaseSQLDataProvider extends BaseDataProvider implements IDataDestination
{
    /**
     * @var string $_host - The DB server
     */
    protected $_host;

    /**
     * @var string $_dbname - The database name
     */
    protected $_dbname;

    /**
     * BaseSQLDataProvider constructor.
     *
     * @param $host
     * @param $username
     * @param $password
     * @param $dbname
     */
    public function __construct($host, $username, $password, $dbname)
    {
        $this->_host = $host;
        $this->_dbname = $dbname;

        parent::__construct($username, $password);
    }

    protected function OpenAdoConnection()
    {
        /**
         * @var ADOConnection $db
         */
        $db = ADONewConnection($this->GetAdoClassName());
        $db->Connect($this->_host, $this->_username, $this->_password, $this->_dbname);

        return $db;
    }

    protected abstract function GetAdoClassName();

    protected function GetDataDictionary() {
        $con = $this->GetConnection();

        return NewDataDictionary($con, 'mysql');
    }

    /**
     * Creates the specified table in the destination
     *
     * @param Table $table - The table data to create
     */
    public function CreateTable($table)
    {
        $this->InsertData($table);
    }

    private function GenerateCreateStatement($table) {

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

        foreach($table->GetData() as $row) {

            $firstColumn = true;
            $cols = "";
            $vals = "";

            foreach ($row as $col => $value) {
                if (empty($value)) {
                    continue;
                }

                if (!$firstColumn) {
                    $cols .= ",";
                    $vals .= ",";
                }

                $firstColumn = false;
                $cols .= "`" . addslashes($col) . "`";

                if ($this->IsIntColumn($col, $table) && is_numeric($value)) {
                    $vals .= $value;
                } else {
                    $vals .= "\"" . addslashes($value) . "\"";
                }
            }

            $insert = "INSERT INTO " . $table->GetName() . " (" . $cols . ") VALUES (" . $vals . ")";
            $db->Execute($insert);
        }
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
        $numbers = array('INTEGER', 'BIGINT', 'TINYINT', 'SMALLINT', 'NUMERIC', 'DOUBLE');

        foreach ($table->GetColumns() as $col) {
            if (strtoupper($col->GetName()) === $uppername &&
                in_array($dictionary->ActualType($col->GetType()), $numbers)) {
                return true;
            }
        }

        return false;
    }
}