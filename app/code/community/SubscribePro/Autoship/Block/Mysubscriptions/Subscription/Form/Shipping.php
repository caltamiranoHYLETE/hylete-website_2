<?php
/**
 * Subscribe Pro - Subscriptions Management Extension
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of SUBSCRIBE PRO INC.
 *
 * @category  SubscribePro
 * @package   SubscribePro_Autoship
 * @author    Subscribe Pro Support <support@subscribepro.com>
 * @copyright 2009-2020 SUBSCRIBE PRO INC. All Rights Reserved.
 * @license   http://www.subscribepro.com/terms-of-service/ Subscribe Pro Terms of Service
 * @link      http://www.subscribepro.com/
 *
 */

class SubscribePro_Autoship_Block_Mysubscriptions_Subscription_Form_Shipping extends SubscribePro_Autoship_Block_Mysubscriptions_Subscription
{

    public function getShippingAddress()
    {
        return $this->getSubscription()->getShippingAddress();
    }

    public function formatAddress(\SubscribePro\Service\Address\AddressInterface $address)
    {
        $formattedOutput = '';
        if (strlen($address->getFirstName())) {
            $formattedOutput .= $address->getFirstName();
        }
        if (strlen($address->getLastName())) {
            $formattedOutput .= ' ' . $address->getLastName();
        }
        if (strlen($address->getStreet1())) {
            $formattedOutput .= ', ' . $address->getStreet1();
        }
        if (strlen($address->getStreet2())) {
            $formattedOutput .= ', ' . $address->getStreet2();
        }
        if (strlen($address->getCity())) {
            $formattedOutput .= ', ' . $address->getCity();
        }
        if (strlen($address->getRegion())) {
            $formattedOutput .= ', ' . $address->getRegion();
        }
        if (strlen($address->getPostcode())) {
            $formattedOutput .= ' ' . $address->getPostcode();
        }
        if (strlen($address->getCountry())) {
            $formattedOutput .= ', ' . $address->getCountry();
        }

        return $formattedOutput;
    }

    /**
     * @return Mage_Directory_Model_Resource_Country_Collection
     */
    public function getCountryCollection()
    {
        return Mage::getResourceModel('directory/country_collection')->loadByStore();
    }

    public function getCountryOptions()
    {
        $options    = false;
        $useCache   = Mage::app()->useCache('config');
        if ($useCache) {
            $cacheId    = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
            $cacheTags  = array('config');
            if ($optionsCache = Mage::app()->loadCache($cacheId)) {
                $options = unserialize($optionsCache);
            }
        }
        if ($options == false) {
            $options = $this->getCountryCollection()->toOptionArray();
            if ($useCache) {
                Mage::app()->saveCache(serialize($options), $cacheId, $cacheTags);
            }
        }
        return $options;
    }

}
