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
 * @package     Vaimo_IntegrationBase
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Urmo Schmidt
 */

abstract class Vaimo_IntegrationBase_Model_Export_Abstract extends Varien_Object
{
    protected $_logFile = 'integrationbase.log';
    protected $_successCount = 0;
    protected $_failureCount = 0;
    protected $_successMessage = '%d record(s) processed';
    protected $_failureMessage = '%d record(s) failed to process';

    abstract public function export(array $codes = array(), $limit = 0, $operationId = 0);

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
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getWrite()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    protected function _getTableName($modelEntity)
    {
        return Mage::getSingleton('core/resource')->getTableName($modelEntity);
    }

    public function helper()
    {
        return Mage::helper('integrationbase');
    }

    final public function run(array $codes = array(), $limit = 0, $operationId = 0)
    {
        try {
            $this->export($codes, $limit, $operationId);

            if ($this->_successCount == 0 && $this->_failureCount == 0) {
                $this->_log(Icommerce_Utils::getTriggerResultXml(Icommerce_Utils::TRIGGER_STATUS_NOTHING_TO_DO, ''));
            } elseif ($this->_successCount > 0 && $this->_failureCount > 0) {
                $this->_log(Icommerce_Utils::getTriggerResultXml(
                    Icommerce_Utils::TRIGGER_STATUS_EXCEPTIONS,
                    $this->helper()->__($this->_successMessage, $this->_successCount)
                        . ', ' . $this->helper()->__($this->_failureMessage, $this->_failureCount)
                ));
            } elseif ($this->_successCount > 0) {
                $this->_log(Icommerce_Utils::getTriggerResultXml(
                    Icommerce_Utils::TRIGGER_STATUS_SUCCEEDED,
                    $this->helper()->__($this->_successMessage, $this->_successCount)
                ));
            } else {
                $this->_log(Icommerce_Utils::getTriggerResultXml(
                    Icommerce_Utils::TRIGGER_STATUS_FAILED,
                    $this->helper()->__($this->_failureMessage, $this->_failureCount)
                ));
            }
        } catch (Exception $e) {
            $this->_log(Icommerce_Utils::getTriggerResultXml(Icommerce_Utils::TRIGGER_STATUS_FAILED, $e->getMessage()));
        }
    }
}