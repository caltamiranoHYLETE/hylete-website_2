<?php

class Ebizmarts_BakerlooRestful_Helper_Sqlite {

    private $_db = null;
    private $_bindParams = array();

    public function __construct($db = null) {

        if(!is_null($db))
            $this->_db = new SQLite3($db);
    }

    public function getDb($db = null) {
        if(!is_null($this->_db))
            return $this->_db;
        else
            return $db;
    }

    public function prepareStmt($stmt, $db = null) {
        return $this->getDb($db)->prepare($stmt);
    }

    public function addParameter($name, $value, $type = null) {
        $this->_bindParams []= array(
            'name'  => $name,
            'value' => $value,
            'type'  => $type
        );
    }

    public function getAllParameters() {
        return $this->_bindParams;
    }

    public function execPrepared($stmt, $db = null) {

        $this->bindValues($stmt, $this->getAllParameters());

        $result = $stmt->execute();

        return $result;
    }

    public function bindValues($stmt, $parameters) {
        foreach($parameters as $param) {

            if($param['type'] == null)
                $stmt->bindValue(':'.$param['name'], $param['value']);
            else
                $stmt->bindValue(':'.$param['name'], $param['value'], $param['type']);
        }
    }

}