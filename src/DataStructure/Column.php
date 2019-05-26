<?php

class Column
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
     * @var bool $_notnull - Is not null
     */
    private $_notnull;

    /**
     * @var string $_type - The column data type
     */
    private $_type;

    /**
     * @var int $_length - The maximum length
     */
    private $_length;

    public function __construct($name, $type, $length = 255, $primary = false, $notnull = false)
    {
        $this->_name = $name;
        $this->_type = $type;
        $this->_length = $length;
        $this->_primary = $primary;
        $this->_notnull = $notnull;
    }

    /**
     * Whether the column is a primary key
     *
     * @return bool
     */
    public function IsPrimary() {
        return $this->_primary;
    }

    public function IsNotNull() {
        return $this->_notnull;
    }

    public function GetName() {
        return $this->_name;
    }

    public function GetType() {
        return $this->_type;
    }

    public function GetLength() {
        return $this->_length;
    }
}