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

class SubscribePro_Autoship_Block_Payment_Profile_Edit extends Mage_Adminhtml_Block_Template
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

    public function getEnvironmentKey()
    {
        /** @var SubscribePro_Autoship_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('autoship/api');
        // Lookup payment method code based on SP config
        $accountConfig = $apiHelper->getAccountConfig();
        // Get env key
        $environmentKey = $accountConfig['transparent_redirect_environment_key'];

        return $environmentKey;
    }

    public function getTitle()
    {
        if (strlen($this->getPaymentProfile()->getId())) {
            // Editing an existing profile
            return $this->helper('autoship')->__(' Edit ' . $this->getPaymentProfile()->getCustomerFacingName());
        }
        else {
            // New profile
            return 'Enter Credit Card Details';
        }
    }

    public function getSaveNewUrl()
    {
        $applyToSubscriptionId = $this->getData('apply_to_subscription_id');
        if (strlen($applyToSubscriptionId)) {
            return $this->getUrl("subscriptions/mycreditcards/savenew/apply_to_subscription_id/$applyToSubscriptionId", array('_secure' => true));
        }
        else {
            return $this->getUrl('subscriptions/mycreditcards/savenew/', array('_secure' => true));
        }
    }

    public function getBackUrl()
    {
        return $this->getUrl('subscriptions/mycreditcards/', array('_secure' => true));
    }

    public function getSaveUrl()
    {
        return $this->getUrl('subscriptions/mycreditcards/save/', array('_secure' => true));
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
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        $output = array();
        if (is_null($months)) {
            $months[0] = $this->__('Month');
            $months = array_merge($months, $this->_getConfig()->getMonths());
            foreach ($months as $k => $v) {
                if (strlen($k) == 1 && $k != 0) {
                    $value = '0' . $k;
                    $output[$value] = $v;
                }
                elseif ($v != 'Month') {
                    $output[$k] = $v;
                }
            }
            $this->setData('cc_months', $months);
        }

        return $output;
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = $this->_getConfig()->getYears();
            $this->setData('cc_years', $years);
        }

        return $years;
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
        $path = 'payment/' . SubscribePro_Autoship_Model_Payment_Method_Cc::METHOD_CODE . '/' . $field;

        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * Retrieve use verification configuration
     *
     * @return boolean
     */
    public function useVerification()
    {
        return false;
    }

    /**
     * Retrieve available credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        return Mage::getModel('autoship/system_config_source_cctype')->getCcAvailableTypes();
    }

    /**
     * Retrieve available credit card types in Subscribe Pro format
     *
     * @return array
     */
    public function getCcAvailableTypesSubscribeProFormat()
    {
        return Mage::getModel('autoship/system_config_source_cctype')->getCcAvailableTypesSubscribeProFormat();
    }

    public function getLocaleLanguage()
    {
        return locale_get_primary_language(Mage::app()->getLocale()->getLocaleCode());
    }

}
