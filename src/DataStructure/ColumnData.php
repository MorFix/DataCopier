<?php

class ColumnData
{
    /**
     * @var string $_name - Column name
     */
    private $_name;

    /**
     * @var bool $_primary - Is primary column
     */
    private $_primary;

    /**
     * @var string $_type - The column data type
     */
    private $_type;


    public function __construct($name, $primary, $type)
    {
        $this->_name = $name;
        $this->_primary = $primary;
        $this->_type = $type;
    }

    /**
     * Whether the column is a primary key
     *
     * @return bool
     */
    public function IsPrimary() {
        return $this->_primary;
    }
}