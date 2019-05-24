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
        $driver = "{Microsoft Access Driver (*.mdb)}";
        $charset = "UTF-8";
        $dbq = $this->_filename;
        $uid = $this->_username;
        $pwd = $this->_password;

        return "odbc:DRIVER=" . $driver . ";charset=" . $charset . "; DBQ=" . $dbq . "; Uid=" . $uid . "; Pwd= ". $pwd .";";
    }

    /**
     * Gets all data of a specific table
     *
     * @param $name - The table name
     * @return TableData - The table data
     */
    function GetTable($name)
    {
        // TODO: Implement GetTable() method.
    }
}