<?php

class MySQLDataProvider extends BaseSQLDataProvider
{
    /**
     * Get a DSN that could be used to open a PDO connection
     *
     * @return string - The DSN
     */
    protected function GetDSN()
    {
        return "mysql:host=" . $this->_host . ";dbname=" . $this->_dbname;
    }

    protected function GetAdoClassName()
    {
        return "mysqli";
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
}
