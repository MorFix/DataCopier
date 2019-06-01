<?php

class MsAccessDataProvider extends BaseDataProvider implements IDataSource
{
    /**
     * @var string $_filename - The path to the access file
     */
    private $_filename;

    public function __construct($filename, $username = '', $password = '')
    {
        parent::__construct($username, $password);

        $this->_filename = $filename;
    }

    public function Connect()
    {
        $this->GetConnection()->connect($this->GetDSN());
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

    /**
     * Gets all tables
     *
     * @return string[]
     */
    public function GetTablesNames()
    {
        return array_map('utf8_encode', $this->GetConnection()->MetaTables());
    }

    protected function GetAdoClassName()
    {
        return 'access';
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
         * @var ADOFieldObject[] $meta_cols
         */
        $meta_cols = $this->GetConnection()->MetaColumns($table);

        $primary = true;
        foreach ($meta_cols as $index => $col) {
            $columns[] = new Column($col->name, $col->type, $col->max_length, $primary, $primary);
            $primary = false;
        }

        return $columns;
    }

    /**
     * @param $table
     * @return array
     * @throws Exception
     */
    private function GetData($table) {
        /**
         * @var ADORecordSet $result
         */
        $result = $this->GetConnection()->Execute("SELECT * FROM " . $table);

        $data = array();
        while (!$result->EOF) {
            $row = get_object_vars($result->FetchObj());
            foreach ($row as $key => $col) {
                $row[$key] = iconv('windows-1255', 'utf-8', $col);
            }

            $data[] = $row;
            $result->MoveNext();
        }

        return $data;
    }

    private function GetDSN()
    {
        $driver = "Microsoft Access Driver (*.mdb)";
        $charset = "UTF-8";
        $dbq = $this->_filename;
        $uid = $this->_username;
        $pwd = $this->_password;

        return "Driver={" . $driver . "};charset=" . $charset . "; DBQ=" . $dbq . "; Uid=" . $uid . "; Pwd= ". $pwd .";";
    }
}