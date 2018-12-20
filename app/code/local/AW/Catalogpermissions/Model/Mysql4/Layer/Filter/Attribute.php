<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento enterprise edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Catalogpermissions
 * @version    1.4.5
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Catalogpermissions_Model_Mysql4_Layer_Filter_Attribute extends Mage_Catalog_Model_Resource_Layer_Filter_Attribute
{
    /**
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @param int $value
     * @return Mage_Catalog_Model_Resource_Layer_Filter_Attribute
     */
    public function applyFilterToCollection($filter, $value)
    {
        parent::applyFilterToCollection($filter, $value);
        $collection = $filter->getLayer()->getProductCollection();
        $select = $collection->getSelect();
        $attribute  = $filter->getAttributeModel();
        $tableAlias = $attribute->getAttributeCode() . '_idx';

        $selectFrom = $select->getPart(Zend_Db_Select::FROM);
        if (isset($selectFrom[$tableAlias])) {
            $configurableCondition = $this->getConfigurableConditions($filter);
            if ($configurableCondition) {
                $selectFrom[$tableAlias]['joinCondition'] .= ' AND (' . $configurableCondition . ')';
                $select->setPart(Zend_Db_Select::FROM, $selectFrom);
                $collection->clear();
            }
        }

        return $this;
    }

    /**
     * Retrieve array with products counts per attribute option
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @return array
     */
    public function getCount($filter)
    {
        // clone select from collection with filters
        $select = clone $filter->getLayer()->getProductCollection()->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        $connection = $this->_getReadAdapter();
        $attribute  = $filter->getAttributeModel();
        $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $filter->getStoreId()),
        );

        $configurableCondition = $this->getConfigurableConditions($filter);
        if ($configurableCondition) {
            $conditions[] = '(' . $configurableCondition . ')';
        }

        $select
            ->join(
                array($tableAlias => $this->getMainTable()),
                join(' AND ', $conditions),
                array('value', 'count' => new Zend_Db_Expr("COUNT({$tableAlias}.entity_id)")))
            ->group("{$tableAlias}.value");

        return $connection->fetchPairs($select);
    }

    /**
     * Get additional condition if there are configurable products in the collection
     *
     * @param $filter
     * @return null|string
     */
    protected function getConfigurableConditions($filter)
    {
        $connection = $this->_getReadAdapter();
        $attribute  = $filter->getAttributeModel();
        $tableAlias = $attribute->getAttributeCode() . '_idx';

        $filterAttributeId = $attribute->getAttributeId();
        $configurable = false;
        $configurableConditions = array();
        foreach ($filter->getLayer()->getProductCollection() as $product) {
            if ($product->getTypeId() == 'configurable') {
                $configurableAttributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
                foreach ($configurableAttributes as $attribute) {
                    if ($filterAttributeId == $attribute->getAttributeId()) {
                        $configurable = true;
                        $options = array();
                        foreach ($attribute->getPrices() as $option) {
                            $options[] = $option['value_index'];
                        }
                        $configurableConditions[] = $connection->quoteInto("({$tableAlias}.entity_id = ?", $product->getId()) .
                            " AND " .
                            $connection->quoteInto("{$tableAlias}.value IN (?))", $options);
                        break;
                    }
                }
            }
        }

        if ($configurable) {
            $condition = null;
            foreach ($configurableConditions as $configurableCondition) {
                if ($condition) {
                    $condition .= ' OR ' . $configurableCondition;
                } else {
                    $condition = $configurableCondition;
                }
            }
            return $condition;
        }
        return null;
    }
}
