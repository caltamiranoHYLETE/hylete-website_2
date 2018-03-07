<?php

/**
 * MSRP (Retail Value) price display block.
 * @category  Class
 * @package   Mediotype_HyletePrice
 * @author    Rick Buczynski <rick@mediotype.com>
 * @copyright 2018 Mediotype
 */

/**
 * Class declaration
 * @category Class
 * @package  Mediotype_HyletePrice
 * @author   Rick Buczynski <rick@mediotype.com>
 */

class Mediotype_HyletePrice_Block_Catalog_Product_List_Msrp extends Mage_Catalog_Block_Product_Abstract
{
    protected $_additionalClasses = array();

    /**
     * Local constructor.
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('hyleteprice/product/list/msrp.phtml');
    }

    /**
     * Register an additional element class name.
     * @param string $name The class name to register.
     * @return Mediotype_HyletePrice_Block_Catalog_Product_List_Msrp
     */
    public function addClass($name)
    {
        $this->_additionalClasses[$name] = true;

        return $this;
    }

    /**
     * Get additional user-defined element class names.
     * @return string
     */
    public function getAdditionalClasses()
    {
        return implode(' ', array_keys($this->_additionalClasses));
    }

    /**
     * Product instance setter. Implementation forced to establish a known interface.
     * @param Mage_Catalog_Model_Product $product The product model.
     * @return Mediotype_HyletePrice_Block_Catalog_Product_List_Msrp
     */
    public function setProduct(Mage_Catalog_Model_Product $product)
    {
        $this->setData('product', $product);

        return $this;
    }
}
