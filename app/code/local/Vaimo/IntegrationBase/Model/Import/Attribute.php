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
 * @author      Allan Paiste <allan.paiste@vaimo.com>
 */

class Vaimo_IntegrationBase_Model_Import_Attribute extends Vaimo_IntegrationBase_Model_Import_Abstract
{
    /**
     * Will contain attribute updaters for different entity types
     *
     * @var array
     */
    protected $_attributeUpdaters = array();
    protected $_eventPrefix = 'attribute';

    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/attribute', 'Attribute update');
        $this->_successMessage = '%d attribute(s) updated';
        $this->_failureMessage = '%d attribute(s) failed to update';
    }

    /**
     * Fetch updater for certain entity type
     *
     * @param $type
     * @return Vaimo_IntegrationBase_Model_Resource_Attribute_Action
     */
    protected function _getUpdaterByEntityType($type)
    {
        if (!isset($this->_attributeUpdaters[$type])) {
            $this->_attributeUpdaters[$type] = Mage::getResourceModel('integrationbase/attribute_action');
            $this->_attributeUpdaters[$type]->setEntityType($type);
        }

        return $this->_attributeUpdaters[$type];
    }

    /**
     * Update attribute value of certain entity in certain store. Note that the attribute scope will override any store-id
     * values (if the attribute scope is global, the store_id will default to default store id)
     *
     * @param $item
     */
    protected function _importRecord($item)
    {
        $this->_log($item->getEntityType() . ':' . ($item->getEntityId() ? $item->getEntityId() : $item->getLookupField() . '=' . $item->getLookupValue()) . '|' . $item->getStoreId() . ',' . $item->getAttributeCode() . ' = ' . $item->getAttributeValue());

        $attribute = Mage::helper('integrationbase/attribute')->getAttribute($item->getEntityType(), $item->getAttributeCode());
        if (!$attribute || !$attribute->getAttributeId()) {
            Mage::throwException('Attribute not found: ' . $item->getEntityType() . ' | ' . $item->getAttributeCode());
        }

        switch ($item->getEntityType()) {
            case 'catalog_product':
                if ($item->getEntityId()) {
                    $productIds = array($item->getEntityId());

                    $select = $this->_getRead()
                        ->select()
                        ->from($this->_getTableName('catalog/product'))
                        ->where('entity_id = :entity_id');

                    $bind = array(':entity_id' => (int)$item->getEntityId());

                    $entityId = $this->_getRead()->fetchOne($select, $bind);

                    if (!$entityId) {
                        Mage::throwException('Product not found');
                    }
                } else {
                    /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
                    $collection = Mage::getModel('catalog/product')->getCollection();
                    $collection->addAttributeToSelect('entity_id');
                    $collection->addAttributeToFilter($item->getLookupField(), array('eq' => $item->getLookupValue()));
                    $productIds = array();
                    foreach ($collection as $product) {
                        $productIds[] = $product->getId();
                    }
                    if (!$productIds) {
                        Mage::throwException('No matching products found');
                    }
                }

                /** @var Mage_Catalog_Model_Resource_Product_Action $updater */
                $updater = Mage::getResourceSingleton('catalog/product_action');
                $updater->updateAttributes($productIds, array($item->getAttributeCode() => $item->getAttributeValue()), $item->getStoreId());
                Mage::helper('integrationbase/pricerules')->applyAllRulesToProducts($collection,'attribute',$item->getAttributeCode(),$item->getStoreId());
                break;
            default:
                Mage::throwException('This entity type is not supported');
                break;
        }

        return $this;
    }

    /**
     * Delete attribute value of certain entity in certain store.
     *
     * @param $item
     */
    protected function _deleteRecord($item)
    {
        $this->_log('deleting attribute value for ' . $item->getEntityId() . ' - ' . $item->getAttributeCode());
        $updater = $this->_getUpdaterByEntityType($item->getEntityType());
        $id = $updater->getAttributeRawValue($item->getEntityId(), 'entity_id', 0);

        if (!$id) {
            Mage::throwException('entity with that id does not exist');
        }

        $updater->deleteAttributes(
            array($item->getEntityId()),
            array($item->getAttributeCode() => $item->getAttributeValue()),
            $item->getStoreId()
        );

        return $this;
    }
}