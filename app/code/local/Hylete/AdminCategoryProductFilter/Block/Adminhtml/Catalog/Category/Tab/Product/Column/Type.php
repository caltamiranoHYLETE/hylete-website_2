<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

/**
 * Render product type column
 */
class Hylete_AdminCategoryProductFilter_Block_Adminhtml_Catalog_Category_Tab_Product_Column_Type
    extends Hylete_AdminCategoryProductFilter_Block_Adminhtml_Catalog_Category_Tab_Product_Product
{
    /**
     * Render column
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        return $this->getProduct($row->getEntityId())->getTypeId();
    }
}
