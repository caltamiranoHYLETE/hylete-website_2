<?php
/**
 * Copyright (c) 2009-2013 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_IntegrationBaseStandard
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Kjell Holmqvist <kjell.holmqvist@vaimo.com>
 */

abstract class Vaimo_IntegrationBaseStandard_Model_Export_Abstract extends Mage_Core_Model_Abstract
{
    protected $_logFile = 'standard_export.log';
    protected $_read = null;
    protected $_write = null;
    protected $_tableNames = array();
    protected $_successCount = 0;
    protected $_failureCount = 0;
    protected $_successMessage = '%d record(s) exported';
    protected $_failureMessage = '%d record(s) failed to export';

    protected function _log($message)
    {
        Mage::log($message, null, $this->_logFile, true);
        echo $message . "\n";
        flush();
        @ob_flush();
    }

    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getRead()
    {
        if (!$this->_read) {
            $this->_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        }

        return $this->_read;
    }

    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getWrite()
    {
        if (!$this->_write) {
            $this->_write = Mage::getSingleton('core/resource')->getConnection('core_write');
        }

        return $this->_write;
    }

    /**
     * Get resource table name, validated by db adapter
     *
     * @param   string $modelEntity
     * @return  string
     */
    protected function _getTableName($modelEntity)
    {
        if (!isset($this->_tableNames[$modelEntity])) {
            $this->_tableNames[$modelEntity] = Mage::getSingleton('core/resource')->getTableName($modelEntity);
        }

        return $this->_tableNames[$modelEntity];
    }

    public function getSuccessCount()
    {
        return $this->_successCount;
    }

    public function getFailureCount()
    {
        return $this->_failureCount;
    }

    public function getSuccessMessage()
    {
        return Mage::helper('integrationbasestandard')->__($this->_successMessage, $this->_successCount);
    }

    public function getFailureMessage()
    {
        return Mage::helper('integrationbasestandard')->__($this->_failureMessage, $this->_failureCount);
    }
}