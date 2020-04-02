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

class SubscribePro_Autoship_Block_Echeck_Profile_Grid extends Mage_Payment_Block_Form
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    public function getPaymentProfiles()
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $collection = $vaultHelper->getBankAccountProfilesForCustomer($customer);

        return $collection;
    }

}
