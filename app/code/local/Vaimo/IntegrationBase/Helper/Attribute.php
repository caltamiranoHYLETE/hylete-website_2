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
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 */

class Vaimo_IntegrationBase_Helper_Attribute extends Mage_Core_Helper_Abstract
{
    protected $_tableNames = array();
    protected $_attributes = array();
    protected $_optionIds = array();
    protected $_values = array();

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
        if (!isset($this->_tableNames[$modelEntity])) {
            $this->_tableNames[$modelEntity] = Mage::getSingleton('core/resource')->getTableName($modelEntity);
        }

        return $this->_tableNames[$modelEntity];
    }

    /**
     * @param string $entityType
     * @param string $code
     * @return Mage_Eav_Model_Entity_Attribute|bool
     */
    public function getAttribute($entityType, $code)
    {
        if (!isset($this->_attributes[$entityType][$code])) {
            $attribute = Mage::getSingleton('eav/config')->getAttribute($entityType, $code);
            if ($attribute && $attribute->getId()) {
                $this->_attributes[$entityType][$code] = $attribute;
            } else {
                $this->_attributes[$entityType][$code] = false;
            }
        }

        return $this->_attributes[$entityType][$code];
    }

    /**
     * Get attribute option id based on attribute and label
     * If this option does not exist, then create it
     *
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @param string $label
     * @param int $storeId
     * @return string
     */
    protected function _getCreateAttributeOptionId($attribute, $label, $storeId = 0)
    {
        if (!$label) {
            return '';
        }

        // try to find this option first from specified store, then from store 0
        $select = $this->_getRead()->select()
            ->from(array('o' => $this->_getTableName('eav/attribute_option')), 'option_id')
            ->joinRight(array('ov' => $this->_getTableName('eav/attribute_option_value')), 'ov.option_id = o.option_id', '')
            ->where('o.attribute_id = ?', $attribute->getId())
            ->where('ov.value = ?', $label)
            ->where('ov.store_id IN (?)', array(0, $storeId))
            ->order('ov.store_id DESC');

        if ($optionId = $this->_getRead()->fetchOne($select)) {
            return $optionId;
        }

        // create this option, add value to store 0
        $data = array(
            'attribute_id' => $attribute->getId(),
            'sort_order' => 0,
        );

        $this->_getWrite()->insert($this->_getTableName('eav/attribute_option'), $data);
        $optionId = $this->_getWrite()->lastInsertId($this->_getTableName('eav/attribute_option'));

        $data = array(
            'option_id' => $optionId,
            'store_id'  => 0,
            'value'     => $label,
        );

        $this->_getWrite()->insert($this->_getTableName('eav/attribute_option_value'), $data);

        return $optionId;
    }

    /**
     * Gets attribute option id based on attribute and label
     *
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @param string $label
     * @return string|bool
     */
    protected function _getAttributeOptionId($attribute, $label)
    {
        if (!$label) {
            return '';
        }

        foreach ($attribute->getSource()->getAllOptions() as $option) {
            if (strcasecmp($option['label'], $label) == 0) {
                return $option['value'];
            }
        }

        return false;
    }

    /**
     * Gets attribute value based on display value, can handle selects and multiselects
     *
     * @param string $entityType
     * @param string $code
     * @param string $text
     * @param int $storeId
     * @param string $delimiter
     * @return mixed
     */
    public function getAttributeValue($entityType, $code, $text, $storeId = 0, $delimiter = ',')
    {
        if (!isset($this->_values[$entityType][$storeId][$code][$text])) {
            if ($attribute = $this->getAttribute($entityType, $code)) {
                $attribute->getSource(); // this is just to assign default source model, in case it's empty on eav_attribute
                switch ($attribute->getFrontendInput()) {
                    case 'select':
                        switch ($attribute->getSourceModel()) {
                            case 'eav/entity_attribute_source_boolean':
                                $this->_values[$entityType][$storeId][$code][$text] = $text ? 1 : 0;
                                break;
                            case 'eav/entity_attribute_source_table':
                                $this->_values[$entityType][$storeId][$code][$text] = $this->_getCreateAttributeOptionId($attribute, $text, $storeId);
                                break;
                            case 'catalog/product_status':
                                $this->_values[$entityType][$storeId][$code][$text] = $text == Mage_Catalog_Model_Product_Status::STATUS_DISABLED ? Mage_Catalog_Model_Product_Status::STATUS_DISABLED : Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
                                break;
                            default:
                                $this->_values[$entityType][$storeId][$code][$text] = $this->_getAttributeOptionId($attribute, $text);
                                break;
                        }
                        break;
                    case 'multiselect':
                        if ($labels = explode($delimiter, $text)) {
                            $values = array();
                            foreach ($labels as $label) {
                                if ($attribute->getSourceModel() == 'eav/entity_attribute_source_table') {
                                    $values[] = $this->_getCreateAttributeOptionId($attribute, $label, $storeId);
                                } else {
                                    $values[] = $this->_getAttributeOptionId($attribute, $label);
                                }
                            }
                            $this->_values[$entityType][$storeId][$code][$text] = implode(',', $values);
                        } else {
                            $this->_values[$entityType][$storeId][$code][$text] = false;
                        }
                        break;
                    default:
                        switch ($attribute->getBackendType()) {
                            case 'int':
                                if ($text === '') {
                                    $this->_values[$entityType][$storeId][$code][$text] = null;
                                } else {
                                    $this->_values[$entityType][$storeId][$code][$text] = (int)$text;
                                }
                                break;
                            case 'decimal':
                                if ($text === '') {
                                    $this->_values[$entityType][$storeId][$code][$text] = null;
                                } else {
                                    $this->_values[$entityType][$storeId][$code][$text] = (float)$text;
                                }
                                break;
                            default:
                                $this->_values[$entityType][$storeId][$code][$text] = $text;
                                break;
                        }

                        break;
                }
            } else {
                $this->_values[$entityType][$storeId][$code][$text] = false;
            }
        }

        return $this->_values[$entityType][$storeId][$code][$text];
    }

    public function getValues()
    {
        return $this->_values;
    }
}