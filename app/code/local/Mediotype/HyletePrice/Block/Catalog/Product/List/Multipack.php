<?php

/**
 * Multipack offer text display block.
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

class Mediotype_HyletePrice_Block_Catalog_Product_List_Multipack extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * Local constructor.
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('hyleteprice/product/list/multipack.phtml');
    }

    /**
     * Determine whether the block contents may be shown.
     * @return boolean
     */
    protected function _canShow()
    {
        return in_array(
            $this->getCustomerGroupId(),
            array(
                Mediotype_HyletePrice_Helper_Data::NOT_LOGGED_IN,
                Mediotype_HyletePrice_Helper_Data::EVERYDAY_ATHLETE
            )
        );
    }

    /**
     * Block renderer implementation.
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_canShow()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Get the customer group ID of the current visitor.
     * @return integer
     */
    public function getCustomerGroupId()
    {
        return (int) Mage::getSingleton('customer/session')->getCustomerGroupId();
    }

    /**
     * Product instance setter. Implementation forced to establish a known interface.
     * @param Mage_Catalog_Model_Product $product The product model.
     * @return Mediotype_HyletePrice_Block_Catalog_Product_List_Multipack
     */
    public function setProduct(Mage_Catalog_Model_Product $product)
    {
        $this->setData('product', $product);

        return $this;
    }
}
