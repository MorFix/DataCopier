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

        $this->_con = ADONewConnection($this->GetAdoClassName());
        $this->_con->SetCharset("UTF8");
        $this->_con->raiseErrorFn = array($this, 'HandleError');
    }

    /**
     * Opens the connection
     */
    public abstract function Connect();

    protected function GetConnection() {
        return $this->_con;
    }

    /**
     * Gets ADO Class name
     *
     * @return string
     */
    protected abstract function GetAdoClassName();

    /**
     * @param $dbms
     * @param $fn
     * @param $errno
     * @param $errmsg
     * @param $p1
     * @param $p2
     * @throws Exception
     */
    public function HandleError($dbms, $fn, $errno, $errmsg, $p1, $p2)
    {
        if (error_reporting() == 0) {
            return;
        }

        switch($fn) {
            case 'EXECUTE':
                $sql = $p1;
                $message = "$dbms error: [$errno: $errmsg] in $fn => $sql \n";
                break;

            case 'PCONNECT':
            case 'CONNECT':
                $host = $p1;
                $database = $p2;

                $message = "$dbms error: [$errno: $errmsg] in $fn($host, '****', '****', $database)\n";
                break;
            default:
                $message = "$dbms error: [$errno: $errmsg] in $fn($p1, $p2)\n";
                break;
        }

        error_log("(" . date('Y-m-d H:i:s') . ") $message");

        throw new Exception(htmlspecialchars($message));
    }

    public function __destruct()
    {
        $con = $this->GetConnection();
        if (!empty($con)) {
            $con->Close();
        }
    }
}