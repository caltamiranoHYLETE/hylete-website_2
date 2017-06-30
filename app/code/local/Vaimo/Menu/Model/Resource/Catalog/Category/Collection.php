<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @package     Vaimo_Menu
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

/**
 * This collection is only needed to make sure default values work the same way that they're implemented in:
 * flat, getModel()->load()
 *
 * eavCollections have problems loading attribute values that do not have default value (store_id=0) set.
 */
class Vaimo_Menu_Model_Resource_Catalog_Category_Collection extends Mage_Catalog_Model_Resource_Category_Collection
{
    protected function _getLoadAttributesSelect($table, $attributeIds = array())
    {
        if (empty($attributeIds)) {
            $attributeIds = $this->_selectAttributes;
        }

        if ($storeId = $this->getStoreId()) {
            $adapter        = $this->getConnection();
            $entityIdField  = $this->getEntity()->getEntityIdField();
            $select = $adapter->select()
                ->from(array('t_v' => $table), array('store_id', 'attribute_id', $entityIdField))
                ->where('t_v.entity_type_id = ?', $this->getEntity()->getTypeId())
                ->where("t_v.{$entityIdField} IN (?)", array_keys($this->_itemsById))
                ->where("t_v.attribute_id IN (?)", $attributeIds)
                ->group(array('t_v.entity_id', 't_v.attribute_id'))
                ->having('t_v.store_id IN (?)', array(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID, $storeId));
        } else {
            $select = parent::_getLoadAttributesSelect($table)
                ->where('store_id = ?', $this->getDefaultStoreId());
        }

        return $select;
    }

    protected function _getValueSelectStatementWithRelationToDefaultStoreValue($value, $storeId)
    {
        $adapter = $this->getConnection();
        $statement = $adapter->getCheckSql($adapter->quoteInto('t_v.store_id = ?', $storeId), $value, 'NULL');

        return $statement;
    }

    protected function _addLoadAttributesSelectValues($select, $table, $type)
    {
        if ($storeId = $this->getStoreId()) {
            $value = Mage::getResourceHelper('eav')->prepareEavAttributeValue('t_v.value', $type);

            $defaultValue = $this->_getValueSelectStatementWithRelationToDefaultStoreValue(
                $value,
                Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
            );

            $storeValue = $this->_getValueSelectStatementWithRelationToDefaultStoreValue(
                $value,
                $this->getStoreId()
            );

            $hasStoreValueFlag = $this->_getValueSelectStatementWithRelationToDefaultStoreValue(
                1,
                $this->getStoreId()
            );

            $select->columns(array(
                'has_store_value' => $this->_getGroupConcatSql($hasStoreValueFlag),
                'default_value' => $this->_getGroupConcatSql($defaultValue),
                'store_value'   => $this->_getGroupConcatSql($storeValue)
            ));
        } else {
            $select = parent::_addLoadAttributesSelectValues($select, $table, $type);
        }

        return $select;
    }

    protected function _getGroupConcatSql($field)
    {
        return new Zend_Db_Expr(sprintf('GROUP_CONCAT(%s)', $field));
    }

    protected function _setItemAttributeValue($valueInfo)
    {
        if ($this->getStoreId()) {
            if (!isset($valueInfo['value'])) {
                if ($valueInfo['store_value'] !== null || $valueInfo['has_store_value']) {
                    $valueInfo['value'] = $valueInfo['store_value'];
                } else {
                    $valueInfo['value'] = $valueInfo['default_value'];
                }
            }

            unset($valueInfo['store_id'], $valueInfo['has_store_value']);
        }

        return parent::_setItemAttributeValue($valueInfo);
    }
}