<?php

abstract class BaseDataProvider
{
    /**
     * @var ADOConnection $_con
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

        $this->_con = $this->OpenAdoConnection();
        $this->_con->SetCharset("UTF8");
    }

    protected function GetConnection() {
        return $this->_con;
    }

    /**
     * Gets the connection object
     *
     * @return ADOConnection - The connection
     */
    protected abstract function OpenAdoConnection();

    public function __destruct()
    {
        $this->_con->Close();
    }
}