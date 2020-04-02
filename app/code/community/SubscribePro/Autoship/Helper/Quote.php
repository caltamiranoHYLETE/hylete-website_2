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

class SubscribePro_Autoship_Helper_Quote extends Mage_Core_Helper_Abstract
{

    /**
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @return Mage_Catalog_Model_Product
     */
    public function getRelevantProductFromQuoteItem(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        if ($quoteItem->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            return $quoteItem->getOptionByCode('simple_product')->getProduct();
        }
        else {
            return $quoteItem->getProduct();
        }
    }

    /**
     * @param Mage_Sales_Model_Order_Item $quoteItem
     * @return Mage_Catalog_Model_Product
     */
    public function getRelevantProductFromOrderItem(Mage_Sales_Model_Order_Item $quoteItem)
    {
        if ($quoteItem->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            return $quoteItem->getOptionByCode('simple_product')->getProduct();
        }
        else {
            return $quoteItem->getProduct();
        }
    }

    /**
     * Depending on configuration return one of:  'save', 'no_save' or 'use_checkbox'
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return string
     */
    public function getSaveCardMode(Mage_Sales_Model_Quote $quote)
    {
        // Get flags from quote and config
        $checkoutSaveCardCheckbox = (bool) Mage::getStoreConfig('payment/' . SubscribePro_Autoship_Model_Payment_Method_Cc::METHOD_CODE . '/checkout_save_card_checkbox', $quote->getStoreId());
        $alwaysSaveCard = (bool) Mage::getStoreConfig('payment/' . SubscribePro_Autoship_Model_Payment_Method_Cc::METHOD_CODE . '/always_save_card', $quote->getStoreId());
        $isGuestCheckout = ($quote->getCheckoutMethod() == Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST);

        // Never save card - if guest checkout
        if ($isGuestCheckout) {
            return 'no_save';
        }
        // This is logged in customer
        else {
            // Always save card - if quote has any subscriptions in it
            if($this->hasProductsToCreateNewSubscription($quote)) {
                return 'save';
            }
            // Display checkbox - if 'save card' checkbox config option turned on
            else if ($checkoutSaveCardCheckbox) {
                return 'use_checkbox';
            }
            // 'Save card' checkbox option is off
            else {
                // Force save card - if this is logged in customer and 'save card' checkbox option is off and 'Always save' option is on
                if ($alwaysSaveCard) {
                    return 'save';
                }
                // Otherwise no save
                else {
                    return 'no_save';
                }
            }
        }
    }

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     */
    public function addAdditionalOptionsToOrderItem(Mage_Sales_Model_Order_Item $orderItem)
    {
        if($orderItem->getData('item_fulfils_subscription')) {
            // Get options
            $options = $orderItem->getProductOptions();
            // Get existing additional_options
            if(isset($options['additional_options']) && is_array($options['additional_options'])) {
                $additionalOptions = $options['additional_options'];
            }
            else {
                $additionalOptions = array();
            }
            // Add our details
            $additionalOptions[] = array(
                'label' => $this->__('Product Subscription Id'),
                'value' => $orderItem->getData('subscription_id'),
            );
            $additionalOptions[] = array(
                'label' => $this->__('Subscription Interval'),
                'value' => $orderItem->getData('subscription_interval'),
            );
            // Set new additional_options on order item
            $options['additional_options'] = $additionalOptions;
            $orderItem->setProductOptions($options);
        }
    }

    /**
     * Does current quote (passed in quote or current shopping cart in session) have any products which are flagged for subscription?
     *
     * @param Mage_Sales_Model_Quote $quote Quote to check.  If null, method will check quote from cart session
     * @return bool
     */
    public function hasProductsToCreateNewSubscription(Mage_Sales_Model_Quote $quote = null)
    {
        // Get platform helper
        /** @var SubscribePro_Autoship_Helper_Product $productHelper */
        $productHelper = Mage::helper('autoship/product');

        // If passed in quote is empty, get quote from cart in session
        if($quote == null) {
            if (Mage::app()->getStore()->isAdmin()) {
                $quote = Mage::getSingleton("adminhtml/session_quote")->getQuote();
            } else {
                // Get cart, quote and quote item
                /** @var Mage_Checkout_Model_Cart $cart */
                $cart = Mage::getSingleton('checkout/cart');
                // Get quote
                $quote = $cart->getQuote();
            }
        }
        // Iterate items in quote
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            // Get subscription product profile
            $product = $this->getRelevantProductFromQuoteItem($quoteItem);
            // Lookup whether product enabled / disabled for subscription
            $isProductEnabled = $productHelper->isAvailableForSubscription($product, $quote->getStore());
            // Check product profile, if this isn't a subscription product, ignore it
            if ($isProductEnabled) {
                // Check quote item flag which indicates we should create a new subscription for this product
                if ($quoteItem->getData('create_new_subscription_at_checkout')) {
                    return true;
                }
            }
        }

        // Didn't find any, return false
        return false;
    }

    /**
     * @param Mage_Sales_Model_Quote|null $quote
     * @return bool
     */
    public function hasSubscriptionReorderProduct($quote = null)
    {
        // If passed in quote is empty, get quote from cart in session
        if($quote == null) {
            // Get cart, quote and quote item
            /** @var Mage_Checkout_Model_Cart $cart */
            $cart = Mage::getSingleton('checkout/cart');
            // Get quote
            $quote = $cart->getQuote();
        }
        // Iterate items in quote
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            // Check quote item attributes
            $itemFulfilsSubscription = $quoteItem->getData('item_fulfils_subscription');
            $itemCreatesNewSubscription = $quoteItem->getData('create_new_subscription_at_checkout');
            // Check quote item flag which indicates we should create a new subscription for this product
            if ($itemFulfilsSubscription && !$itemCreatesNewSubscription) {
                return true;
            }
        }

        // Didn't find any, return false
        return false;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param null $params
     */
    public function onCheckoutCartAddProductComplete(Mage_Catalog_Model_Product $product, $params = null)
    {
        if (is_null($params)) {
            // Get params from request
            $request = Mage::app()->getRequest();
            $params = $request->getParams();
        }
        // Filter delivery params
        $deliveryOption = isset($params['delivery-option']) ? $params['delivery-option'] : '';
        $deliveryInterval = isset($params['delivery-interval']) ? $params['delivery-interval'] : '';
        $requestQty = isset($params['qty']) ? $params['qty'] : 1;

        $this->updateProductInCart($product, $deliveryOption, $deliveryInterval);
    }

    /**
     * @param Mage_Catalog_Model_Product $groupedProduct
     * @param null $params
     */
    public function onCheckoutCartAddGroupedProductComplete(Mage_Catalog_Model_Product $groupedProduct, $params = null)
    {
        if (is_null($params)) {
            // Get request info
            // Get params from request
            $request = Mage::app()->getRequest();
            $params = $request->getParams();
        }
        $superGroupParam = isset($params['super_group']) ? $params['super_group'] : '';
        $deliveryOptionParam = isset($params['delivery-option']) ? $params['delivery-option'] : '';
        $deliveryIntervalParam = isset($params['delivery-interval']) ? $params['delivery-interval'] : '';

        // Get product type instance
        $typeInstance = $groupedProduct->getTypeInstance(true);
        // Iterate through associated products and handle 1 at a time
        /** @var Mage_Catalog_Model_Product $product */
        foreach ($typeInstance->getAssociatedProducts($groupedProduct) as $product) {
            // Check if product was added to the cart
            if (isset($superGroupParam[$product->getId()])) {
                // Check if quantity added was > 0
                if ($superGroupParam[$product->getId()] > 0) {
                    // Get params
                    // Update cart
                    if (Mage::app()->getStore()->isAdmin()) {
                        //If in admin, there is no parent item, so transfer custom options
                        $product->setCustomOptions(array(
                            'product_type' => new Varien_Object(array(
                                'value' => 'grouped',
                                'code' => 'product_type'
                            ))
                        ));
                    }
                    $this->updateProductInCart(
                        $product,
                        is_array($deliveryOptionParam) ? $deliveryOptionParam[$product->getId()] : '',
                        is_array($deliveryOptionParam) ? $deliveryIntervalParam[$product->getId()] : '');

                }
            }
        }
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return Mage::app()->getStore()->isAdmin()
            ? Mage::getSingleton("adminhtml/session_quote")->getQuote()
            : Mage::getSingleton("checkout/cart")->getQuote();
    }

    /**
     * @return Mage_Adminhtml_Model_Session_Quote|Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::app()->getStore()->isAdmin()
            ? Mage::getSingleton("adminhtml/session_quote")
            : Mage::getSingleton("checkout/session");
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param string $deliveryOption
     * @param string $deliveryInterval
     */
    protected function updateProductInCart(Mage_Catalog_Model_Product $product, $deliveryOption, $deliveryInterval)
    {
        // Get platform helper
        /** @var SubscribePro_Autoship_Helper_Platform_Product $platformProductHelper */
        $platformProductHelper = Mage::helper('autoship/platform_product');
        /** @var SubscribePro_Autoship_Helper_Product $productHelper */
        $productHelper = Mage::helper('autoship/product');

        // Get quote
        $quote = $this->_getQuote();

        // Check if product is enabled
        // In admin, if the product isn't available for subscription it won't have an ID yet
        if (!$productHelper->isAvailableForSubscription($product, $quote->getStore())) {
            return;
        }

        //Get quote item
        $quoteItem = $quote->getItemByProduct($product);
        if ($quoteItem == null || !strlen($quoteItem->getId())) {
            // Not sure why this would ever happen, but just to be safe
            // Return here in case the quote item hasn't been saved yet (*should* only happen if isn't enabled for subscription)
            if (Mage::app()->getStore()->isAdmin() && !is_null($quoteItem)) {
                return;
            }
            Mage::throwException($this->__('Cant find quote item which was added!'));
        }

        // Get subscription product profile
        $platformProduct = $platformProductHelper->getPlatformProduct($product);

        // Get new product qty from cart / quote
        $quoteQty = $quoteItem->getQty();

        // Apply default delivery option if none set
        if (!strlen($deliveryOption)) {
            if ($platformProduct->getSubscriptionOptionMode() != 'subscription_only'
                && $platformProduct->getDefaultSubscriptionOption() == 'onetime_purchase'
            ) {
                $deliveryOption = 'one-time-delivery';
            } else {
                $deliveryOption = 'subscribe';
            }
        }

        // Implement trial subscription functionality
        if ($platformProduct->getIsTrialProduct()) {
            // Force subscription delivery option
            $deliveryOption = 'subscribe';
            // Set trial price on quote item
            $quoteItem->setCustomPrice($platformProduct->getTrialPrice());
            $quoteItem->setOriginalCustomPrice($platformProduct->getTrialPrice());
        }

        // Only do error messages if added product is set for subscription
        if ($deliveryOption == 'subscribe') {
            // Check qty to max sure we're in min - max range for subscription
            // Check the new quantity in the cart after addition
            $removeItem = false;
            if($quoteQty < $platformProduct->getMinQty()) {
                $this->_getCheckoutSession()->addError(
                    $this->__('Item %s requires minimum quantity of %s for subscription.',
                        $product->getSku(),
                        $platformProduct->getMinQty()
                    ));
                //If invalid qty and subscription only, remove the product
                if ($platformProduct->getSubscriptionOptionMode() == 'subscription_only') {
                    $removeItem = true;
                }
            }
            if($quoteQty > $platformProduct->getMaxQty()) {
                $this->_getCheckoutSession()->addError(
                    $this->__('Item %s allows maximum quantity of %s for subscription.',
                        $product->getSku(),
                        $platformProduct->getMaxQty()
                    ));
                //If invalid qty and subscription only, remove the product
                if ($platformProduct->getSubscriptionOptionMode() == 'subscription_only') {
                    $removeItem = true;
                }
            }
            // If we found a bug when adding the item (and it's admin) we have to remove it from cart
            if ($removeItem) {
                $product = $quoteItem->getProduct();
                // We have to remove the item this way due to a bug in Magento
                foreach($quote->getItemsCollection() as $key => $compItem) {
                    if ($compItem->getId() == $quoteItem->getId()) {
                        $quote->removeItem($key);
                        $quoteItem->delete();
                        break;
                    }
                }
                $quote->setTotalsCollectedFlag(false);
                $quote->save();
                if (!Mage::app()->getStore()->isAdmin()) {
                    Mage::app()->getResponse()->setRedirect($product->getProductUrl())->sendResponse();
                }
                return;
            }
        }

        // Set data on quote item
        // Only set subscription option on quote item if we are in we meet all conditions
        if($quoteQty >= $platformProduct->getMinQty() && $quoteQty <= $platformProduct->getMaxQty()) {
            $quoteItem->setData('create_new_subscription_at_checkout', ($deliveryOption == 'subscribe'));
        }
        else {
            $quoteItem->setData('create_new_subscription_at_checkout', false);
        }
        // Apply default interval if no interval set
        if (!strlen($deliveryInterval)) {
            $deliveryInterval = $platformProduct->getDefaultInterval();
        }
        if (!strlen($deliveryInterval)) {
            // If no default interval, go for the 1st one in the list
            if (count($intervals = $platformProduct->getIntervals())) {
                $deliveryInterval = $intervals[0];
            }
        }
        // Set interval on quote item regardless
        $quoteItem->setData('new_subscription_interval', $deliveryInterval);
        // Save quote item
        $quoteItem->save();
        // Recalculate quote after item save - in case discounting, etc is affected
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $quote->save();
    }

    /**
     * @param Mage_Checkout_Model_Cart $cart
     * @param array $data
     */
    public function onCheckoutCartUpdateItemsAfter(Mage_Checkout_Model_Cart $cart, $data)
    {
        $this->updateQuoteItems($cart->getQuote(), $data);
    }

    /**
     * Update the items in a quote based on the incoming date
     * @param Mage_Sales_Model_Quote $quote
     * @param $data
     * @return $this
     */
    public function updateQuoteItems(Mage_Sales_Model_Quote $quote, $data)
    {
        // Get platform helper
        /** @var SubscribePro_Autoship_Helper_Platform_Product $platformProductHelper */
        $platformProductHelper = Mage::helper('autoship/platform_product');
        /** @var SubscribePro_Autoship_Helper_Product $productHelper */
        $productHelper = Mage::helper('autoship/product');
        /** @var SubscribePro_Autoship_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('autoship/api');

        $isAdmin = Mage::app()->getStore()->isAdmin();
        $session = $this->_getCheckoutSession();

        // Set store on api helper
        $apiHelper->setConfigStore($quote->getStore());
        // Iterate items in quote
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $hasError = false;
            // Get corresponding data for this quote item
            $itemDeliveryOption = isset($data[$quoteItem->getId()]['delivery-option']) ? $data[$quoteItem->getId()]['delivery-option'] : '';
            $itemDeliveryInterval = isset($data[$quoteItem->getId()]['delivery-interval']) ? $data[$quoteItem->getId()]['delivery-interval'] : '';
            // Get subscription product profile
            $product = $this->getRelevantProductFromQuoteItem($quoteItem);
            // Check if product is enabled for subscription
            // Check product profile, if this isn't a subscription product, ignore it
            if ($productHelper->isAvailableForSubscription($product, $quote->getStore())) {
                // Get platform product
                $platformProduct = $platformProductHelper->getPlatformProduct($product);
                // Only do error messages if added product is set for subscription
                if ($itemDeliveryOption == 'subscribe') {
                    // Check qty to max sure we're in min - max range for subscription
                    if($quoteItem->getQty() < $platformProduct->getMinQty()) {
                        $session->addError(
                            $this->__('Item %s requires minimum quantity of %s for subscription.',
                                $product->getSku(),
                                $platformProduct->getMinQty()
                            ));
                        $hasError = true;
                    }
                    if($quoteItem->getQty() > $platformProduct->getMaxQty()) {
                        $session->addError(
                            $this->__('Item %s allows maximum quantity of %s for subscription.',
                                $product->getSku(),
                                $platformProduct->getMaxQty()
                            ));
                        $hasError = true;
                    }
                }

                // Set data on quote item
                // Only set subscription option on quote item if we are in we meet all conditions
                if($quoteItem->getQty() >= $platformProduct->getMinQty() && $quoteItem->getQty() <= $platformProduct->getMaxQty()) {
                    $quoteItem->setData('create_new_subscription_at_checkout', ($itemDeliveryOption == 'subscribe'));
                }
                else if ($platformProduct->getSubscriptionOptionMode() != 'subscription_only') {
                    $quoteItem->setData('create_new_subscription_at_checkout', false);
                }
                $quoteItem->setData('new_subscription_interval', $itemDeliveryInterval);
                /*
                 * We have to save the admin item if there is no error for the below situation:
                 * 1 item has an error and 1 does not
                 * Without saving the one that does not, all items will revert to "no subscription"
                 */
                if ($isAdmin) {
                    if ($hasError && $quoteItem->getOrigData('create_new_subscription_at_checkout')
                        && $platformProduct->getSubscriptionOptionMode() == 'subscription_only'
                    ) {
                        //If this product can only be subscription, and an invalid qty was requested, revert the quantity
                        $quoteItem->setQty($quoteItem->getOrigData('qty'));
                        $buyRequest = $quoteItem->getBuyRequest();
                        $buyRequest->setData('qty', $quoteItem->getOrigData('qty'));
                        $optionValue = @serialize($buyRequest->getData());
                        $quoteItem->getOptionByCode('info_buyRequest')->setData('value', $optionValue)->save();
                        $quoteItem->save();
                    } else if (!$hasError) {
                        //Otherwise if there's no error make an update
                        $quoteItem->save();
                    }
                }
//                if (!$hasError && $isAdmin) {
//                    $quoteItem->save();
//                }
            }
        }
        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @param Mage_Sales_Model_Order_Item $orderItem
     */
    public function onSalesConvertQuoteItemToOrderItem(Mage_Sales_Model_Quote_Item $quoteItem, Mage_Sales_Model_Order_Item $orderItem)
    {
        // Map additional options from quote item (from the buy request) to order item
        // TODO:    It would be ideal if we can get the additional_options from buy request into additional_options option field in
        //          the quote item at time quote item is created
        $buyRequest = unserialize($quoteItem->getOptionByCode('info_buyRequest')->getValue());
        if (isset($buyRequest['additional_options']) && count($buyRequest['additional_options'])) {
            $additionalOptions = $buyRequest['additional_options'];
            $options = $orderItem->getProductOptions();
            $options['additional_options'] = $additionalOptions;
            $orderItem->setProductOptions($options);
        }
        // Set fields / attributes from quote on to order item
        $orderItem->setData('item_fulfils_subscription', $quoteItem->getData('item_fulfils_subscription'));
        $orderItem->setData('subscription_id', $quoteItem->getData('subscription_id'));
        $orderItem->setData('subscription_interval', $quoteItem->getData('subscription_interval'));
        $orderItem->setData('subscription_reorder_ordinal', $quoteItem->getData('subscription_reorder_ordinal'));
        $orderItem->setData('subscription_next_order_date', $quoteItem->getData('subscription_next_order_date'));
        // If this item fulfils a subscription, update additional_information
        if($quoteItem->getData('item_fulfils_subscription')) {
            $this->addAdditionalOptionsToOrderItem($orderItem);
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Quote $quote
     */
    public function onSalesConvertOrderToQuote(Mage_Sales_Model_Order $order, Mage_Sales_Model_Quote $quote)
    {
        /** @var SubscribePro_Autoship_Helper_Payment $paymentHelper */
        $paymentHelper = Mage::helper('payment');
        // Check for subscribe pro vault pay method
        if($paymentHelper->isSubscribeProCreditCardMethod($order->getPayment()->getMethod())) {
            // Quote was using SP vault pay method
            // Reset payment data fields on order
            $quote->getPayment()->setData('cc_type', '');
            $quote->getPayment()->setData('cc_number', '');
            $quote->getPayment()->setData('cc_exp_month', '');
            $quote->getPayment()->setData('cc_exp_year', '');
            $quote->getPayment()->setAdditionalInformation('save_card', '');
            $quote->getPayment()->setAdditionalInformation('is_new_card', '');
            $quote->getPayment()->setAdditionalInformation('payment_token', '');
            $quote->getPayment()->setAdditionalInformation('obscured_cc_number', '');
        }
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Order $order
     * @return $this
     */
    public function onCheckoutSubmitAllAfter(Mage_Sales_Model_Quote $quote, Mage_Sales_Model_Order $order)
    {
        // Create subscriptions
        $createdSubscriptions = $this->createSubscriptionsFromQuote($quote, $order);
        // Check result
        if(count($createdSubscriptions)) {
            // At least 1 subscription was created, set flag to display message on thank you page
            Mage::getSingleton('checkout/session')->setData('created_subscription_ids', array_keys($createdSubscriptions));
        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function createSubscriptionsFromQuote(Mage_Sales_Model_Quote $quote, Mage_Sales_Model_Order $order)
    {
        // Only create subscriptions from front-end and admin orders, never from API orders
        /** @var Mage_Api_Model_Server $apiServer */
        $apiServer = Mage::getSingleton('api/server');
        /** @var Mage_Api_Model_Session $apiSession */
        $apiSession = Mage::getSingleton('api/session');
        if ($apiSession->isLoggedIn() || strlen($apiServer->getApiName())) {
            return array();
        }

        /** @var SubscribePro_Autoship_Helper_Platform_Customer $customerHelper */
        $customerHelper = Mage::helper('autoship/platform_customer');
        /** @var SubscribePro_Autoship_Helper_Api $apiHelper */
        $apiHelper = Mage::helper('autoship/api');

        // Set store on api helper
        $apiHelper->setConfigStore($quote->getStore());

        // Find customer and payment details
        $spCustomerId = $customerHelper->fetchSubscribeProCustomerId($quote->getCustomer());
        if (!strlen($spCustomerId)) {
            // Create customer because didn't exist
            $platformCustomer = $customerHelper->createOrUpdatePlatformCustomer($quote->getCustomer());
            $spCustomerId = $platformCustomer->getId();
        }

        // Maintain failed subscription count in session
        Mage::getSingleton('checkout/session')->setData('failed_subscription_count', 0);

        // Keep track of subscriptions created
        $subscriptions = array();

        // Go through quote and get list of all items with their shipping addresses
        $quoteItems = $this->collectRelevantQuoteItems($quote);

        // Iterate items
        foreach ($quoteItems as $quoteItemDetails) {
            $subscription = $this->checkAndCreateSubscriptionAndUpdateQuoteItem($spCustomerId, $order, $quoteItemDetails['quoteItem'], $quoteItemDetails['shippingAddress']);
            if ($subscription != null) {
                $subscriptions[] = $subscription;
            }
        }

        // Return array of created subscriptions
        return $subscriptions;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    private function collectRelevantQuoteItems(Mage_Sales_Model_Quote $quote)
    {
        $productTypesNoShippingAddress = array(
            Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL,
            Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE,
        );

        $quoteItemDetails = array();

        // Iterate shipping addresses
        /** @var Mage_Sales_Model_Quote_Address $curAddress */
        foreach ($quote->getAllShippingAddresses() as $curAddress) {
            // Iterate items in address
            /** @var Mage_Sales_Model_Quote_Item $quoteItem */
            foreach ($curAddress->getAllItems() as $quoteItem) {
                // For product types which DO require shipping address
                if (!in_array($quoteItem->getProductType(), $productTypesNoShippingAddress)) {
                    $quoteItemDetails[] = array('quoteItem' => $quoteItem, 'shippingAddress' => $curAddress);
                }
            }
        }
        // Now iterate all products to get virtual products or other types not requiring shipping
        // Iterate items
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            // For product types which DO NOT require shipping address
            if (in_array($quoteItem->getProductType(), $productTypesNoShippingAddress)) {
                $quoteItemDetails[] = array('quoteItem' => $quoteItem, 'shippingAddress' => null);
            }
        }

        return $quoteItemDetails;
    }

    /**
     * @param $spCustomerId
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @param Mage_Sales_Model_Quote_Address|null $shippingAddress
     * @return null|\SubscribePro\Service\Subscription\SubscriptionInterface
     */
    private function checkAndCreateSubscriptionAndUpdateQuoteItem($spCustomerId, Mage_Sales_Model_Order $order, Mage_Sales_Model_Quote_Item $quoteItem, $shippingAddress)
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Product $platformProductHelper */
        $platformProductHelper = Mage::helper('autoship/platform_product');
        /** @var SubscribePro_Autoship_Helper_Product $productHelper */
        $productHelper = Mage::helper('autoship/product');

        // Get subscription product profile
        $product = $this->getRelevantProductFromQuoteItem($quoteItem);
        // Check if product is enabled for subscription
        // Check product profile, if this isn't a subscription product, ignore it
        if ($productHelper->isAvailableForSubscription($product, $quoteItem->getQuote()->getStore())) {
            // Retrieve product details from SP
            $platformProduct = $platformProductHelper->getPlatformProduct($product);
            // Check quote item flag which indicates we should create a new subscription for this product
            if ($quoteItem->getData('create_new_subscription_at_checkout')
                || $platformProduct->getSubscriptionOptionMode() == 'subscription_only'
                || $platformProduct->getDefaultSubscriptionOption() == 'subscription')
            {
                $interval = strlen($quoteItem->getData('new_subscription_interval')) ? $quoteItem->getData('new_subscription_interval') : $platformProduct->getDefaultInterval();
                // For virtual products, set billing address as shipping address
                $subscription = $this->createSubscriptionAndUpdateQuoteItem($spCustomerId, $order, $quoteItem, $shippingAddress, $interval);

                return $subscription;
            }
        }

        return null;
    }

    /**
     * @param $spCustomerId
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @param Mage_Sales_Model_Quote_Address|null $shippingAddress
     * @param string $interval
     * @return null|\SubscribePro\Service\Subscription\SubscriptionInterface
     */
    private function createSubscriptionAndUpdateQuoteItem($spCustomerId, Mage_Sales_Model_Order $order, Mage_Sales_Model_Quote_Item $quoteItem, $shippingAddress, $interval)
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Subscription $subscriptionHelper */
        $subscriptionHelper = Mage::helper('autoship/platform_subscription');
        try {
            // Create subscription from this item
            $subscription =
                $this->createSubscriptionFromQuoteItem($spCustomerId, $order, $quoteItem, $shippingAddress, $interval);

            Mage::dispatchEvent('subscribepro_autoship_before_create_subscription_from_quote_item',
                array('subscription' => $subscription, 'quote_item' => $quoteItem));

            // Create subscription via API
            $subscription = $subscriptionHelper->createSubscription($subscription);
            // Save in array
            $subscriptions[$subscription->getId()] = $subscription;
            // Save subscription id and flag on quote item
            $quoteItem->setData('subscription_id', $subscription->getId());
            $quoteItem->setData('subscription_interval', $subscription->getInterval());
            $quoteItem->setData('item_fulfils_subscription', true);
            $quoteItem->save();
            // Lookup order item
            /** @var Mage_Sales_Model_Order_Item $orderItem */
            $orderItem = Mage::getModel('sales/order_item')->load($quoteItem->getId(), 'quote_item_id');
            // Save subscription id and flag on order item
            if(strlen($orderItem->getId())) {
                $orderItem->setData('subscription_id', $subscription->getId());
                $orderItem->setData('subscription_interval', $subscription->getInterval());
                $orderItem->setData('item_fulfils_subscription', true);
                $this->addAdditionalOptionsToOrderItem($orderItem);
                $orderItem->save();
            }

            Mage::dispatchEvent('subscribepro_autoship_after_create_subscription_from_quote_item',
                array('subscription' => $subscription, 'quote_item' => $quoteItem));

            return $subscription;
        }
        catch(\Exception $e) {
            SubscribePro_Autoship::log('Failed to create subscription with error: ' . $e->getMessage(), Zend_Log::ERR);
            SubscribePro_Autoship::logException($e);

            // Increment failed subscription count
            Mage::getSingleton('checkout/session')->setData(
                'failed_subscription_count',
                1 + Mage::getSingleton('checkout/session')->getData('failed_subscription_count')
            );
        }

        return null;
    }

    /**
     * @param $spCustomerId
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @param Mage_Sales_Model_Quote_Address|null $shippingAddress
     * @param string $interval
     * @return \SubscribePro\Service\Subscription\SubscriptionInterface
     * @throws Mage_Core_Exception
     */
    protected function createSubscriptionFromQuoteItem($spCustomerId, Mage_Sales_Model_Order $order, Mage_Sales_Model_Quote_Item $quoteItem, $shippingAddress, $interval)
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Subscription $subscriptionHelper */
        $subscriptionHelper = Mage::helper('autoship/platform_subscription');
        /** @var SubscribePro_Autoship_Helper_Payment $paymentHelper */
        $paymentHelper = Mage::helper('autoship/payment');
        // Get quote
        $quote = $quoteItem->getQuote();
        // Lookup which product is "relevant" to create subscription
        $product = $this->getRelevantProductFromQuoteItem($quoteItem);

        // Empty subscription object
        $subscription = $subscriptionHelper->initSubscription();
        // Customer
        $subscription->setCustomerId($spCustomerId);
        // Send notification
        $subscription->setSendCustomerNotificationEmail(true);
        // Payment
        $paymentMethodCode = $quote->getPayment()->getMethod();
        if ($paymentHelper->isAnySubscribeProPayMethod($paymentMethodCode)) {
            $spPaymentProfileId = $order->getPayment()->getAdditionalInformation('payment_profile_id');
            if (!strlen($spPaymentProfileId)) {
                Mage::throwException('Failed to find Subscribe Pro payment profile ID!');
            }
            $subscription->setPaymentProfileId($spPaymentProfileId);
        }
        else {
            $subscription->setPaymentMethodCode($quote->getPayment()->getMethod());
        }
        // Product
        $subscription->setProductSku($product->getSku());
        $subscription->setQty($quoteItem->getQty());
        // Save coupon code on subscription, if config setting enabled
        if(Mage::getStoreConfig('autoship_subscription/options/allow_coupon', $quote->getStore()) == 1) {
            $subscription->setCouponCode($quote->getCouponCode());
        }
        $subscription->setUseFixedPrice(false);
        // Schedule
        $subscription->setFirstOrderAlreadyCreated(true);
        $subscription->setNextOrderDate(date('Y-m-d'));
        $subscription->setInterval($interval);
        // Magento specific
        $subscription->setMagentoStoreCode($quote->getStore()->getCode());
        $subscription->setPlatformSpecificFields(array(
            'magento1' => array(
                'magento_website_id' => $quote->getStore()->getWebsiteId(),
                'magento_store_code' => $quote->getStore()->getCode(),
                'magento_product_options' => $this->getProductOptionsFromQuoteItem($quoteItem),
                'magento_order_details' => $this->getOrderDetails($order),
            ),
        ));
        // Shipping
        if ($shippingAddress instanceof Mage_Sales_Model_Quote_Address) {
            $subscription->setRequiresShipping(true);
            $subscription->setMagentoShippingMethodCode($shippingAddress->getData('shipping_method'));
            // Shipping Address
            $subscription->getShippingAddress()->setFirstName($shippingAddress->getFirstname());
            $subscription->getShippingAddress()->setLastName($shippingAddress->getLastname());
            $subscription->getShippingAddress()->setCompany($shippingAddress->getCompany());
            $subscription->getShippingAddress()->setStreet1($shippingAddress->getStreet1());
            $subscription->getShippingAddress()->setStreet2($shippingAddress->getStreet2());
            $subscription->getShippingAddress()->setCity($shippingAddress->getCity());
            $subscription->getShippingAddress()->setRegion($shippingAddress->getRegionCode());
            $subscription->getShippingAddress()->setPostcode($shippingAddress->getPostcode());
            $subscription->getShippingAddress()->setCountry($shippingAddress->getCountryId());
            $subscription->getShippingAddress()->setPhone($shippingAddress->getTelephone());
        }
        else {
            $subscription->setRequiresShipping(false);
            $subscription->setShippingAddress(null);
        }

        // Return the new subscription model
        return $subscription;
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @return array
     */
    protected function getProductOptionsFromQuoteItem(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        // Create array to hold product options and then be stored in subscription
        $productOptions = array();
        // Get buy request object
        $buyRequest = $quoteItem->getBuyRequest();
        // If this is a bundle product
        if ($quoteItem->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            // Get bundle options from buy request
            $bundleOption = $buyRequest->getData('bundle_option');
            $bundleOptionQty = $buyRequest->getData('bundle_option_qty');
            if (is_array($bundleOption) && count($bundleOption)) {
                // Save bundle options with subscription
                $productOptions['bundle_option'] = $bundleOption;
                $productOptions['bundle_option_qty'] = $bundleOptionQty;
            }
        }
        // Save custom options
        $customOptions = $buyRequest->getData('options');
        if (is_array($customOptions) && count($customOptions)) {
            $productOptions['options'] = $customOptions;
        }

        return $productOptions;
    }

    /**
     * Get Magento order details in same format as 'sales_order.info' API call
     *
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function getOrderDetails(Mage_Sales_Model_Order $order)
    {
        try {
            /** @var Mage_Sales_Model_Order_Api $salesOrderApi */
            $salesOrderApi = Mage::getSingleton('sales/order_api');
            // Call method which implements API call and get order details
            $orderDetails = $salesOrderApi->info($order->getIncrementId());

            return $orderDetails;
        }
        catch(\Exception $e) {
            SubscribePro_Autoship::logException($e);
            SubscribePro_Autoship::log("Failed to get order details for order # {$order->getIncrementId()}");
            return array();
        }
    }

}
