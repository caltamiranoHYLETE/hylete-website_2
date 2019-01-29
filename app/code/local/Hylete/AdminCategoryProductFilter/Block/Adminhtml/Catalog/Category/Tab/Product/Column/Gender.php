<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

/**
 * Render gender column
 */
class Hylete_AdminCategoryProductFilter_Block_Adminhtml_Catalog_Category_Tab_Product_Column_Gender
    extends Hylete_AdminCategoryProductFilter_Block_Adminhtml_Catalog_Category_Tab_Product_Product
{
    /**
     * Render column
     *
     * @param Varien_Object $row
     * @return bool|string
     * @throws Mage_Core_Exception
     */
    public function render(Varien_Object $row)
    {
        return $this->_render($row, 'gender');
    }
}
