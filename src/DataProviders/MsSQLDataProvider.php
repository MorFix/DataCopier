<?php

class MsSQLDataProvider extends BaseSQLDataProvider implements IDataSource
{
    protected function GetAdoClassName()
    {
        return "mssqlnative";
    }

    /**
     * Creates the table drop statement
     *
     * @param Table $table
     * @return string
     */
    protected function GenerateDropStatement($table) {
        return $this->GetTableExistsCondition($table, true) . "DROP TABLE " . $table->GetName();
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
        $stmt = $this->GetTableExistsCondition($table, false) . "CREATE TABLE " . $table->GetName() . " (\n";

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
     * @param ADOFieldObject $col
     * @param ADODB_DataDict $data_dictionary
     * @return Column
     */
    protected function CreateColumn($col, $data_dictionary)
    {
        return new Column($col->name, $data_dictionary->MetaType($col->type), $col->max_length, $col->auto_increment, $col->not_null);
    }

    protected function PrepareColumnNameForInsert($col)
    {
        return $col;
    }

    protected function PrepareColumnValueForInsert($val)
    {
        $converted = iconv('UTF-8', 'windows-1255', $val);
        $converted = str_replace("'", "''", $converted);

        return "'$converted'";
    }

    protected function IsPrimaryInsertAllowed()
    {
        return false;
    }

    /**
     * Generates a statement to check for an existence of a table
     *
     * @param Table $table
     * @param bool $exists
     * @return string
     */
    private function GetTableExistsCondition($table, $exists = true) {
        return "IF ". (!$exists ? 'NOT' : '') ." EXISTS (SELECT * FROM sysobjects WHERE name='" . $table->GetName() . "' and xtype='U')\n";
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

        if ($type === "NVARCHAR") {
            $stmt .= "(255) ";
        }

        $stmt .= $column->IsNotNull() ? "NOT NULL " : "DEFAULT NULL ";
        if ($column->IsPrimary()) {
            $stmt .= "IDENTITY(1,1) PRIMARY KEY";
        }

        return $stmt;
    }
}