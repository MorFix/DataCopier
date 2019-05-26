<?php

interface IDataSource
{
    /**
     * Gets all data of a specific table
     *
     * @param $name - The table name
     * @return Table - The table data
     * @throws Exception - When cannot create the Table object
     */
    function GetTable($name);

    /**
     * Gets all tables
     *
     * @return string[]
     */
    function GetTablesNames();
}