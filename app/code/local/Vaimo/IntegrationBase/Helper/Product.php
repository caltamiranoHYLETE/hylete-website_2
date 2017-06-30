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

class Vaimo_IntegrationBase_Helper_Product extends Mage_Core_Helper_Abstract
{
    protected $_read = null;
    protected $_write = null;

    /**
     * Get read adapter
     *
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getRead()
    {
        if ($this->_read == null) {
            $this->_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        }

        return $this->_read;
    }

    /**
     * Get write adapter
     *
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
     * Fast method for deleting all products. Note that indexes are not updated. Convenience method for
     * all the integrators out there.
     *
     * @param int $maxCount How many product to delete with one go
     */
    public function deleteAll($maxCount = 0)
    {
        $productIds = Mage::getResourceModel('catalog/product_collection')->setStoreId(0)->getAllIds();

        foreach ($productIds as $index => $id) {
            $this->_getWrite()->delete('catalog_product_entity', 'entity_id = ' . $id);

            if ($maxCount > 0 && $index >= $maxCount) {
                break;
            }
        }
    }

    /**
     * This function deletes all product attribute store values that are same as default value
     */
    public function deleteDuplicateAttributeValues($dryRun = true)
    {
        $tables = array(
            'catalog_product_entity_datetime',
            'catalog_product_entity_decimal',
            'catalog_product_entity_int',
            'catalog_product_entity_text',
            'catalog_product_entity_varchar'
        );

        $attributes = array();

        foreach ($tables as $table) {
            $select = $this->_getRead()
                ->select()
                ->from(array('s' => $table), array('value_id', 'attribute_id', 'value'))
                ->join(array('d' => $table), 'd.entity_type_id = s.entity_type_id AND d.attribute_id = s.attribute_id AND d.entity_id = s.entity_id AND d.store_id = 0', array('default_value' => 'value'))
                ->where('s.store_id != 0')
                ->where('s.value = d.value');

            foreach ($this->_getRead()->fetchAll($select) as $row) {
                echo implode("\t", $row) . "\n";
                $attributes[$row['attribute_id']][] = $row['value'];
            }
        }

        foreach ($attributes as $attributeId => $values) {
            $select = $this->_getRead()->select()->from('eav_attribute', 'attribute_code')->where('attribute_id = ?', $attributeId);
            echo $this->_getRead()->fetchOne($select) . "\n";
        }
    }

    public function recalculateProductUrls()
    {

    }
}