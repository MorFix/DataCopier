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

    /**
     * Creates the specified table in the destination
     *
     * @param TableData $table - The table data to create
     */
    function CreateTable($table)
    {
        // TODO: Implement CreateTable() method.
    }
}