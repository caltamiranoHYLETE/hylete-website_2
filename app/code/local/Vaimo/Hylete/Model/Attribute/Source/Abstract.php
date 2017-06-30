<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
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
 * @package     Vaimo_Hylete
 * @file        Source.php
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Vitali Rassolov <vitali.rassolov@vaimo.com>
 */

class Vaimo_Hylete_Model_Attribute_Source_Abstract extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $_options;

    public function getAllOptions()
    {
        return array();
    }

    protected function _getSortDir($dir)
    {
        return $dir ?: 'asc';
    }

    /**
     * Add Value Sort To Collection Select
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract $collection
     * @param string $dir direction
     * @return Mage_Eav_Model_Entity_Attribute_Source_Abstract
     */
    public function addValueSortToCollection($collection, $dir = false)
    {
        $attributeCode  = $this->getAttribute()->getAttributeCode();
        $attributeId    = $this->getAttribute()->getId();
        $attributeTable = $this->getAttribute()->getBackend()->getTable();

        if ($this->getAttribute()->isScopeGlobal()) {
            $tableName = $attributeCode . '_t';
            $collection->getSelect()
                ->joinLeft(
                    array($tableName => $attributeTable),
                    "e.entity_id={$tableName}.entity_id"
                    . " AND {$tableName}.attribute_id='{$attributeId}'"
                    . " AND {$tableName}.store_id='0'",
                    array());
            $valueExpr = $tableName . '.value';
        } else {
            $valueTable1 = $attributeCode . '_t1';
            $valueTable2 = $attributeCode . '_t2';
            $collection->getSelect()
                ->joinLeft(
                    array($valueTable1 => $attributeTable),
                    "e.entity_id={$valueTable1}.entity_id"
                    . " AND {$valueTable1}.attribute_id='{$attributeId}'"
                    . " AND {$valueTable1}.store_id='0'",
                    array())
                ->joinLeft(
                    array($valueTable2 => $attributeTable),
                    "e.entity_id={$valueTable2}.entity_id"
                    . " AND {$valueTable2}.attribute_id='{$attributeId}'"
                    . " AND {$valueTable2}.store_id='{$collection->getStoreId()}'",
                    array()
                );
            $valueExpr = $collection->getConnection()->getCheckSql(
                $valueTable2 . '.value_id > 0',
                $valueTable2 . '.value',
                $valueTable1 . '.value'
            );
        }

        $collection->getSelect()->order($valueExpr . ' ' . $this->_getSortDir($dir));

        return $this;
    }
}
