<?php

interface IDataDestination
{
    /**
     * Creates the specified table in the destination
     *
     * @param TableData $table - The table data to create
     */
    function CreateTable($table);
}