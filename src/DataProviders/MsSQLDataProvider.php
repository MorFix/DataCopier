<?php

class MsSQLDataProvider extends BaseSQLDataProvider
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