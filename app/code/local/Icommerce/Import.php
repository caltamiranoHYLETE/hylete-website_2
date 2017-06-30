<?php

class Icommerce_Import {

    /**
     * Loads the Indexer Process specified by $id
     * @static
     * @param Mage_Index_Model_Process|int|string $id Process Id, Process Code or Process instance
     * @return Mage_Index_Model_Process
     */
    protected static function _loadIndexerProcess($id)
    {
        if ($id instanceof Mage_Index_Model_Process) {
            return $id;
        } else {
            if (is_string($id)) {
                $id = Icommerce_Db::getDbRead()->fetchOne(
                    "SELECT process_id FROM index_process WHERE indexer_code LIKE '$id'"
                );
            }
            if ($id) {
                return Mage::getModel('index/process')->load($id);
            }
        }
        return null;
    }

    /**
     * Sets the Index Mode of all Indexing Processes to the mode specified
     * 
     * @static
     * @param string $mode Mage_Index_Model_Process::MODE_REAL_TIME or Mage_Index_Model_Process::MANUAL
     * @return void
     */
    static function setAllIndexModes($mode = Mage_Index_Model_Process::MODE_REAL_TIME) {
        $col = Mage::getModel('index/process')->getCollection()->load();
        foreach ($col as $process) {
            self::setOneIndexModes($process,$mode);
        }
    }

    /**
     * Sets the Index Mode for one Indexing Process to the mode specified
     * @static
     * @param Mage_Index_Model_Process|int|string $id Process Id, Process Code or Process instance
     * @param string $mode Mage_Index_Model_Process::MODE_REAL_TIME or Mage_Index_Model_Process::MANUAL
     * @return void
     */
    static function setOneIndexModes($id, $mode = Mage_Index_Model_Process::MODE_REAL_TIME) {
        $process = self::_loadIndexerProcess($id);
        if ($process) {
            $process->setMode($mode)->save();
        }
    }

    /**
     * Gets the current Index Mode of the Indexer Process specified by $id
     * @static
     * @param Mage_Index_Model_Process|int|string $id Process Id, Process Code or Process instance
     * @return string
     */
    static function getOneIndexMode($id) {
        $process = self::_loadIndexerProcess($id);
        if ($process)
            return $process->getMode();
        return null;
    }

    /**
     * Gets the current Index Mode of all Indexer Processes
     * @static
     * @return array Associative array indexed by indexer_code of current modes (IndexerCode => mode)
     */
    static function getAllIndexModes() {
        $modes = null;
        $col = Mage::getModel('index/process')->getCollection()->load();
        foreach ($col as $process) {
            $modes[$process->getIndexerCode()] = self::getOneIndexMode($process);
        }
        return $modes;
    }

    /**
     * Set the Index Modes of all Indexer Processes specified in the array
     * @static
     * @param array $idxMode Array of Indexer Processes and the modes to set (IndexerCode => mode)
     * @return void
     */
    static function setIndexModes($idxMode = array())
    {
        if (!is_array($idxMode)) return;

        foreach ($idxMode as $indexerCode => $mode) {
            self::setOneIndexModes($indexerCode, $mode);
        }
    }



    /**
     * @static
     * @param  $path An import path (relative or absolute
     * @return string The first found file matching the path
     */
    static function locateImportFile( $path ){
        if( !$path ) return null;
        if( $path[0]==DS ){
            if( file_exists($path) ){
                return $path;
            }
        } else{
            // Make sure / first
            $path = "/$path";
        }

        $sdir = Icommerce_Default::getSiteRoot(true);
        if( file_exists($sdir.$path) ){
            return $sdir.$path;
        }
        if( file_exists($sdir."var/".$path) ){
            return $sdir."var/".$path;
        }
        $inst = Icommerce_Default::getInstance();
        if( file_exists("/home/ftp/".$inst.$path) ){
            return "/home/ftp/".$inst.$path;
        }

        // No match
        return null;
    }

}
