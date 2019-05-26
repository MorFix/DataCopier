<?php

class MsSQLDataProvider extends BaseSQLDataProvider
{
    /**
     * Get a DSN that could be used to open a PDO connection
     *
     * @return string - The DSN
     */
    protected function GetDSN()
    {
        //$mssqldriver = '{SQL Server}';
        //$mssqldriver = '{ODBC Driver 11 for SQL Server}';
        $driver = '{SQL Server Native Client 11.0}';

        return "odbc:Driver=" . $driver . ";Server=" . $this->_host . ";Database=" . $this->_dbname;
    }

    protected function GetAdoClassName()
    {
        return "mssqlnative";
    }
}