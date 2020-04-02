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

class SubscribePro_Autoship_Block_Hosted_Addressbook extends SubscribePro_Autoship_Block_Hosted_Abstract
{

    public function getCreatedAddresses()
    {
        /** @var SubscribePro_Autoship_Helper_Hosted $hostedHelper */
        $hostedHelper = Mage::helper('autoship/hosted');

        // Get address details
        $addresses = $hostedHelper->getAddressDetails(SubscribePro_Autoship_Helper_Hosted::CREATED_ADDRESS_DETAILS_SESSION_KEY);
        // Now wipe them from session
        $hostedHelper->wipeAddressDetails(SubscribePro_Autoship_Helper_Hosted::CREATED_ADDRESS_DETAILS_SESSION_KEY);

        return $addresses;
    }

    public function getUpdatedAddresses()
    {
        /** @var SubscribePro_Autoship_Helper_Hosted $hostedHelper */
        $hostedHelper = Mage::helper('autoship/hosted');

        // Get address details
        $addresses = $hostedHelper->getAddressDetails(SubscribePro_Autoship_Helper_Hosted::UPDATED_ADDRESS_DETAILS_SESSION_KEY);
        // Now wipe them from session
        $hostedHelper->wipeAddressDetails(SubscribePro_Autoship_Helper_Hosted::UPDATED_ADDRESS_DETAILS_SESSION_KEY);

        return $addresses;
    }

    public function getDeletedAddresses()
    {
        /** @var SubscribePro_Autoship_Helper_Hosted $hostedHelper */
        $hostedHelper = Mage::helper('autoship/hosted');

        // Get deleted address details
        $addresses = $hostedHelper->getAddressDetails(SubscribePro_Autoship_Helper_Hosted::DELETED_ADDRESS_DETAILS_SESSION_KEY);
        // Now wipe them from session
        $hostedHelper->wipeAddressDetails(SubscribePro_Autoship_Helper_Hosted::DELETED_ADDRESS_DETAILS_SESSION_KEY);

        return $addresses;
    }
}
