<?php

interface IDataSource
{
    /**
     * Gets all data of a specific table
     *
     * @param $name - The table name
     * @return TableData - The table data
     */
    function GetTable($name);
}