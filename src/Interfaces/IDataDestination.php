<?php

interface IDataDestination
{
    /**
     * Creates the specified table in the destination
     *
     * @param Table $table - The table data to create
     * @param bool $drop - Should the table be dropped first
     */
    function CreateTable($table, $drop = true);
}