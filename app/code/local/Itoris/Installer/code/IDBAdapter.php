<?php

class IDBAdapter {
    
	public function  __construct(Varien_Db_Adapter_Pdo_Mysql $db = null) {
		if ($db == null) {
			$this->db = Mage::getSingleton('core/resource')->getConnection('core_write');
		} else {
			$this->db = $db;
		}
	}

	public function getPrefix(){
		return '';
	}

	public function getTableList(){
		return $this->db->listTables();
	}

	public function setQuery($query){
		$this->query = str_replace('#__', $this->getPrefix(), $query);
	}

	public function query($query = null, $returnData = false){
		try{
			if ($query != null) {
				$this->query = $query;
			}
			$this->data = $this->db->query($this->query);
			$this->query = '';
			$this->setError(null);
			
			$this->errorNo = 0;

			if ($returnData) {
				return $this->data;
			}
			
			return true;
		}catch(Zend_Db_Adapter_Exception $ex){
			$this->setError($ex);
		}catch(Exception $ex){
			$this->setError($ex);
		}
	}

	private function setError($ex){
		if ($ex != null){
			$msg = $ex->getMessage();
			$code = $ex->getCode();
		}else{
			$msg = '';
			$code = 0;
		}
		
		$this->errorMsg = $this->_errorMsg = $msg; 
		$this->errorNo = $this->_errorNum = $code;
	}

	
	/**
	 * 
	 * On failure, or empty result, returns false
	 */
	public function loadResult(){
		try{
			$this->query();
			$arr = $this->data->fetchAll(Zend_Db::FETCH_BOTH);
			if(isset($arr[0][0]))
				return $arr[0][0];
			else
				return false;
		}catch(Exception $ex){
			$this->setError($ex);
			return false;
		}
	}

	public function Quote($s){
		return $this->db->quote($s);
	}
	
//	public function quote($s){
//		return $this->db->quote($s); 
//	}

	public function loadAssocList(){
		$this->query();
		return $this->data->fetchAll(Zend_Db::FETCH_ASSOC);
	}
	
	public function loadObjectList(){
		$this->query();
		return $this->data->fetchAll(Zend_Db::FETCH_OBJ);
	}
	
	public function fetchOne($sql, $bind = array()){
        $stmt = $this->db->query($sql, $bind);
        $result = $stmt->fetchColumn(0);
        return $result;
    }
    
	public function fetchAll($sql, $bind = array(), $fetchMode = null){
        if ($fetchMode === null) {
            $fetchMode = $this->_fetchMode;
        }
        $stmt = $this->db->query($sql, $bind);
        $result = $stmt->fetchAll($fetchMode);
        return $result;
    }
    
	public function fetchRow($sql, $bind = array(), $fetchMode = null)
    {
        if ($fetchMode === null) {
            $fetchMode = $this->_fetchMode;
        }
        $stmt = $this->db->query($sql, $bind);
        $result = $stmt->fetch($fetchMode);
        return $result;
    }
    
    public function insert($table, array $bind){
    	return $this->db->insert($table, $bind);
    }
    
	public function insertArray($table, array $columns, array $data){
        $vals = array();
        $bind = array();
        $columnsCount = count($columns);
        foreach ($data as $row) {
            if ($columnsCount != count($row)) {
                throw new Varien_Exception('Invalid data for insert');
            }
            $line = array();
            if ($columnsCount == 1) {
                if ($row instanceof Zend_Db_Expr) {
                    $line = $row->__toString();
                } else {
                    $line = '?';
                    $bind[] = $row;
                }
                $vals[] = sprintf('(%s)', $line);
            } else {
                foreach ($row as $value) {
                    if ($value instanceof Zend_Db_Expr) {
                        $line[] = $value->__toString();
                    }
                    else {
                        $line[] = '?';
                        $bind[] = $value;
                    }
                }
                $vals[] = sprintf('(%s)', join(',', $line));
            }
        }

        // build the statement
        $columns = array_map(array($this->db, 'quoteIdentifier'), $columns);
        $sql = sprintf("INSERT INTO %s (%s) VALUES%s",
            $this->db->quoteIdentifier($table, true),
            implode(',', $columns), implode(', ', $vals));

        // execute the statement and return the number of affected rows
        $stmt = $this->db->query($sql, $bind);
        $result = $stmt->rowCount();
        return $result;
    }
    
    public function exec($sql){
    	return $this->db->query($sql);
    }
    
	public function insertOnDuplicate($table, array $data, array $fields = array())
    {
        // extract and quote col names from the array keys
        $row    = reset($data); // get first elemnt from data array
        $bind   = array(); // SQL bind array
        $cols   = array();
        $values = array();

        if (is_array($row)) { // Array of column-value pairs
            $cols = array_keys($row);
            foreach ($data as $row) {
                $line = array();
                if (array_diff($cols, array_keys($row))) {
                    throw new Varien_Exception('Invalid data for insert');
                }
                foreach ($row as $val) {
                    if ($val instanceof Zend_Db_Expr) {
                        $line[] = $val->__toString();
                    } else {
                        $line[] = '?';
                        $bind[] = $val;
                    }
                }
                $values[] = sprintf('(%s)', join(',', $line));
            }
            unset($row);
        } else { // Column-value pairs
            $cols = array_keys($data);
            $line = array();
            foreach ($data as $val) {
                if ($val instanceof Zend_Db_Expr) {
                    $line[] = $val->__toString();
                } else {
                    $line[] = '?';
                    $bind[] = $val;
                }
            }
            $values[] = sprintf('(%s)', join(',', $line));
        }

        $updateFields = array();
        if (empty($fields)) {
            $fields = $cols;
        }

        // quote column names
        $cols = array_map(array($this->db, 'quoteIdentifier'), $cols);

        // prepare ON DUPLICATE KEY conditions
        foreach ($fields as $k => $v) {
            $field = $value = null;
            if (!is_numeric($k)) {
                $field = $this->db->quoteIdentifier($k);
                if ($v instanceof Zend_Db_Expr) {
                    $value = $v->__toString();
                } else if (is_string($v)) {
                    $value = 'VALUES('.$this->db->quoteIdentifier($v).')';
                } else if (is_numeric($v)) {
                    $value = $this->db->quoteInto('?', $v);
                }
            } else if (is_string($v)) {
                $field = $this->db->quoteIdentifier($v);
                $value = 'VALUES('.$field.')';
            }

            if ($field && $value) {
                $updateFields[] = "{$field}={$value}";
            }
        }

        // build the statement
        $sql = "INSERT INTO "
             . $this->db->quoteIdentifier($table, true)
             . ' (' . implode(', ', $cols) . ') '
             . 'VALUES ' . implode(', ', $values);
        if ($updateFields) {
            $sql .= " ON DUPLICATE KEY UPDATE " . join(', ', $updateFields);
        }

        // execute the statement and return the number of affected rows
        $stmt = $this->db->query($sql, array_values($bind));
        $result = $stmt->rowCount();
        return $result;
    }

	public function insertMultiple($table, array $data) {
		$row = reset($data);
		// support insert syntaxes
		if (!is_array($row)) {
			return $this->insert($table, $data);
		}

		// validate data array
		$cols = array_keys($row);
		$insertArray = array();
		foreach ($data as $row) {
			$line = array();
			if (array_diff($cols, array_keys($row))) {
				throw new Zend_Db_Exception('Invalid data for insert');
			}
			foreach ($cols as $field) {
				$line[] = $row[$field];
			}
			$insertArray[] = $line;
		}
		unset($row);

		return $this->insertArray($table, $cols, $insertArray);
	}
    
    public function update($table, array $bind, $where = ''){
    	return $this->db->update($table, $bind, $where);
    }
    public function delete($table, $where = ''){
    	return $this->db->delete($table, $where);
    }

	public function dropTable($tableName) {
        $table = $tableName;
        $query = 'DROP TABLE IF EXISTS ' . $table;
        $this->db->query($query);
        return true;
    }

	public function listTables() {
        return $this->db->fetchCol('SHOW TABLES');
    }

	private $db;
	private $query;
	private $errorNo = 0;
	private $errorMsg = '';
	private $data = null;
	public $_errorMsg = '';
	public $_errorNum = 0;
	
	protected $_fetchMode = Zend_Db::FETCH_ASSOC;

}


?>