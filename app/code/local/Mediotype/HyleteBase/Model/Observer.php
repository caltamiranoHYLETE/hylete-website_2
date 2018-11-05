<?php

/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All Rights Reserved.
 */
class Mediotype_HyleteBase_Model_Observer
{
    /**
     * @param $observer
     */
    public function removeProductFromCart($observer)
    {
        if ($this->_getHelper()->isEnabled()) {
            $item = $observer->getQuoteItem();
            $productData = array($item->getProductId());
            $this->_getHelper()->pushToSession($productData, Mediotype_HyleteBase_Helper_Justuno::SESSION_DATA_KEY_CART_REMOVE);
        }
    }

    /**
     * @param $observer
     */
    public function addProductToCart($observer)
    {
        if ($this->_getHelper()->isEnabled()) {
            $item = $observer->getEvent()->getQuoteItem();
            $product = $observer->getEvent()->getProduct();

            $productData = array($item->getProductId() => ['name' => $item->getName(), 'quantity' => $item->getQty(), 'color' => $item->getProduct()->getAttributeText('color'), 'size' => $item->getProduct()->getAttributeText('shoe_size'), 'price' => $product->getFinalPrice()]);

            $this->_getHelper()->pushToSession($productData, Mediotype_HyleteBase_Helper_Justuno::SESSION_DATA_KEY_CART_ADD);
        }
    }

    /**
     * @return Mage_Core_Helper_Abstract|Mediotype_HyleteBase_Helper_Justuno
     */
    protected function _getHelper()
    {
        return Mage::helper('mediotype_hyletebase/justuno');
    }
}