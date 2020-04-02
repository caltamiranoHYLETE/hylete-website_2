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
 * Block to display each individual subscription on My Subscriptions Page
 */
class SubscribePro_Autoship_Block_Mysubscriptions_Subscription extends Mage_Core_Block_Template
{

    private $subscriptionData = array();
    private $customer = null;
    private $customerPaymentProfiles = array();
    private $customerAddresses = array();
    private $subscription = null;
    private $subscribeProProduct = null;
    private $magentoProduct = null;


    /**
     * @param null|string $blockClass
     * @param string $templateName
     * @return SubscribePro_Autoship_Block_Mysubscriptions_Subscription
     */
    public function createChildSubscriptionBlock($blockClass = null, $templateName)
    {
        // Default for block class
        if ($blockClass == null) {
            $blockClass = 'autoship/mysubscriptions_subscription';
        }
        /** @var SubscribePro_Autoship_Block_Mysubscriptions_Subscription $block */
        $block = $this->getLayout()->createBlock($blockClass);
        $block->setParentBlock($this);
        $block->setTemplate($templateName);
        $block->setSubscriptionData($this->subscriptionData);

        return $block;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setSubscriptionData(array $data)
    {
        $this->subscriptionData = $data;
        $this->customer = $data['customer'];
        $this->customerPaymentProfiles = $data['customer_payment_profiles'];
        $this->customerAddresses = $data['customer_addresses'];
        $this->subscription = $data['subscription'];
        $this->magentoProduct = $data['magento_product'];
        $this->subscribeProProduct = $data['subscribe_pro_product'];

        return $this;
    }

    /**
     * @return null|Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return array
     */
    public function getCustomerPaymentProfiles()
    {
        return $this->customerPaymentProfiles;
    }

    /**
     * @return array
     */
    public function getCustomerAddresses()
    {
        return $this->customerAddresses;
    }

    /**
     * @return null|\SubscribePro\Service\Subscription\SubscriptionInterface
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * Get the product for this subscription
     *
     * @return null|Mage_Catalog_Model_Product The Magento product for this subscription
     */
    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

    /**
     * Get the product profile for this subscription
     *
     * @return null|\SubscribePro\Service\Product\ProductInterface The product product model object for this subscription
     */
    public function getSubscribeProProduct()
    {
        return $this->subscribeProProduct;
    }

    /**
     * Return eligible subscription intervals for the subscription product
     *
     * @return array Array of eligible subscription interval strings (for example: One Month, Two Months, etc)
     */
    public function getIntervals()
    {
        // Lookup intervals from product and subscription
        $productIntervals = $this->getSubscribeProProduct()->getIntervals();
        $subscriptionInterval = $this->getSubscription()->getInterval();
        // Add product interval if not present
        if (!is_array($productIntervals)) {
            return array($subscriptionInterval);
        }
        else if (!in_array($subscriptionInterval, $productIntervals)) {
            return array_merge(array($subscriptionInterval), $productIntervals);
        }
        else {
            return $productIntervals;
        }
    }

    /**
     * @return null|string
     */
    public function getDefaultInterval()
    {
        // Lookup from product
        return $this->getSubscribeProProduct()->getDefaultInterval();
    }

    /**
     * Is this product a trial subscription product?
     *
     * @return boolean
     */
    public function isTrialProduct()
    {
        return ($this->getSubscribeProProduct()->getIsTrialProduct());
    }

    /**
     * Subscription option mode
     *
     * @return string
     */
    public function getSubscriptionOptionMode()
    {
        return ($this->getSubscribeProProduct()->getSubscriptionOptionMode());
    }

    /**
     * Default subscription option
     *
     * @return string
     */
    public function getDefaultSubscriptionOption()
    {
        return ($this->getSubscribeProProduct()->getDefaultSubscriptionOption());
    }

    /**
     * Return the price for purchasing the current product as a one time purchase, optionally format the returned price
     *
     * @param bool $formatted True to return the price formatted, false to return the raw price number
     * @return string Price of product, either formatted or as a raw number
     */
    public function getOneTimePurchasePrice($formatted = false)
    {
        /** @var SubscribePro_Autoship_Helper_Subscription $subscriptionHelper */
        $subscriptionHelper = Mage::helper('autoship/subscription');

        return $subscriptionHelper->getOneTimePurchasePrice($this->getMagentoProduct(), $this->getSubscription()->getQty(), $formatted);
    }

    /**
     * Return the price for purchasing the product with a subscription, optionally format the returned price
     *
     * @param bool $formatted True to return the price formatted, false to return the raw price number
     * @return string Price of product, either formatted or as a raw number
     */
    public function getSubscriptionPrice($formatted = false)
    {
        /** @var SubscribePro_Autoship_Helper_Subscription $subscriptionHelper */
        $subscriptionHelper = Mage::helper('autoship/subscription');

        return $subscriptionHelper->getSubscriptionPrice($this->getSubscribeProProduct(), $this->getMagentoProduct(), $this->getSubscription()->getQty(), $formatted);
    }

    /**
     * Return the price for purchasing the product with a subscription, formatted and with text indicating the discount amount
     *
     * @return string Price of product, formatted and with text indicating the discount
     */
    public function getSubscriptionPriceText()
    {
        /** @var SubscribePro_Autoship_Helper_Subscription $subscriptionHelper */
        $subscriptionHelper = Mage::helper('autoship/subscription');

        return $subscriptionHelper->getSubscriptionPriceText($this->getSubscribeProProduct(), $this->getMagentoProduct(), $this->getSubscription()->getQty());
    }

    /**
     * @return bool
     */
    public function useCouponCode()
    {
        $allowCouponConfig = Mage::getStoreConfig('autoship_subscription/options/allow_coupon');

        return ($allowCouponConfig == 1);
    }

    /**
     * @return false|string
     */
    public function getNextOrderDateText()
    {
        $date = date_create_from_format('Y-m-d', $this->getSubscription()->getNextOrderDate());

        return date_format($date, 'm/d/y');
    }

    /**
     * Returns a formatted version of the shipping address for this subscription, ready for display on page
     *
     * @return string Formatted version of shipping address
     */
    public function getFormattedShippingAddress()
    {
        $address = $this->getSubscription()->getShippingAddress();

        return $address->getStreet1() . ' ' . $address->getStreet2();
    }

    /**
     * Retrieve given media attribute label or product name if no label
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $mediaAttributeCode
     *
     * @return string
     */
    public function getImageLabel($product = null, $mediaAttributeCode = 'image')
    {
        if (is_null($product)) {
            $product = $this->getMagentoProduct();
        }

        $label = $product->getData($mediaAttributeCode . '_label');
        if (empty($label)) {
            $label = $product->getName();
        }

        return $label;
    }

    /**
     * @return string
     */
    public function getNewCardUrl()
    {
        return $this->getUrl('subscriptions/mycreditcards/new/', array('apply_to_subscription_id' => $this->getSubscription()->getId()));
    }

    /**
     * @return string
     */
    public function getNewBankAccountUrl()
    {
        return $this->getUrl('subscriptions/mybankaccounts/new/', array('apply_to_subscription_id' => $this->getSubscription()->getId()));
    }

    /**
     * @return bool
     */
    public function isBankAccountFeatureEnabled()
    {
        return (Mage::getStoreConfig('payment/' . SubscribePro_Autoship_Model_Payment_Method_Echeck::METHOD_CODE . '/active') == '1');
    }

    /**
     * Retrieve payment configuration object
     *
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig()
    {
        /** @var Mage_Payment_Model_Config $paymentConfig */
        $paymentConfig = Mage::getSingleton('payment/config');

        return $paymentConfig;
    }

    public function getPaymentMethodTitle()
    {
        $paymentMethodCode = $this->getSubscription()->getPaymentMethodCode();
        if (!strlen($paymentMethodCode)) {
            return null;
        }
        $paymentMethodTitle = Mage::getStoreConfig("payment/$paymentMethodCode/title");
        if (!strlen($paymentMethodTitle)) {
            return null;
        }

        return $paymentMethodTitle;
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] = $this->__('Month');
            $months = array_merge($months, $this->_getConfig()->getMonths());
            $this->setData('cc_months', $months);
        }

        return $months;
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
            $years = array(0 => $this->__('Year')) + $years;
            $this->setData('cc_years', $years);
        }

        return $years;
    }

    public function getMessages()
    {
        $subscription = $this->getSubscription();
        $paymentProfile = $subscription->getPaymentProfile();

        // Build Output
        $messages = array();

        // Failed order
        if ($subscription->getStatus() == 'Failed' || $subscription->getStatus() == 'Retry') {
            $messages[] = "<li class=\"error\">{$this->__('There was a problem with your last order.')}</li>";
        }

        // Missing shipping address
        if ($subscription->getRequiresShipping() && !strlen($subscription->getShippingAddress()->getId())) {
            $messages[] = "<li class=\"error\">{$this->__('Please enter a shipping address for your subscriptions.')}</li>";
        }

        // Expired CC
        if (strlen($paymentProfile->getId()) && $paymentProfile->getPaymentMethodType() == \SubscribePro\Service\PaymentProfile\PaymentProfile::TYPE_CREDIT_CARD) {
            $ccExp = $paymentProfile->getCreditcardYear() . '-' . sprintf("%02d", $paymentProfile->getCreditcardMonth());
            if ($ccExp < date('Y-m')) {
                $messages[] = "<li class=\"error\">{$this->__('Your credit card is expired.')}</li>";
            }
        }

        // No Payment Method
        if (!strlen($paymentProfile->getId()) && !strlen($subscription->getPaymentMethodCode())) {
            $messages[] = "<li class=\"error\">{$this->__('Please select a payment method.')}</li>";
        }

        return $messages;
    }

}
