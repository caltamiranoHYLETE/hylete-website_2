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

class SubscribePro_Autoship_Helper_Platform_Abstract extends Mage_Core_Helper_Abstract
{

    /**
     * @return SubscribePro_Autoship_Helper_Api
     */
    public function getApiHelper()
    {
        /** @var SubscribePro_Autoship_Helper_Api $helper */
        $helper = Mage::helper('autoship/api');

        return $helper;
    }

    /**
     * @return SubscribePro_Autoship_Helper_Cache
     */
    public function getCacheHelper()
    {
        /** @var SubscribePro_Autoship_Helper_Cache $helper */
        $helper = Mage::helper('autoship/cache');

        return $helper;
    }

    /**
     * @return \SubscribePro\Tools\Config
     */
    public function getConfigTool()
    {
        return $this->getApiHelper()->getSdk()->getConfigTool();
    }

    /**
     * @return \SubscribePro\Service\Customer\CustomerService
     */
    public function getCustomerService()
    {
        return $this->getApiHelper()->getSdk()->getCustomerService();
    }

    /**
     * @return \SubscribePro\Service\Product\ProductService
     */
    public function getProductService()
    {
        return $this->getApiHelper()->getSdk()->getProductService();
    }

    /**
     * @return \SubscribePro\Service\Subscription\SubscriptionService
     */
    public function getSubscriptionService()
    {
        return $this->getApiHelper()->getSdk()->getSubscriptionService();
    }

    /**
     * @return \SubscribePro\Service\PaymentProfile\PaymentProfileService
     */
    public function getPaymentProfileService()
    {
        return $this->getApiHelper()->getSdk()->getPaymentProfileService();
    }

    /**
     * @return \SubscribePro\Service\Token\TokenService
     */
    public function getTokenService()
    {
        return $this->getApiHelper()->getSdk()->getTokenService();
    }

    /**
     * @return \SubscribePro\Service\Transaction\TransactionService
     */
    public function getTransactionService()
    {
        return $this->getApiHelper()->getSdk()->getTransactionService();
    }

    /**
     * @return \SubscribePro\Service\Address\AddressService
     */
    public function getAddressService()
    {
        return $this->getApiHelper()->getSdk()->getAddressService();
    }

}
