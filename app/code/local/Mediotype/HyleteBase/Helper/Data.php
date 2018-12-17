<?php

/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All Rights Reserved.
 */

/**
 * Helper class
 *
 * Class Mediotype_HyleteBase_Helper_Data
 */
class Mediotype_HyleteBase_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_PATH_CONFIG_NEXTOPIA_ENABLED = 'mediotype_justuno/mediotype_nextopia/enable';

    /**
     * @return bool
     */
    public function isNextopiaTrackingEnabled()
    {
        return (bool) Mage::getStoreConfigFlag(self::XML_PATH_CONFIG_NEXTOPIA_ENABLED);
    }

    /**
     * @return array
     */
    public function getCartItemSkuArray() {
        $return = [];

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $cartItems = $quote->getAllVisibleItems();
        foreach ($cartItems as $item) {
            $product = [];
            $product['sku'] = $item->getProduct()->getSku();
            array_push($return, $product);
        }

        return $return;
    }
}