<?php
/**
 * Support functions for Data Imports into a Magento Database
 *
 * @category    Icommerce
 * @package     Icommerce_Import
 * @copyright   Copyright (c) 2011 Icommerce Nordic AB (http://www.icommerce.se)
 * @author      Wilko Nienhaus <wilko@icommerce.se>
 *
 */

/**
 * DbImport support class
 */
class Icommerce_DbImport
{
    /**
     * @deprecated since 2012-04-21. Use self::$_holdSize instead.
     */
    const HOLD_SIZE = 100;

    /**
     * Constant defining the index of the Main Table substructure in the _dataCache
     */
    const CACHE_INDEX_MAIN_TABLES = 0;

    /**
     * Constant defining the index of the Detail Table substructure in the _dataCache
     */
    const CACHE_INDEX_DETAIL_TABLES = 1;

    /**
     * Flag inserted into the record buffer key fields to indicate data should be overwritten if exists in database.
     */
    const FORCE_FLAG = '!force';

    /**
     * Flag inserted into the record buffer key fields to indicate inserted records ids must be cached for inserting detail rows
     */
    const GUID_FLAG = '!guid';

    /**
     * Constant to indicate insert and update operations
     */
    const OPERATION_UPDATE = 1;

    /**
     * Constant to indicate delete operations
     */
    const OPERATION_DELETE = 2;

    /**
     * Flag indicating whether we want to collect statistics or not
     *
     * @var bool
     */
    static $_countStats = false;

    /**
     * Internal counter for how many DB updates we have "withheld".  Used to determine when to flush the buffer.
     *
     * @static int
     */
    static $_holdCnt = 0;

    /**
     * Defines how many DB operations are buffered before they are flushed to the DB.
     *
     * @static int
     */
    static $_holdSize = 100;

    /**
     * Defines whether to use manual flushing or to automatically flush, when reaching $holdSize
     *
     * @static bool
     */
    static $_manualFlush = false;

    /**
     * Internal buffer of records that is automatically flushed when $_holdSize is reached, or when flush() is called.
     * @static array
     */
    static $_dataCache = array();

    /**
     * Internal buffer of records that should be deleted when $_holdSize is reached, or when flush() is called.
     * @static array
     */
    static $_deleteCache = array();

    /**
     * Internal buffer for mapping GUIDs to IDs as records are inserted.
     * Detail rows use this to look up the parents entity id before rows are inserted.
     */
    static $_guidIdCache = array();

    /**
     * Array of flags (indexed by table name) indicating whether it is necessary to resolve GUID->entity_id mappings
     */
    static $_resolveGuidsOnFlush = array();

    /**
     * Array of statistics (counters) for operations on different table types
     *
     * @var array
     */
    static $_stats = array();

    /**
     * Defines how many DB operations are buffered before they are flushed to the DB
     *
     * @static
     * @param int $size Size of the hold buffer in number of records
     */
    static function setHoldSize($size)
    {
        self::$_holdSize = $size;
    }

    /**
     * Enables or disabled manual flushing using the "flush" function
     *
     * @static
     * @param bool $mode True to enable manual flushing, False for automatic flushing
     */
    static function setManualFlush($mode = true)
    {
        self::$_manualFlush = $mode;
    }

    /**
     * Enables or disables collection of internal statistics data
     *
     * @static
     * @param bool $mode
     */
    static function setStatisticsCollection($mode = true)
    {
        self::$_countStats = $mode;
    }

    static function resetStatistics()
    {
        self::$_stats = array();
    }

    /**
     * Update internal counters about database operations performed
     *
     * @static
     * @param int $tableType Table type updated (master, detail)
     * @param int $operation DB operation performed
     * @param array $rows Array of rows that were updated
     * @return mixed
     */
    static function updateStatistics($tableType, $operation, $rows)
    {
        if (!self::$_countStats) {
            return;
        }

        self::$_stats[$tableType][$operation] += count($rows);
    }

    /**
     * Get update and delete statistics for table row operations
     *
     * @static
     * @param bool $includeDetailTables Include stats about detail tables
     * @return array Array of counters for operations
     */
    static function getStatistics($includeDetailTables)
    {
        $result = array();

        $tableTypes = array(self::CACHE_INDEX_MAIN_TABLES);
        if ($includeDetailTables) {
            $tableTypes[] = self::CACHE_INDEX_DETAIL_TABLES;
        }

        foreach (array(self::OPERATION_UPDATE, self::OPERATION_DELETE) as $operation) {
            $result[$operation] = 0;
            foreach ($tableTypes as $tableType) {
                if (!isset(self::$_stats[$tableType][$operation])) {
                    continue;
                }
                $result[$operation] += self::$_stats[$tableType][$operation];
            }
        }

        return $result;
    }

    /**
     * Write the internal record cache to the database
     *
     * @param bool $force In manual mode, force must be used to actually flush data
     * @static
     * @return bool True if data was written to the database, False otherwise
     */
    static function flush($force = false)
    {
        if (self::$_manualFlush && !$force) {
            return false;
        }

        $didFlush = false;

        //insert + update
        foreach (array(self::CACHE_INDEX_MAIN_TABLES,self::CACHE_INDEX_DETAIL_TABLES) as $tableType) {
            if (isset(self::$_dataCache[$tableType]))
            foreach (self::$_dataCache[$tableType] as $table => $rowTypes) {
                foreach ($rowTypes as $type => $rows) {
                    //break apart type key, because it contains some flags before the field names
                    $type = explode(':', $type);
                    
                    //if the force flag is set, we overwrite data in the database
                    $force = false;
                    if ($type[0]==self::FORCE_FLAG) {
                        array_shift($type);
                        $force = true;
                    }
                    $insertMethod = $force ? 'insertOnDuplicate' : 'insertMultiple';

                    //if guid flag is set, we need to update the GuidId mappings cache with the insertId of each new record
                    if ($type[0]==self::GUID_FLAG) {
                        foreach ($rows as $row) {
                            $guid = $row[self::GUID_FLAG];
                            unset($row[self::GUID_FLAG]);
                            Icommerce_Db::getDbWrite()->insert($table,$row);
                            $didFlush = true;
                            self::$_guidIdCache[$guid] = Icommerce_Db::getDbWrite()->lastInsertId();
                            self::updateStatistics($tableType, self::OPERATION_UPDATE, array($row));
                        }
                        continue;
                    }

                    //for tables where we are using GUID placeholders, we need to replace those with the real IDs recorded when inserting the parent records
                    if (isset(self::$_resolveGuidsOnFlush[$table])) {
                        foreach ($rows as &$row) {
                            if (isset($row['entity_id']) && substr($row['entity_id'],0,5) == 'guid:') {
                                $row['entity_id'] = self::$_guidIdCache[$row['entity_id']];
                            }
                        }
                    }

                    //insert rows
                    Icommerce_Db::getDbWrite()->$insertMethod($table,$rows);
                    self::updateStatistics($tableType, self::OPERATION_UPDATE, $rows);
                    $didFlush = true;
                }
            }
        }
        self::$_dataCache = array(); //empty

        //delete
        foreach (array(self::CACHE_INDEX_DETAIL_TABLES,self::CACHE_INDEX_MAIN_TABLES) as $tableType) {
            if (isset(self::$_deleteCache[$tableType]))
            foreach (self::$_deleteCache[$tableType] as $table => $fieldIdMap) {
                foreach ($fieldIdMap as $field => $ids) {
                    Icommerce_Db::getDbWrite()->delete(
                        $table,
                        Icommerce_Db::getDbWrite()->quoteInto($field . ' IN (?)', $ids)
                    );
                    self::updateStatistics($tableType, self::OPERATION_DELETE, $ids);
                    $didFlush = true;
                }
            }
        }
        self::$_deleteCache = array();

        self::$_holdCnt = 0;
        self::$_resolveGuidsOnFlush = array(); //empty

        return $didFlush;
    }

    /**
     * Write the internal record cache only if we reach the internal buffer size limit
     *
     * @return bool True if data was flushed, False otherwise
     */
    static function flushIfNeeded()
    {
        if (self::$_manualFlush) {
            return false;
        }
        if (++self::$_holdCnt==self::$_holdSize) {
            return self::flush();
        }
        return false;
    }

    /**
     * Get a key uniquely identifying the type of row
     *
     * @param array $data Key/value pair array of fields/data
     * @param bool $overwrite Overwrite existing record
     * @return string key
     */
    static function _getRowKey($data,$overwrite = false)
    {
        $keys = array_keys($data);
        sort($keys);
        if ($overwrite) {
            array_unshift($keys, self::FORCE_FLAG);
        }
        return implode($keys,':');
    }

    /**
     * Add the given data row to the internal record buffer
     *
     * @param string $tableName Name of the table for which the records are meant
     * @param string $type Data type if we are dealing with an EAV detail table
     * @param array $data Key/value pair array of fields/data
     * @param bool $overwrite Overwrite existing record
     * @return void
     */
    static function _addRecordToCache($tableName,$type,$data,$overwrite = false)
    {
        $key = $tableName;
        $idx = self::CACHE_INDEX_MAIN_TABLES;
        if (Icommerce_Eav::isDetailTable($tableName,$type)) {
            if ($type && $type!='') {
                $key .= '_' . $type;
            }
            $idx = self::CACHE_INDEX_DETAIL_TABLES;
        }
        self::$_dataCache[$idx][$key][self::_getRowKey($data, $overwrite)][] = $data;
    }

    /**
     * Add record to buffer for lazy deletion
     *
     * @static
     * @param string $tableName Name of the table from which the record is to be deleted
     * @param string $entityField Field name that holds the entities unique record identifier
     * @param int $entityId Entity id of the record to be deleted
     */
    static function _addRecordToDeleteCache($tableName, $entityField, $entityId)
    {
        $idx = self::CACHE_INDEX_MAIN_TABLES;
        self::$_deleteCache[$idx][$tableName][$entityField][] = $entityId;
    }

    /**
     * Store an entity to the database with the entity_id already known
     *
     * @param string $entity_type_code EAV entity type code or name of flat table
     * @param int $entity_id Entity id (or primary key field value)
     * @param array $data Key/value pair array of attributes/data
     * @return void
     */
    static function createEntityWithId($entity_type_code, $entity_id, $data = array())
    {
        $pk = 'entity_id';
        if (is_array($entity_id)) {
            $pk = array_keys($entity_id);
            $pk = $pk[0];
            $entity_id = $entity_id[$pk];
        }
        $tableName = Icommerce_Eav::getEntityMainTable($entity_type_code);
        foreach ($data as $field => $value) {
            $type = Icommerce_Eav::getAttributeType($field,$entity_type_code);
            if ($type!='' && $type!='static') {
                $data = array(
                    'entity_type_id'=> Icommerce_Eav::getEntityTypeId($entity_type_code),
                    'attribute_id'=> Icommerce_Eav::getAttributeId($field,$entity_type_code),
                    'entity_id' => $entity_id,
                    'value' => $value
                );
                $backendTable = Icommerce_Eav::getBackendTable($entity_type_code,$field);
                if ($backendTable) {
                    self::_addRecordToCache($backendTable,null,$data,true);
                } else {
                    self::_addRecordToCache($tableName,$type,$data,true);
                }
            } else {
                $mainTable[$field] = $value;
            }
        }
        if (isset($mainTable)) {
            $mainTable[$pk] = $entity_id;
            if (Icommerce_Eav::entityStoresTypeId($entity_type_code)) {
                $mainTable['entity_type_id'] = Icommerce_Eav::getEntityTypeId($entity_type_code);
            }
            self::_addRecordToCache($tableName,'',$mainTable,true);
        }
        self::flushIfNeeded();
    }

    /**
     * Store an entity to the database with the entity_id (primary key) not yet known
     *
     * @param string $entity_type_code EAV entity type code or name of flat table
     * @param array $data Key/value pair array of attributes/data
     * @param boolean $cacheInsert optional True to buffer, False to insert immediately
     * @param bool $overwrite Overwrite existing record
     * @return int|null Returns the ID of the newly inserted record (only if it wasn't cached)
     *
     * @todo Implement support for detail tables when in cached mode (i.e get ID from master record, once inserted)
     */
    static function createEntity($entity_type_code, $data = array(), $cacheInsert=false, $overwrite=false)
    {
        //TODO: merge with above function (DRY principle)
        $newId = null;
        $usingGuid = false;
        $tableName = Icommerce_Eav::getEntityMainTable($entity_type_code);
        foreach ($data as $field => $value) {
            $type = Icommerce_Eav::getAttributeType($field,$entity_type_code);
            //deal only with main table attributes
            if ($type=='' || $type=='static') {
                $mainTable[$field] = $value;
            }
        }
        if (isset($mainTable)) {
            if (Icommerce_Eav::entityStoresTypeId($entity_type_code)) {
                $mainTable['entity_type_id'] = Icommerce_Eav::getEntityTypeId($entity_type_code);
            }
            if ($cacheInsert) {
                if (isset($data['entity_id'])) {
                    $newId = $data['entity_id'];
                } else {
                    //insert !guid as a flag, that we need to manually insert this record
                    //and get the primary key value after insert, so that when inserting
                    //details records, we can look up the GUID to primary key value
                    $usingGuid = true;
                    $newId = 'guid:' . Icommerce_Utils::create_guid();
                    $mainTable['!guid'] = $newId;
                }
                self::_addRecordToCache($tableName,'',$mainTable);
            } else {
                $insertMethod = $overwrite ? 'insertOnDuplicate' : 'insert';
                Icommerce_Db::getDbWrite()->$insertMethod($tableName,$mainTable);
                $newId = Icommerce_Db::getDbWrite()->lastInsertId();
            }
        }
        foreach ($data as $field => $value) {
            $type = Icommerce_Eav::getAttributeType($field,$entity_type_code);
            //deal only with detail table attributes
            if ($type!='' && $type!='static') {
                $data = array(
                    'entity_type_id'=> Icommerce_Eav::getEntityTypeId($entity_type_code),
                    'attribute_id'=> Icommerce_Eav::getAttributeId($field,$entity_type_code),
                    'entity_id' => $newId,
                    'value' => $value
                );
                if (is_null($newId)) {
                    throw new Exception('Cannot add EAV detail records without entity_id');
                }
                if ($usingGuid) {
                    self::$_resolveGuidsOnFlush[$tableName . '_' . $type] = true; //TODO: ugly to derive table name here. rather encapsulate
                }
                self::_addRecordToCache($tableName,$type,$data,$overwrite);
            }
        }
        self::flushIfNeeded();
        return $newId;
    }

    /**
     * Updates an entity in the the attribute values provided
     *
     * @param string $entity_type_code EAV entity type code or name of flat table
     * @param int $entity_id Entity id (or primary key field value)
     * @param array $data Key/value pair array of attributes/data
     * @return void
     */
    static function updateEntity($entity_type_code,$entity_id,$data = array())
    {
        $pk = 'entity_id';
        if (is_array($entity_id)) {
            $pk = array_keys($entity_id);
            $pk = $pk[0];
            $entity_id = $entity_id[$pk];
        }
        $tableName = Icommerce_Eav::getEntityMainTable($entity_type_code);
        foreach ($data as $field => $value) {
            $type = Icommerce_Eav::getAttributeType($field,$entity_type_code);
            if ($type!='' && $type!='static') {
                $data = array(
                    'entity_type_id'=> Icommerce_Eav::getEntityTypeId($entity_type_code),
                    'attribute_id'=> Icommerce_Eav::getAttributeId($field,$entity_type_code),
                    'entity_id' => $entity_id,
                    'value' => $value
                );
                Icommerce_Db::getDbWrite()->insertOnDuplicate($tableName . '_' . $type,$data);
            } else {
                $mainTable[$field] = $value;
            }
        }
        if (isset($mainTable)) {
            $cond = Icommerce_Db::getDbWrite()->quoteInto($pk . '=?', $entity_id);
            Icommerce_Db::getDbWrite()->update($tableName,$mainTable,$cond);
        }
    }

    /**
     * Deletes an entity
     *
     * @param string $entity_type_code EAV entity type code or name of flat table
     * @param int|array $entity_id Entity id (or primary key field value)
     * @return void
     */
    static function deleteEntity($entity_type_code, $entity_id)
    {
        $pk = 'entity_id';
        if (is_array($entity_id)) {
            $pk = array_keys($entity_id);
            $pk = $pk[0];
            $entity_id = $entity_id[$pk];
        }
        $tableName = Icommerce_Eav::getEntityMainTable($entity_type_code);

        self::_addRecordToDeleteCache($tableName, $pk, $entity_id);
        self::flushIfNeeded();
    }
}
