<?php

class MsAccessDataProvider extends BaseDataProvider implements IDataSource
{
    /**
     * @var string $_filename - The path to the access file
     */
    private $_filename;

    public function __construct($filename, $username = '', $password = '')
    {
        $this->_filename = $filename;

        parent::__construct($username, $password);
    }

    public function GetDSN()
    {
        $driver = "Microsoft Access Driver (*.mdb)";
        $charset = "UTF-8";
        $dbq = $this->_filename;
        $uid = $this->_username;
        $pwd = $this->_password;

        return "Driver={" . $driver . "};charset=" . $charset . "; DBQ=" . $dbq . "; Uid=" . $uid . "; Pwd= ". $pwd .";";
    }

    /**
     * Gets all data of a specific table
     *
     * @param $name - The table name
     * @return Table - The table data
     * @throws Exception
     */
    function GetTable($name)
    {
        return new Table($name, $this->GetColumns($name), $this->GetData($name));
    }

    /**
     * Gets the table's columns metadata
     *
     * @param string $table The table name
     * @return Column[] - All columns metadata
     */
    private function GetColumns($table) {
        $columns = [];

        /**
         * @var ADOFieldObject[] $cols
         */
        $cols = $this->GetConnection()->MetaColumns($table);

        $primary = true;
        foreach ($cols as $index => $col) {
            $columns[] = new Column($col->name, $col->type, $col->max_length, $primary, false);
            $primary = false;
        }

        return $columns;
    }

    private function GetData($table) {
        /**
         * @var ADORecordSet $res
         */
        $res = $this->GetConnection()->Execute("SELECT * FROM " . $table);

        $data = array();
        while (!$res->EOF) {
            $row = get_object_vars($res->FetchObj());
            foreach ($row as $key => $col) {
                $row[$key] = iconv('windows-1255', 'utf-8', $col);
            }

            $data[] = $row;
            $res->MoveNext();
        }

        return $data;
    }

    /**
     * Gets the connection object
     *
     * @return ADOConnection - The connection
     */
    protected function OpenAdoConnection()
    {
        /**
         * @var ADOConnection $db;
         */
        $db = ADONewConnection("access");
        $db->connect($this->GetDSN());

        return $db;
    }
}