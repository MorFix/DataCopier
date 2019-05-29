<?php

class MsSQLDataProvider extends BaseSQLDataProvider implements IDataSource
{
    protected function GetAdoClassName()
    {
        return "mssqlnative";
    }

    /**
     * Creates the table creation statement
     *
     * @param Table $table
     * @return string
     */
    protected function GenerateCreateStatement($table)
    {
        $columns = $table->GetColumns();
        $stmt = "IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='" . $table->GetName() . "' and xtype='U')\n";
        $stmt .= "CREATE TABLE " . $table->GetName() . " (\n";

        foreach ($columns as $index => $column) {
            $stmt .= $this->GenerateCreateColumn($column);

            if ($index !== sizeof($columns) - 1) {
                $stmt .= ",";
            }

            $stmt .= "\n";
        }

        $stmt .= ")";

        return $stmt;
    }

    /**
     * Generate a statement partial for column creation
     *
     * @param Column $column
     * @return string
     */
    private function GenerateCreateColumn($column)
    {
        /**
         * @var ADODB_DataDict $dictionary
         */
        $dictionary = $this->GetDataDictionary();
        $type = $dictionary->ActualType($column->GetType());

        $type = $type === "VARCHAR" ? "NVARCHAR" : $type; // To support UTF-8

        $stmt = $column->GetName() . " " . $type . " ";

        if (in_array($type, array("NVARCHAR"))) {
            $stmt .= "(" . $column->GetLength() . ") ";
        }

        $stmt .= $column->IsNotNull() ? "NOT NULL " : "DEFAULT NULL ";
        if ($column->IsPrimary()) {
            $stmt .= "IDENTITY(1,1) PRIMARY KEY";
        }

        return $stmt;
    }

    protected function GetDataDictionary()
    {
        $con = $this->GetConnection();

        return NewDataDictionary($con, 'mssql');
    }

    protected function MaybeCreateDatabase($db)
    {
        $this->GetConnection()->Execute("IF DB_ID('" . $db . "') IS NULL\nCREATE DATABASE " . $db . " COLLATE Hebrew_CI_AS");
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
     * Gets all tables
     *
     * @return string[]
     */
    function GetTablesNames()
    {
        return $this->GetConnection()->MetaTables();
    }

    /**
     * Gets the table's columns metadata
     *
     * @param string $table The table name
     * @return Column[] - All columns metadata
     */
    private function GetColumns($table) {
        /**
         * @var ADODB_DataDict $dictionary
         */
        $dictionary = $this->GetDataDictionary();
        $columns = [];

        foreach ($this->GetConnection()->MetaColumns($table) as $col) {
            $columns[] = new Column($col->name, $dictionary->MetaType($col->type), $col->max_length, $col->auto_increment, $col->not_null);
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
         * @var ADORecordSet $res
         */
        $res = $this->GetConnection()->Execute("SELECT * FROM " . $table);
        if (!$res) {
            throw new Exception("Cannot get table data: " . $table);
        }


        $data = array();
        while (!$res->EOF) {
            $data[] = get_object_vars($res->FetchObj());
            $res->MoveNext();
        }

        return $data;
    }

    protected function PrepareColumnNameForInsert($col)
    {
        return $col;
    }

    protected function PrepareColumnValueForInsert($val)
    {
        return "'" . iconv('UTF-8', 'windows-1255', $val) . "'";
    }

    protected function IsPrimaryInsertAllowed()
    {
        return false;
    }
}