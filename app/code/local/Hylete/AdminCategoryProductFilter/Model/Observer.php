<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

class Hylete_AdminCategoryProductFilter_Model_Observer
{
    /**
     * Add Columns to Admin Category product filter tab
     *
     * @return  null
     * @throws Mage_Core_Exception
     */
    public function addProductColumnsToCategoryGrid(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (empty($block)) {
            return $this;
        }

        /** @var $block Mage_Adminhtml_Block_Catalog_Category_Tab_Product */
        if ($block->getType() === 'adminhtml/catalog_category_tab_product') {
            $attributeModel = Mage::getSingleton('hylete_admincategoryproductfilter/attribute');
            $catalogHelper = Mage::helper('catalog');
            $block->addColumnAfter('type',
                [
                    'header' => $catalogHelper->__('Type'),
                    'width' => '60px',
                    'index' => 'type_id',
                    'type' => 'options',
                    'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
                    'sortable' => true,
                    'renderer' => Hylete_AdminCategoryProductFilter_Block_Adminhtml_Catalog_Category_Tab_Product_Column_Type::class,
                ],
                'sku');
            $block->addColumnAfter('visibility',
                [
                    'header' => $catalogHelper->__('Visibility'),
                    'width' => '70px',
                    'index' => 'visibility',
                    'type' => 'options',
                    'options' => $attributeModel->getAttributeOptions('visibility'),
                    'sortable' => true,
                    'renderer' => Hylete_AdminCategoryProductFilter_Block_Adminhtml_Catalog_Category_Tab_Product_Column_Visibility::class
                ],
                'type'
            );
            $block->addColumnAfter('status',
                [
                    'header' => $catalogHelper->__('Status'),
                    'width' => '70px',
                    'index' => 'status',
                    'type' => 'options',
                    'options' => $attributeModel->getAttributeOptions('status'),
                    'sortable' => true,
                    'renderer' => Hylete_AdminCategoryProductFilter_Block_Adminhtml_Catalog_Category_Tab_Product_Column_Status::class,
                ],
                'visibility'
            );
            $block->addColumnAfter('gender',
                [
                    'header' => $catalogHelper->__('Gender'),
                    'width' => '70px',
                    'index' => 'gender',
                    'type' => 'options',
                    'options' => $attributeModel->getAttributeOptions('gender'),
                    'sortable' => true,
                    'renderer' => Hylete_AdminCategoryProductFilter_Block_Adminhtml_Catalog_Category_Tab_Product_Column_Gender::class,
                ],
                'status'
            );
        }
    }
}
