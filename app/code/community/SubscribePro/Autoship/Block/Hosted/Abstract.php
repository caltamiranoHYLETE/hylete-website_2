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

/**
 * @method string getWidgetScriptUrl()
 * @method string getApiBaseUrl()
 * @method string getApiAccessToken()
 * @method string getSpreedlyEnvironmentKey()
 * @method string getSubscribeProCustomerId()
 */
class SubscribePro_Autoship_Block_Hosted_Abstract extends Mage_Core_Block_Template
{

    private $isInitialized = false;


    /**
     * @inheritdoc
     */
    protected function _beforeToHtml()
    {
        if (!$this->isInitialized) {
            /** @var SubscribePro_Autoship_Helper_Api $apiHelper */
            $apiHelper = Mage::helper('autoship/api');
            /** @var SubscribePro_Autoship_Helper_Platform_Customer $customerHelper */
            $customerHelper = Mage::helper('autoship/platform_customer');
            /** @var SubscribePro_Autoship_Helper_Hosted $hostedHelper */
            $hostedHelper = Mage::helper('autoship/hosted');

            // Hosted data
            $accessToken = $customerHelper->getWidgetAccessToken($this->getCustomer());
            $accessTokenString = isset($accessToken['access_token']) ? $accessToken['access_token'] : '';
            $spreedlyEnvKey = isset($accessToken['spreedly_environment_key']) ? $accessToken['spreedly_environment_key'] : '';
            $spCustomerId = isset($accessToken['customer_id']) ? $accessToken['customer_id'] : '';

            // Save hosted data
            $this->setData('widget_script_url', $hostedHelper->getWidgetsScriptUrl());
            $this->setData('subscribe_pro_customer_id', $spCustomerId);
            $this->setData('spreedly_environment_key', $spreedlyEnvKey);
            $this->setData('api_access_token', $accessTokenString);
            $this->setData('api_base_url', $apiHelper->getApiBaseUrl());

            $this->isInitialized = true;
        }

        return $this;
    }

    /**
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');
        $customer = $customerSession->getCustomer();

        return $customer;
    }

}
