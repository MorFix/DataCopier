<?php

class MySQLDataProvider extends BaseSQLDataProvider
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
     * Creates the table drop statement
     *
     * @param Table $table
     * @return string
     */
    protected function GenerateDropStatement($table) {
        return 'DROP TABLE IF EXISTS ' . $table->GetName();
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
     * @param ADOFieldObject $col
     * @param ADODB_DataDict $data_dictionary
     * @return Column
     */
    protected function CreateColumn($col, $data_dictionary)
    {
        return new Column($col->name, $data_dictionary->MetaType($col->type), $col->max_length, $col->primary_key, $col->not_null);
    }

    protected function GetDataDictionary()
    {
        $con = $this->GetConnection();

        return NewDataDictionary($con, 'mysql');
    }

    protected function PrepareColumnNameForInsert($col)
    {
        return "`" . $col . "`";
    }

    protected function PrepareColumnValueForInsert($val)
    {
        return "\"" . addslashes($val) . "\"";
    }

    protected function IsPrimaryInsertAllowed()
    {
        return true;
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

        // This because of row size issues
        if (in_array($type, array('VARCHAR', 'DATETIME'))) {
            $type = "LONGTEXT";
        }

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
}
