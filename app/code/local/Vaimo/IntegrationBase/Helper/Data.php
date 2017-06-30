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
 * @package     Vaimo_IntegarionBase
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 * @author      Allan Paiste <allan.paiste@vaimo.com>
 */

class Vaimo_IntegrationBase_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_XML_PATH_EXPORT_QUEUE = 'integrationbase/export_queue/';

    const ROW_STATUS_IMPORTED = 0;
    const ROW_STATUS_IMPORT   = 1;
    const ROW_STATUS_DELETE   = 2;
    const ROW_STATUS_DELETED  = 3;

    const EVENT_PREFIX = 'integrationbase';
    const DEFAULT_SCOPE = 'process_queue';

    const QUEUE_ADD_NONE = 0;
    const QUEUE_ADD_CREATE = 1;
    const QUEUE_ADD_CREATE_UPDATE = 2;

    protected $_write = null;
    protected $_scopeTypes = array();
    protected $_optionIds = array();

    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getWrite()
    {
        if ($this->_write == null) {
            $this->_write = Mage::getSingleton('core/resource')->getConnection('core_write');
        }

        return $this->_write;
    }

    /**
     * Return all scopes in the order which they should be executed
     *
     * @return  array    Scope codes as strings
     */
    public function getScopeSequence()
    {
        return array(self::DEFAULT_SCOPE);
    }

    public function getAllIntegrationEventTypes()
    {
        $allScopes = $this->getScopeSequence();
        $types = array();
        foreach ($allScopes as $scope) {
            $types = array_merge($types, $this->getIntegrationEventTypes($scope));
        }

        return array_unique($types);
    }

    /**
     * Get list of events that have observers in a certain scope
     *
     * @param $scope
     */
    public function getIntegrationEventTypes($scope = self::DEFAULT_SCOPE)
    {
        if (!isset($this->_scopeTypes[$scope])) {
            $this->_scopeTypes[$scope] = array();
            $prefix = Vaimo_IntegrationBase_Helper_Data::EVENT_PREFIX . '_' . $scope;

            if ($events = Mage::getConfig()->getXpath("global/events/*[starts-with(local-name(), '" . $prefix . "')]")) {
                foreach ($events as $event) {
                    $eventName = (string)$event->getName();
                    $this->_scopeTypes[$scope][] = substr($eventName, strlen($prefix) + 1);
                }

                $this->_scopeTypes[$scope] = array_unique($this->_scopeTypes[$scope]);
            }
        }

        return $this->_scopeTypes[$scope];
    }

    /**
     * Checks whether entity can be added to queue
     *
     * @param string $entityTypeCode
     * @param Mage_Core_Model_Abstract $entity
     * @return bool
     */
    public function canAddEntityToQueue($entityTypeCode, $entity)
    {
        switch (Mage::getStoreConfig(self::CONFIG_XML_PATH_EXPORT_QUEUE . $entityTypeCode)) {
            case self::QUEUE_ADD_NONE:
                return false;
                break;
            case self::QUEUE_ADD_CREATE:
                if ($entity->getOrigData()) {
                    return false;
                }
                break;
            case self::QUEUE_ADD_CREATE_UPDATE:
                break;
        }

        $result = new Varien_Object();
        $result->setData(array(
            'skip' => null
        ));
        Mage::dispatchEvent('integrationbase_skip_exportqueue', array(
            'entity' => $entity,
            'result' => $result,
        ));

        if ($result->getSkip()) {
            return false;
        }

        if ($this->isAlreadyQueued($entityTypeCode, $entity->getId())) {
            return false;
        }

        return true;
    }

    /**
     * Check if certain type of entity is already in queue (and not exported yet)
     *
     * @param   string  $type Entity type code
     * @param   int $id   Unique ID of the entity
     * @param   bool $includeExported
     * @return  bool    Whether the entity is already queued
     */
    public function isAlreadyQueued($type, $id, $includeExported = false)
    {
        /** @var Vaimo_IntegrationBase_Model_Resource_Queue_Collection $collection */
        $collection = Mage::getModel('integrationbase/queue')->getCollection()
            ->applyEntityTypeCode($type)
            ->applyEntityId($id);

        if (!$includeExported) {
            $collection->applyNotExported();
        }

        return (bool)count($collection);
    }

    /**
     * Convert object recursively into an multi-dimensional array and return the end result as a Varien_Object.
     *
     * @param   object  $data   Object with attributes
     * @param   bool    $asArray    Determines whether the response should be returned as Varien_Object or as an array
     * @param   array   $unwrapKeys Indicate what single-key arrays should have they values unwrapped (eliminating the single-key wrapper).
     * @return  Varien_Object
     */
    public function objectToVarienObject($data, $asArray = false, array $unwrapKeys = array())
    {
        $newData = array();

        // convert object to kvp array
        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        // flip the array once. We assume that all keys are keywords, so we flip the array if the first key is numeric
        if (is_numeric(key($unwrapKeys))) {
            $unwrapKeys = array_flip($unwrapKeys);
        }

        // Using list statement because foreach has performance issues with changing the values of loop variable
        while (list($key, $value) = each($data)) {
            if (is_object($value) || is_array($value)) {
                $value = $this->objectToVarienObject($value, true, $unwrapKeys);
            }

            // some elements are just packed into single element arrays that define the type of underlying values, unwrap those
            if(is_array($value) && count($value) == 1 && isset($unwrapKeys[key($value)])) {
                if (is_array(reset($value))) {
                    $value = reset($value);
                } else {
                    // if it's single object, then we'll just reset the key
                    $value = array(reset($value));
                }
            }

            if (strtoupper($key) != $key) {
                $key = preg_replace('/(?!^)[[:upper:]][[:lower:]]/', '$0', preg_replace('/(?!^)[[:upper:]]+/', '_$0', $key));
            }


            $newData[strtolower($key)] = $value;
        }

        return $asArray ? $newData : new Varien_Object($newData);
    }

    protected function _getOptionId($source, $value)
    {
        foreach ($source->getAllOptions() as $option) {
            if (strcasecmp($option['label'], $value) == 0) {
                return $option['value'];
            }
        }
        return null;
    }

    /**
     * Gets product attribute option id based on attribute code and option text
     *
     * @param string $attributeCode
     * @param string $value
     * @return string
     */
    public function getAttributeOptionId($attributeCode, $value)
    {
        if (!$value) {
            return '';
        }

        if (!isset($this->_optionIds[$attributeCode][$value])) {
            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            if ($attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeCode)) {
                // $attribute->getSource()->getOptionId($value) has a bug in it, so will have to reimplement that function
                if (!$optionId = $this->_getOptionId($attribute->getSource(), $value)) {
                    $optionTable = Mage::getSingleton('core/resource')->getTableName('eav/attribute_option');
                    $data = array(
                        'attribute_id' => $attribute->getId(),
                        'sort_order' => 0,
                    );
                    $this->_getWrite()->insert($optionTable, $data);
                    $optionId = $this->_getWrite()->lastInsertId($optionTable);

                    $optionValueTable = Mage::getSingleton('core/resource')->getTableName('eav/attribute_option_value');
                    $data = array(
                        'option_id' => $optionId,
                        'store_id'  => 0,
                        'value'     => $value,
                    );
                    $this->_getWrite()->insert($optionValueTable, $data);
                }

                $this->_optionIds[$attributeCode][$value] = $optionId;
            } else {
                $this->_optionIds[$attributeCode][$value] = null;
            }
        }

        return $this->_optionIds[$attributeCode][$value];
    }

    /**
     * @param $code
     * @return bool|Vaimo_IntegrationBase_Model_Importer_Csv_Abstract
     */
    public function getImporter($code)
    {
        switch ($code) {
            case 'csv_product':
                return Mage::getSingleton('integrationbase/importer_csv_product');
            case 'csv_product_attribute':
                return Mage::getSingleton('integrationbase/importer_csv_product_attribute');
        }

        return false;
    }
}