<?php

include_once dirname(__FILE__) . '/MySQL.php';

Class Model {

    private static $myMySQL = NULL;
    private $DATAHOST = "127.0.0.1";
    private $DATAUSER = "root";
    private $DATAPASS = "";
    private $DATACHARSET = "UTF8";
    private $DATANAME = "test";      // 基础库

    public $db = null;


    public function __construct() {
        
        if (self::$myMySQL == NULL) {
            self::$myMySQL = new MySQL($this->DATAHOST, $this->DATAUSER, $this->DATAPASS, $this->DATANAME, $this->DATACHARSET);
        }

        $this->db = self::$myMySQL;
    }

    public function setDBName($DBName)
    {
        return self::$myMySQL->selectDatabase($this->$DBName);
    }

}

?>