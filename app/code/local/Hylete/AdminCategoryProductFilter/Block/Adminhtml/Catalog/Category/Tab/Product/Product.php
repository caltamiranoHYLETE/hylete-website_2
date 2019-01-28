<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

/**
 * Render column
 */
class Hylete_AdminCategoryProductFilter_Block_Adminhtml_Catalog_Category_Tab_Product_Product
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /** @var Mage_Catalog_Model_Product[] */
    protected $products = [];

    /** @var Mage_Catalog_Model_Product|Mage_Core_Model_Abstract */
    protected $productModel;

    /**
     * Retrieve product by entity id
     *
     * @param $id
     * @return Mage_Catalog_Model_Product
     */
    protected function getProduct($id)
    {
        if (isset($this->products[$id])) {
            return $this->products[$id];
        }

        return $this->products[$id] = $this->getProductModel()->load($id);
    }

    /**
     * Retrieve Product Model
     *
     * @return Mage_Catalog_Model_Product|Mage_Core_Model_Abstract
     */
    protected function getProductModel()
    {
        if ($this->productModel === null) {
            $this->productModel = Mage::getModel('catalog/product');
        }
        return $this->productModel;
    }

    /**
     * Render column
     *
     * @param Varien_Object $row
     * @param string $attributeCode
     * @return bool|string
     * @throws Mage_Core_Exception
     */
    protected function _render(Varien_Object $row, $attributeCode)
    {
        $product = $this->getProduct($row->getEntityId());
        $attribute = Mage::getSingleton('hylete_admincategoryproductfilter/attribute')
            ->getAttribute($attributeCode);
        if ($attribute->usesSource()) {
            return $attribute->getSource()->getOptionText($product->getVisibility());
        }
        return $row;
    }
}
