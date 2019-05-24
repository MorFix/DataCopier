<?php

class TableData
{
    /**
     * @var string $_name - Table name
     */
    private $_name;

    /**
     * @var ColumnData[] $_columns - All table columns
     */
    private $_columns;

    /**+
     * @var array $_data - All table data
     */
    private $_data;

    /**
     * TableData constructor.
     *
     * @param $name
     * @param $columns
     * @param $data
     * @throws Exception - When there is more than one primary column
     */
    public function __construct($name, $columns, $data)
    {
        $this->_data = $data;

        $this->SetName($name);
        $this->SetColumns($columns);
    }

    /**
     * Set the columns in the table
     *
     * @param ColumnData[] $columns - The columns
     * @throws Exception - When there is more than one primary column
     */
    private function SetColumns($columns) {
        $has_primary = false;

        foreach ($columns as $column) {
            if ($column->IsPrimary()) {
                if (!$has_primary) {
                    $has_primary = true;
                } else {
                    throw new Exception("Cannot have more than one primary column");
                }
            }
        }

        $this->_columns = $columns;
    }

    /**
     * Gets the name
     *
     * @return string - The name
     */
    public function GetName() {
        return $this->_name;
    }

    /**
     * Sets a new name
     *
     * @param $name - The name
     */
    public function SetName($name) {
        $this->_name = $name;
    }

    /**
     * Gets the columns
     *
     * @return ColumnData[] - The columns
     */
    public function GetColumns() {
        return $this->_columns;
    }

    /**
     * Gets the table data
     *
     * @return array - The data
     */
    public function GetData() {
        return $this->_data;
    }
}