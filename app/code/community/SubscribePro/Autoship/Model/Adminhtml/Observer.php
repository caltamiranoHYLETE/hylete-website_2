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
 * Observer class to handle all event observers for subscriptions module Adminhtml area
 */
class SubscribePro_Autoship_Model_Adminhtml_Observer
{

    public function adminhtmlWidgetContainerHtmlBefore($event)
    {
        /** @var SubscribePro_Autoship_Helper_Payment $paymentHelper */
        $paymentHelper = Mage::helper('payment');

        $block = $event->getBlock();

        // Add Reauthorize button to View Order
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {
            // Cur order
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::registry('current_order');
            // Check for subscribe pro vault pay method
            if($paymentHelper->isSubscribeProCreditCardMethod($order->getPayment()->getMethod())) {
                // Only show for authorize only orders
                /** @var SubscribePro_Autoship_Model_Payment_Method_Cc $methodInstance */
                $methodInstance = $order->getPayment()->getMethodInstance();
                if ($methodInstance->canReauthorize($order->getPayment())) {
                    // Only show when the order used a
                    $message = Mage::helper('autoship')->__('Are you sure you want to create a new authorization for this order?');
                    $block->addButton('reauthorize_order_payment', array(
                        'label' => Mage::helper('autoship')->__('Reauthorize'),
                        'onclick' => "confirmSetLocation('{$message}', '{$block->getUrl('adminhtml/sporderpayment/reauthorize')}')",
                        'class' => 'go'
                    ));
                }
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function onAdminhtmlSalesOrderCreateProcessData(Varien_Event_Observer $observer)
    {
        SubscribePro_Autoship::log('SubscribePro_Autoship_Model_Observer::onAdminhtmlSalesOrderCreateProcessData', Zend_Log::INFO);

        /** @var SubscribePro_Autoship_Helper_Quote $quoteHelper */
        $quoteHelper = Mage::helper('autoship/quote');

        /** @var Mage_Adminhtml_Model_Sales_Order_Create $orderCreateModel */
        $orderCreateModel = $observer->getEvent()->getOrderCreateModel();

        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled', $orderCreateModel->getQuote()->getStore()) != '1') {
            return $this;
        }

        // If request has 'item' present, we have updated, added, or removed an item
        if (Mage::app()->getRequest()->has('item')) {

            // Update all quote item
            $quoteHelper->updateQuoteItems($orderCreateModel->getQuote(), Mage::app()->getRequest()->getPost('item'));

            $action = Mage::app()->getRequest()->getActionName();

            if (!Mage::app()->getRequest()->getPost('update_items') && !($action == 'save')) {

                // Process all newly added items, so we can set their defaults
                foreach (Mage::app()->getRequest()->getPost('item') as $productId => $config) {
                    //Validation on the product id has already been done at this point
                    $config['qty'] = isset($config['qty']) ? (float)$config['qty'] : 1;
                    $product = Mage::getModel('catalog/product')
                        ->setStore($orderCreateModel->getQuote()->getStore())
                        ->setStoreId($orderCreateModel->getQuote()->getStore()->getId())
                        ->load($productId);

                    $cartProduct = $product;

                    if ($cartProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                        //In order to ensure the custom options match from product<-->quote item, we run it through prepareForCartAdvanced
                        $cartCandidates = $product->getTypeInstance(true)
                            ->prepareForCartAdvanced(new Varien_Object($config), $product, null);

                        if (sizeof($cartCandidates) < 1) {
                            Mage::throwException("Unable to add bundle product with sku: " . $product->getSku() . ' to cart');
                        }

                        /** @var Mage_Catalog_Model_Product $candidate */
                        foreach($cartCandidates as $candidate) {
                            //Ensure we get the bundled product
                            if ($candidate->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                                $cartProduct = $candidate;
                                break;
                            }
                        }
                    }

                    // Different logic for grouped
                    if ($cartProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
                        $quoteHelper->onCheckoutCartAddGroupedProductComplete($cartProduct, $config);
                    } else {
                        $quoteHelper->onCheckoutCartAddProductComplete($cartProduct, $config);
                    }
                }
            }

            // Recollect
            $orderCreateModel->recollectCart();

        }

        return $this;
    }

}
