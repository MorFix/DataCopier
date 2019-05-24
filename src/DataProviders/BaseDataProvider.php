<?php

use ParagonIE\EasyDB\Factory;
use ParagonIE\EasyDB\EasyDB;

abstract class BaseDataProvider
{
    /**
     * @var EasyDB $_con
     */
    private $_con;

    /**
     * @var string $_username - The data provider username
     */
    protected $_username;

    /**
     * @var string $_password - The data provider password
     */
    protected $_password;

    /**
     * BaseDataProvider constructor.
     *
     * @param $username
     * @param $password
     */
    public function __construct($username, $password)
    {
        $this->_username = $username;
        $this->_password = $password;

        $this->_con = Factory::create($this->GetDSN(), $this->_username, $this->_password);
    }

    protected function GetConnection() {
        return $this->_con;
    }

    /**
     * Get a DSN that could be used to open a PDO connection
     *
     * @return string - The DSN
     */
    protected abstract function GetDSN();
}