<?php

/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All Rights Reserved.
 */

/**
 * Helper class
 *
 * Class Mediotype_HyleteBase_Helper_Justuno
 */
class Mediotype_HyleteBase_Helper_Justuno extends Mage_Core_Helper_Abstract
{
    const SESSION_DATA_KEY_CART_ADD = 'product_to_shopping_cart';
    const SESSION_DATA_KEY_CART_REMOVE = 'product_from_shopping_cart';

    const XML_PATH_CONFIG_JUSTUNO_ENABLED = 'mediotype_justuno/mediotype_justuno/enable';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfigFlag(self::XML_PATH_CONFIG_JUSTUNO_ENABLED);
    }

    /**
     * @param $productData
     * @param $sessionDataKey
     */
    public function pushToSession($productData, $sessionDataKey)
    {
        $currentData = Mage::getModel('core/session')->getData($sessionDataKey, false);

        if (!isset($currentData) || !is_array($currentData)) {
            $currentData = array();
        }

        $currentData[] = $productData;

        Mage::getModel('core/session')->setData($sessionDataKey, $currentData);
    }

    /**
     * @param $key
     * @return bool|array
     */
    public function readFromSession($key)
    {
        $session = Mage::getModel('core/session');

        $itemData = $session->getData($key, true);

        if (!isset($itemData)) {
            return false;
        }

        return reset($itemData);
    }

    public function getCartItemOutput()
    {
        $return = [];

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $cartItems = $quote->getAllVisibleItems();
        foreach ($cartItems as $item) {
            $product = [];

            $product['itemid'] = $item->getProductId();
            $product['quantity'] = $item->getQty();
            $product['price'] = $item->getProduct()->getFinalPrice();
            $product['name'] = $item->getName();
            $product['size'] = $item->getProduct()->getAttributeText('shoe_size');
            $product['color'] = $item->getProduct()->getAttributeText('color');

            array_push($return, $product);
        }

        return Mage::helper('core')->jsonEncode($return);
    }
}
