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
 * Observer class to handle all event observers for subscriptions module
 */
class SubscribePro_Autoship_Model_Observer
{

    protected static $autoloaderRegistered = false;
    protected static $autoloadedNamespaces = array(
        'Psr' => 'Psr',
        'Monolog' => 'Monolog',
        'GuzzleHttp' => 'GuzzleHttp',
        'SubscribePro' => 'SubscribePro',
    );


    /**
     * Add autoloader for namespace'd classes in the lib folder.
     * Limit this to classes in certain namespaces
     *
     * @param Varien_Event_Observer $observer
     */
    public function addAutoloader(Varien_Event_Observer $observer)
    {
        // Check if our autoloader is already registered
        if (!self::$autoloaderRegistered) {

            // Include guzzle functions
            // No easy way to integrate these into Magento autoloader
            include __DIR__.'/../../../../../../lib/GuzzleHttp/Promise/functions_include.php';
            include __DIR__.'/../../../../../../lib/GuzzleHttp/Psr7/functions_include.php';
            include __DIR__.'/../../../../../../lib/GuzzleHttp/functions_include.php';

            // Register our namespace'd libraries in the lib folder.
            // Specifically, for Subscribe Pro PHP SDK and dependencies.
            spl_autoload_register(
                function ($class) {
                    // Build namespace'd class file name
                    $classFile = str_replace('\\', '/', $class) . '.php';
                    // Only include a namespace'd class.  This should leave the regular Magento autoloader alone
                    if (strpos($classFile, '/') !== false) {
                        // Check for certain namespaces
                        list($namespace, $rest) = explode('\\', ltrim($class, '\\'), 2);
                        if (isset(self::$autoloadedNamespaces[$namespace])) {
                            // Include class file
                            include $classFile;
                        }
                    }
                },
                true,
                true
            );

            // Set flag so we only do this once
            self::$autoloaderRegistered = true;
        }

    }

    /**
     * Save Product Subscription Data
     *
     */
    public function onProductSaveCommitAfter(Varien_Event_Observer $observer)
    {
        SubscribePro_Autoship::log('SubscribePro_Autoship_Model_Observer::onProductSaveCommitAfter', Zend_Log::INFO);

        // Get current product
        $product = $observer->getEvent()->getProduct();

        // Check that we have a real product
        if (strlen($product->getId())) {
            // Call helper to update product / product profile in Magento and on platform
            Mage::helper('autoship/platform_product')->handleOnSaveProduct($product);
        }
    }

    public function onCustomerAddressSaveBefore(Varien_Event_Observer $observer)
    {
        SubscribePro_Autoship::log('SubscribePro_Autoship_Model_Observer::onCustomerAddressSaveBefore', Zend_Log::INFO);

        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled') != '1') {
            return;
        }

        /** @var Mage_Customer_Model_Address $address */
        $address = $observer->getData('customer_address');

        /** @var SubscribePro_Autoship_Helper_Hosted $hostedHelper */
        $hostedHelper = Mage::helper('autoship/hosted');

        $hostedHelper->onCustomerAddressSaveBefore($address);
    }

    public function onCustomerAddressSaveCommitAfter(Varien_Event_Observer $observer)
    {
        SubscribePro_Autoship::log('SubscribePro_Autoship_Model_Observer::onCustomerAddressSaveCommitAfter', Zend_Log::INFO);

        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled') != '1') {
            return;
        }

        /** @var Mage_Customer_Model_Address $address */
        $address = $observer->getData('customer_address');

        /** @var SubscribePro_Autoship_Helper_Hosted $hostedHelper */
        $hostedHelper = Mage::helper('autoship/hosted');

        $hostedHelper->onCustomerAddressSaveCommitAfter($address);
    }

    public function onCustomerAddressDeleteCommitAfter(Varien_Event_Observer $observer)
    {
        SubscribePro_Autoship::log('SubscribePro_Autoship_Model_Observer::onCustomerAddressDeleteCommitAfter', Zend_Log::INFO);

        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled') != '1') {
            return;
        }

        /** @var Mage_Customer_Model_Address $address */
        $address = $observer->getData('customer_address');

        /** @var SubscribePro_Autoship_Helper_Hosted $hostedHelper */
        $hostedHelper = Mage::helper('autoship/hosted');

        $hostedHelper->onCustomerAddressDeleteCommitAfter($address);
    }

    /**
     * Handle checkout_cart_add_product_complete event
     *
     * @param Varien_Event_Observer $observer
     *
     * Might be better to observe a different event, like checkout_cart_product_add_after, but these events all happen before quote is saved
     * public function onCheckoutCartProductAddAfter(Mage_Sales_Model_Quote_Item $quoteItem, Mage_Catalog_Model_Product $product)
     */
    public function onCheckoutCartAddProductComplete(Varien_Event_Observer $observer)
    {
        Mage::log(__DIR__. " ".__LINE__, null, 'sp-checkout.log', true);
        SubscribePro_Autoship::log('SubscribePro_Autoship_Model_Observer::onCheckoutCartAddProductComplete', Zend_Log::INFO);

        // Get store for config checks
        $store = Mage::getSingleton('checkout/cart')->getQuote()->getStore();

        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled', $store) != '1') {
            return;
        }

        // Get data from $observer
        $product = $observer->getData('product');

        // Get product type
        $productType = $product->getTypeId();

        // Call helper to handle this event
        /** @var SubscribePro_Autoship_Helper_Quote $quoteHelper */
        $quoteHelper = Mage::helper('autoship/quote');
        // Check product type
        if ($productType == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
            $quoteHelper->onCheckoutCartAddGroupedProductComplete($product);
        }
        //else if ($productType == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
        //else if ($productType == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
        //else if ($productType == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
        else {
            $quoteHelper->onCheckoutCartAddProductComplete($product);
        }

    }

    public function onCheckoutCartUpdateItemsAfter(Varien_Event_Observer $observer)
    {
        SubscribePro_Autoship::log('SubscribePro_Autoship_Model_Observer::onCheckoutCartUpdateItemsAfter', Zend_Log::INFO);

        // Get data from $observer
        /** @var Mage_Checkout_Model_Cart $cart */
        $cart = $observer->getData('cart');
        /** @var array $data */
        $data = $observer->getData('info');

        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled', $cart->getQuote()->getStore()) != '1') {
            return;
        }

        // Call helper to handle this event
        /** @var SubscribePro_Autoship_Helper_Quote $quoteHelper */
        $quoteHelper = Mage::helper('autoship/quote');
        $quoteHelper->onCheckoutCartUpdateItemsAfter($cart, $data);

    }

    public function onSalesConvertQuoteItemToOrderItem(Varien_Event_Observer $observer)
    {
        SubscribePro_Autoship::log('SubscribePro_Autoship_Model_Observer::onSalesConvertQuoteItemToOrderItem', Zend_Log::INFO);

        // Get data from $observer
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        $quoteItem = $observer->getData('item');
        /** @var Mage_Sales_Model_Order_Item $orderItem */
        $orderItem = $observer->getData('order_item');

        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled', $quoteItem->getStore()) != '1') {
            return;
        }

        // Call helper to handle this event
        /** @var SubscribePro_Autoship_Helper_Quote $quoteHelper */
        $quoteHelper = Mage::helper('autoship/quote');
        $quoteHelper->onSalesConvertQuoteItemToOrderItem($quoteItem, $orderItem);
    }

    public function onSalesConvertOrderToQuote(Varien_Event_Observer $observer)
    {
        SubscribePro_Autoship::log('SubscribePro_Autoship_Model_Observer::onSalesConvertOrderToQuote', Zend_Log::INFO);

        // Get data from $observer
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getData('quote');
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getData('order');

        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled', $quote->getStore()) != '1') {
            return;
        }

        // Call helper to handle this event
        /** @var SubscribePro_Autoship_Helper_Quote $quoteHelper */
        $quoteHelper = Mage::helper('autoship/quote');
        $quoteHelper->onSalesConvertOrderToQuote($order, $quote);
    }

    public function onCheckoutSubmitAllAfter(Varien_Event_Observer $observer)
    {
        SubscribePro_Autoship::log('SubscribePro_Autoship_Model_Observer::onCheckoutSubmitAllAfter', Zend_Log::INFO);

        // Get data from $observer
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getData('quote');
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getData('order');

        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled', $quote->getStore()) != '1') {
            return;
        }

        try {
            // Call helper to handle this event
            /** @var SubscribePro_Autoship_Helper_Quote $quoteHelper */
            $quoteHelper = Mage::helper('autoship/quote');
            $quoteHelper->onCheckoutSubmitAllAfter($quote, $order);
        }
        catch (\Exception $e) {
            SubscribePro_Autoship::log('Failed to create subscription(s)!', Zend_Log::ERR);
            SubscribePro_Autoship::log('Error message: ' . $e->getMessage(), Zend_Log::ERR);
        }
    }

    public function onCheckoutOnepageControllerSuccessAction(Varien_Event_Observer $observer)
    {
        SubscribePro_Autoship::log('SubscribePro_Autoship_Model_Observer::onCheckoutOnepageControllerSuccessAction', Zend_Log::INFO);

        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled') != '1') {
            return;
        }

        try {
            // Get data from $observer
            /** @var Mage_Sales_Model_Quote $quote */
            $orderIds = $observer->getData('order_ids');

            // Inject create subscription ids into block
            /** @var Mage_Core_Model_Layout $coreLayout */
            $coreLayout = Mage::getSingleton('core/layout');
            /** @var Mage_Core_Block_Template $blockCheckoutSuccessSubscriptions */
            $blockCheckoutSuccessSubscriptions = $coreLayout->getBlock('checkout.success.subscriptions');
            $blockCheckoutSuccessSubscriptions->setData(
                'created_subscription_ids',
                Mage::getSingleton('checkout/session')->getData('created_subscription_ids'));
            $blockCheckoutSuccessSubscriptions->setData(
                'failed_subscription_count',
                Mage::getSingleton('checkout/session')->getData('failed_subscription_count'));
            // Clear data from checkout session
            Mage::getSingleton('checkout/session')->setData('created_subscription_ids', null);
            Mage::getSingleton('checkout/session')->setData('failed_subscription_count', 0);
        }
        catch (\Exception $e) {
            SubscribePro_Autoship::log('Failed to display subscription created message on one-page checkout success page!', Zend_Log::ERR);
            SubscribePro_Autoship::log('Error message: ' . $e->getMessage(), Zend_Log::ERR);
        }
    }

    /**
     * @deprecated
     * @param Varien_Event_Observer $observer
     */
    public function onSalesQuoteAddressDiscountItem(Varien_Event_Observer $observer)
    {
        SubscribePro_Autoship::log('SubscribePro_Autoship_Model_Observer::onSalesQuoteAddressDiscountItem', Zend_Log::INFO);
    }

    /**
     * Check is allowed guest checkout if quote contains subscription products
     *
     * @param Varien_Event_Observer $observer
     * @return SubscribePro_Autoship_Model_Observer
     */
    public function isAllowedGuestCheckout(Varien_Event_Observer $observer)
    {
        SubscribePro_Autoship::log('SubscribePro_Autoship_Model_Observer::isAllowedGuestCheckout', Zend_Log::INFO);

        Mage::log(__DIR__. " ".__LINE__, null, 'sp-checkout.log', true);
        // Get data from $observer
        /** @var Mage_Sales_Model_Quote $quote */
        $quote  = $observer->getData('event')->getQuote();
        /** @var Varien_Object $result */
        $result = $observer->getData('event')->getResult();

        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled', $quote->getStore()) != '1') {
            Mage::log(__DIR__. " ".__LINE__, null, 'sp-checkout.log', true);
            return $this;
        }

        // Get quote helper
        /** @var SubscribePro_Autoship_Helper_Quote $quoteHelper */
        $quoteHelper = Mage::helper('autoship/quote');
        // Check if quote has any subscriptions in it
        Mage::log(__DIR__. " ".__LINE__, null, 'sp-checkout.log', true);
        if($quoteHelper->hasProductsToCreateNewSubscription($quote)) {
            Mage::log(__DIR__. " ".__LINE__, null, 'sp-checkout.log', true);
            // Quote has subscriptions, disable guest checkout
            $result->setData('is_allowed', false);
        }

        return $this;
    }

    /**
     * When a customer is saved, check if they exist on the platform. If so, update the platform with their data
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function onCustomerSave(Varien_Event_Observer $observer)
    {
        SubscribePro_Autoship::log('SubscribePro_Autoship_Model_Observer::onCustomerSave', Zend_Log::INFO);

        /** @var Mage_Customer_Model_Customer $customer */
        $customer = $observer->getData('customer');

        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled', $customer->getStore()) != '1') {
            return $this;
        }

        // Make sure the customer isn't brand new and actually has an original email
        if (!$customer->isObjectNew()
            && $customer->getOrigData('email')
            && $customer->dataHasChangedFor('email')) {

            /** @var SubscribePro_Autoship_Helper_Platform_Customer $platformCustomerHelper */
            $platformCustomerHelper = Mage::helper('autoship/platform_customer');

            /** @var SubscribePro_Autoship_Helper_Api $apiHelper */
            $apiHelper = Mage::helper('autoship/api');

            // Update config store to match store customer is associated to?
            $apiHelper->setConfigStore($customer->getStore());

            // Update SP platform customer, don't save Magento customer, since customer will be saved after this observer returns
            $result = $platformCustomerHelper->updatePlatformCustomerIfExists($customer, false);
            if ($result) {
                SubscribePro_Autoship::log('Customer with email: ' . $customer->getData('email') . ' was changed, updated Subscribe Pro', Zend_Log::INFO);
            } else {
                SubscribePro_Autoship::log('Customer with email: ' . $customer->getData('email') . ' was changed, but does not exist on Subscribe Pro platform', Zend_Log::INFO);
            }
        }
        return $this;
    }

    /**
     * When re-ordering via admin, ensure we don't copy another subscription's id over
     *
     * @see Mage_Adminhtml_Model_Sales_Order_Create::initFromOrderItem
     * @param Varien_Event_Observer $observer
     */
    public function onSalesConvertOrderItemToQuoteItem(Varien_Event_Observer $observer)
    {
        SubscribePro_Autoship::log('SubscribePro_Autoship_Model_Observer::onSalesConvertOrderItemToQuoteItem', Zend_Log::INFO);

        /** @var SubscribePro_Autoship_Helper_Quote $quoteHelper */
        $quoteHelper = Mage::helper('autoship/quote');

        /** @var Mage_Sales_Model_Quote_Item $item */
        $item = $observer->getData('quote_item');

        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled', $item->getQuote()->getStore()) != '1') {
            return;
        }

        // For bundled product, only 1 item will be passed to the observer
        // This item *might* be the bundled product or it might not be
        // If it's not, go ahead and grab the bundled product from the parent
        // Seems to be a bug in magento, @see Mage_Adminhtml_Model_Sales_Order_Create::initFromOrderItem
        if ($item->getParentItem() && $item->getParentItem()->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $item = $item->getParentItem();
        }
        /** @var SubscribePro_Autoship_Helper_Product $productHelper */
        $productHelper = Mage::helper('autoship/product');

        $product = $item->getProduct();

        // Check if product is enabled, otherwise don't bother updating the custom options
        if (!$productHelper->isAvailableForSubscription($product, $item->getQuote()->getStore())) {
            return;
        }

        // Remove the subscription, so re-ordering doesn't result in 2 subscription ids
        $item->removeOption('additional_options');
        $item->setData('subscription_id', null);
        $item->save();

        // Set basic config
        $config = array(
            'qty' => $item->getQty()
        );

        // Different logic for grouped
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
            $quoteHelper->onCheckoutCartAddGroupedProductComplete($product, $config);
        } else {
            $quoteHelper->onCheckoutCartAddProductComplete($product, $config);
        }
    }

}
