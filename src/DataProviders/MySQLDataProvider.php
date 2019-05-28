<?php

class MySQLDataProvider extends BaseSQLDataProvider implements IDataSource
{
    protected function GetAdoClassName()
    {
        return "mysqli";
    }

    protected function MaybeCreateDatabase($db)
    {
        $this->GetConnection()->Execute("CREATE DATABASE IF NOT EXISTS " . $db);
    }

    /**
     * Generates a create table statement
     *
     * @param Table $table
     * @return string - The statement
     */
    protected function GenerateCreateStatement($table)
    {
        $columns = $table->GetColumns();
        $stmt = "CREATE TABLE IF NOT EXISTS `" . $table->GetName() . "` (\n";

        foreach ($columns as $index => $column) {
            $stmt .= $this->GenerateCreateColumn($column);

            if ($index !== sizeof($columns) - 1) {
                $stmt .= ",";
            }

            $stmt .= "\n";
        }

        $stmt .= ") ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";

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

        $stmt = "`" . $column->GetName() . "` ";
        $stmt .= $type . " ";
        if (!in_array($type, array("DATETIME", "LONGTEXT"))) {
            $stmt .= "(" . $column->GetLength() . ") ";
        }
        
        $stmt .= $column->IsNotNull() ? "NOT NULL " : "DEFAULT NULL ";
        if ($column->IsPrimary()) {
            $stmt .= "AUTO_INCREMENT PRIMARY KEY";
        }

        return $stmt;
    }

    protected function GetDataDictionary()
    {
        $con = $this->GetConnection();

        return NewDataDictionary($con, 'mysql');
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
            $columns[] = new Column($col->name, $dictionary->MetaType($col->type), $col->max_length, $col->primary_key, $col->not_null);
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
        return "`" . $col . "`";
    }

    protected function PrepareColumnValueForInsert($val)
    {
        return "\"" . $val . "\"";
    }

    protected function IsPrimaryInsertAllowed()
    {
        return true;
    }
}
