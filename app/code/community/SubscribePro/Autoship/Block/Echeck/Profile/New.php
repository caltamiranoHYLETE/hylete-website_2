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

class SubscribePro_Autoship_Block_Echeck_Profile_New extends Mage_Adminhtml_Block_Template
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    /**
     * @return SubscribePro\Service\PaymentProfile\PaymentProfileInterface
     */
    public function getPaymentProfile()
    {
        return $this->getData('payment_profile');
    }

    public function getTitle()
    {
        // New profile
        return 'Enter Bank Account Details';
    }

    public function getBackUrl()
    {
        return $this->getUrl('subscriptions/mybankaccounts/', array('_secure' => true));
    }

    public function getSaveUrl()
    {
        return $this->getUrl('subscriptions/mybankaccounts/save/', array('_secure' => true));
    }

    /**
     * Retrieve payment configuration object
     *
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('payment/config');
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|Mage_Core_Model_Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = Mage::app()->getStore();
        }
        $path = 'payment/' . SubscribePro_Autoship_Model_Payment_Method_Echeck::METHOD_CODE . '/' . $field;

        return Mage::getStoreConfig($path, $storeId);
    }

    public function getLocaleLanguage()
    {
        return locale_get_primary_language(Mage::app()->getLocale()->getLocaleCode());
    }

}
