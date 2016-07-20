<?php

class MySQL {

    private $connection = 0;
    private $queryID = 0;
    private $record = array();
    private $sql;
    public static $sqls = array();
    private $errorMessage = "";
    private $errorCode = 0;
    private $usePconnect = 1;

    public function __construct($server, $user, $password, $database, $charset = 'UTF8') {

        if ($password != "") {
            if ($this->usePconnect == 1) {
                $this->connection = mysql_pconnect($server, $user, $password);
            } else {
                $this->connection = mysql_connect($server, $user, $password);
            }
        } else {
            if ($this->usePconnect == 1) {
                $this->connection = mysql_pconnect($server, $user);
            } else {
                $this->connection = mysql_connect($server, $user);
            }
        }

        if (!$this->connection) {
            //echo mysql_errno() . ": " . mysql_error() . "\n";
        }

        $this->database = $database;
        if (!mysql_select_db($database, $this->connection)) {
            //echo mysql_errno() . ": " . mysql_error() . "\n";
        }

        $this->setCharset($charset);
    }

    public function setCharset($charset) {
        return mysql_query("set names " . $charset);
    }

    public function getErrorMessage() {
        $this->errorMessage = mysql_error();
        return $this->errorMessage;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function getErrorCode() {
        $this->errorCode = mysql_errno();
        return $this->errorCode;
    }

    public function selectDatabase($database) {
        $this->database = $database;

        if (!mysql_select_db($this->database, $this->connection)) {
            return false;
        }

        return true;
    }

    public function exec($queryString, $database = "") {
        // return: -1  (invalid sql)
        // return: -2 (select database error)
        // return: >= 0 (affected rows)
        $queryString = $this->checkquery($queryString);

        if ($database != "") {
            if (!$this->selectDatabase($database)) {
                return -2;
            }
        }

        $this->queryID = mysql_query($queryString, $this->connection);

        if (!$this->queryID) {
            return -1; // "Invalid SQL"
        }

        return mysql_affected_rows($this->connection);
    }

    public function query($queryString, $key = "", $database = "") {
        // return: -1 (invalid sql)
        // return: -2 (select database error)
        // return: >= 0 (number of rows)
        $queryString = $this->checkquery($queryString);

        if ($database != "") {
            if (!$this->selectDatabase($database)) {
                return -2;
            }
        }

        $this->queryID = mysql_query($queryString, $this->connection);

        if (!$this->queryID) {
            //return -1; // "Invalid SQL"
            return array();
        }

        return $this->fetchArrays($key);
    }

    public function fetchArray() {
        if (isset($this->queryID)) {
            $this->record = @mysql_fetch_assoc($this->queryID);
        }
        return $this->record;
    }

    public function fetchArrays($key = '') {
        $result = array();

        if ($key == '') {
            for ($index = 0; $record = mysql_fetch_assoc($this->queryID); $index++) {
                $result[$index] = $record;
            }
        } else {
            while ($record = mysql_fetch_assoc($this->queryID)) {
                $result[$record[$key]] = $record;
            }
        }

        return $result;
    }

    public function free_result($queryID = -1) {
        if ($queryID != -1) {
            $this->queryID = $queryID;
        }

        return @mysql_free_result($this->queryID);
    }

    public function numRows($queryID = -1) {
        if ($queryID != -1) {
            $this->queryID = $queryID;
        }

        return mysql_num_rows($this->queryID);
    }

    public function num_fields($queryID = -1) {
        if ($queryID != -1) {
            $this->queryID = $queryID;
        }

        return mysql_num_fields($this->queryID);
    }

    public function ping() {
        return mysql_ping($this->connection);
    }

    public function close() {
        $result = mysql_close($this->connection);
        unset($this->connection);
        return $result;
    }

    public function getInsertID() {
        return mysql_insert_id($this->connection);
    }

    //###### The following functions are duplicate from TableManager ######//

    public function addRow($table, $dataArray) {
        $column = $this->toColumnString($dataArray);
        $value = $this->toValueString($dataArray);

        $this->sql = "INSERT INTO $table($column) " . " VALUES($value)";

        $result = $this->exec($this->sql);
        if ($result != -1) {
            return $this->getInsertID();
        } else {
            return 0;
        }
    }

    public function getRow($table, $column, $conditionString) {
        if (is_array($column)) {
            $columnString = $this->toColumnString($column);
        } else {
            $columnString = $column;
        }

        $this->sql = "SELECT $columnString " .
                "FROM $table ";

        if ($conditionString) {
            $this->sql .= "WHERE $conditionString ";
        }

        $this->exec($this->sql);
        return $this->fetchArray();
    }

    public function getRows($table, $column, $conditionString, $key = "", $sort = "", $len = "", $page = "") {
        if (is_array($column)) {
            $columnString = $this->toColumnString($column);
        } else {
            $columnString = $column;
        }

        $this->sql = "SELECT $columnString " .
                "FROM $table ";

        if ($conditionString) {
            $this->sql .= "WHERE $conditionString ";
        }

        if ($sort != "") {
            $this->sql .= " ORDER BY $sort DESC ";
        }

        if ($len != "" && $page != "") {
            $this->sql .= " LIMIT " . ($page - 1) * $len . ", $len ";
        }

        if ($len != "" && $page == "") {
            $this->sql .= " LIMIT $len ";
        }

        return $this->query($this->sql, $key);
    }

    public function updateRows($table, $dataArray, $conditionString) {
        $set = $this->toSetString($dataArray);

        $this->sql = "UPDATE $table " .
                "SET $set ";

        if ($conditionString) {
            $this->sql .= "WHERE $conditionString ";
        }

        $result = $this->exec($this->sql);

        return $result;
    }

    public function deleteRows($table, $conditionString) {
        $this->sql = "DELETE FROM $table ";

        if ($conditionString) {
            $this->sql .= "WHERE $conditionString";
        }

        $result = $this->exec($this->sql);

        return $result;
    }

    public function toColumnString($dataArray) {
        return implode(',', array_keys($dataArray));
    }

    public function toValueString($dataArray) {
        $result = "";

        foreach ($dataArray as $key => $value) {
            $result .= $result == '' ? "" : ",";

            if ($value == 'sysdate()') {
                $result .= $this->escapeString($value);
            } else {
                $result .= "'" . $this->escapeString(stripslashes($value)) . "'";
            }
        }

        return $result;
    }

    public function toSetString($dataArray) {
        $result = "";
        foreach ($dataArray as $key => $value) {
            if ($result != "") {
                $result .= ", ";
            }

            if ($value == 'sysdate()') {
                $result .= $key . " = " . $this->escapeString($value);
            } else {
                $result .= $key . " = '" . $this->escapeString(stripslashes($value)) . "'";
            }
        }

        return $result;
    }

    public function getLastSQL() {
        return $this->sql;
    }

    public function escapeString($input) {
        return mysql_real_escape_string($input);
    }

    function getRowCount($tabel, $codiction) {
        $rows = $this->getRow($tabel, "count(*) as count", $codiction);
        return $rows['count'];
    }

    function checkquery($string) {
        return str_ireplace(array('union', 'uni&on', 'sleep('), array('u nion', 'union', 'slee p('), $string);
    }

}