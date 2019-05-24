<?php

class MySQLDataProvider extends BaseSQLDataProvider
{
    /**
     * Get a DSN that could be used to open a PDO connection
     *
     * @return string - The DSN
     */
    protected function GetDSN()
    {
        return "mysql:host=" . $this->_host . ";dbname=" . $this->_dbname;
    }
}