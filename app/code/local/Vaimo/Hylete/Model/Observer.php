<?php

/**
 * Copyright (c) 2009-2015 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_Hylete
 * @file        Observer.php
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Vitali Rassolov <vitali.rassolov@vaimo.com>
 */
class Vaimo_Hylete_Model_Observer
{
    private $optionsArray = array();

    private $alreadyChanged = false;

    public function salesQuoteConfigGetProductAttributes(Varien_Event_Observer $observer)
    {
        /** @var Varien_Object $attributesTransfer */
        $attributesTransfer = $observer->getEvent()->getAttributes();

        $result = array('color' => true, 'size' => true, 'checkout_disclaimer' => true);

        $attributesTransfer->addData($result);

        return $this;
    }

    public function salesOrderSendNewOrderEmailAfterSend(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        if ($order = $observer->getEvent()->getOrder()) {
            $order->setEmailSent(true);
            $order->getResource()->saveAttribute($order, 'email_sent');
        }

        return $this;
    }

    /**
     * Assign customer to group after registration.
     * Used with custom registration pages.
     * See HYL-93.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function customerRegisterSuccess(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();

        /** @var Mage_Customer_AccountController $controller */
        $controller = $event->getAccountController();

        if ($controller) {
            if ($customerGroupId = $controller->getRequest()->getParam('customer_group_id')) {
                /** @var Mage_Customer_Model_Customer $customer */
                $customer = $event->getCustomer();
                $customer->setGroupId($customerGroupId)
                    ->save();
            }
        }

        return $this;
    }

    /**
     * Prevent website restriction processing if logged in as admin, required for vaimo/cms to work properly
     * with resellers and marketing sites.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function websiterestrictionFrontend(Varien_Event_Observer $observer)
    {
        $adminSession = Mage::getSingleton('admin/session');

        if ($adminSession->isLoggedIn()) {
            $result = $observer->getEvent()->getResult();
            $result->setShouldProceed(false);
        }

        return $this;
    }

    /**
     * Add color column in cross-sell,up-sell,related-products.
     * Used in product grids in admin.
     * See HYL-153.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function appendColorColumn(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();

        if (!isset($block)) {
            return $this;
        }

        if ($block->getType() == 'adminhtml/catalog_product_edit_tab_related'
                || $block->getType() == 'adminhtml/catalog_product_edit_tab_upsell'
                || $block->getType() == 'adminhtml/catalog_product_edit_tab_crosssell'
                || $block->getType() == 'adminhtml/catalog_product_edit_tab_super_config_grid'
                || $block->getType() == 'adminhtml/catalog_category_tab_product') {
            $block->addColumnAfter('color',
                    array('header' => 'Color',
                            'type' => 'options',
                            'index' => 'color',
                            'options' => $this->getProductAttributeOptions('color'))
                    , 'sku');
        }
    }

    /**
     * Add attribute to product collecttion.
     * Used in product grids in admin.
     * See HYL-153.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return
     */
    public function beforeCollectionLoad(Varien_Event_Observer $observer)
    {
        $collection = $observer->getCollection();
        if (!isset($collection)) {
            return;
        }

        /**
         * Mage_Catalog_Model_Resource_Product_Collection
         */
        if ($collection instanceof Mage_Catalog_Model_Resource_Product_Collection) {
            /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
            $collection->addAttributeToSelect('color');
            $collection->joinAttribute('color', 'catalog_product/color', 'entity_id', null, 'left');
        }
    }

    /**
     * Return attribute options array
     *
     * @param string $attributeName
     *
     * @return array $optionsArray
     */
    public function getProductAttributeOptions($attributeName)
    {
        if (empty($this->optionsArray)) {
            $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeName);
            $allOptions_array = $attribute->getSource()->getAllOptions(true, true);
            foreach ($allOptions_array as $instance) {
                if ($instance['value'] != "") {
                    $this->optionsArray[$instance['value']] = $instance['label'];
                }
            }
        }

        return $this->optionsArray;
    }

    /**
     * Add custom messages on apply coupon .
     * See HYL-159.
     *
     * @param
     *
     * @return
     */
    public function customCouponMessages()
    {
        if (!$this->alreadyChanged) {
            $request = Mage::app()->getRequest();
            $actionName = $request->getActionName();
            $isRemove = $request->getParam('remove');
            $session = Mage::getSingleton('checkout/session');
            if ($actionName == 'couponPost') {
                $this->alreadyChanged = true;
                $isCustomerLoggedIn = Mage::getSingleton("customer/session")->isLoggedIn();
                $messages = Mage::getSingleton('checkout/session')->getMessages();
                $message = $messages->getLastAddedMessage();
                $message->setIdentifier('defaultMessage');
                $messages->deleteMessageByIdentifier('defaultMessage');
                if (!$isCustomerLoggedIn) {
                    if ($message->getType() == 'error') {
                        $message->setCode('You must be logged in to activate promo codes.');
                    }
                } else {
                    if ($message->getType() == 'error') {
                        $message->setCode('Invalid code: Your promo code may have expired. Make sure that all product in your cart is valid with your promotion. Only one promo code can be used at a time.');
                    } elseif ($message->getType() == 'success') {
                        if ($isRemove != "1") {
                            $message->setCode('Success! Please note: Promotional value will not be applied to gift cards, clearance locker, featured product, NPGL, or charity items present in your cart, unless otherwise specified.');
                        }
                    }
                }

                $session->addMessage($message);
            }
        }
    }

    /**
     * event: icommerce_addtocartajax_before
     * see HYL-383
     *
     * @param Varien_Event_Observer $observer
     *
     * @throws Exception
     */
    public function validateAddToCartAjaxPost(Varien_Event_Observer $observer)
    {
        /** @var array $params */
        $params = $observer->getParams();
        try {
            $this->_validateCartAddFields($params);
        } catch (Mage_Core_Exception $ex) {
            Mage::logException($ex);

            $exceptionMessage = Mage::helper('checkout')->__('Cannot add the item to shopping cart.');

            throw new Exception($exceptionMessage);
        }
    }

    /**
     * event: controller_action_predispatch_checkout_cart_add
     * see HYL-383
     *
     * @param Varien_Event_Observer $observer
     */
    public function validateAddToCartPost(Varien_Event_Observer $observer)
    {
        /** @var Mage_Checkout_CartController $controller */
        $controller = $observer->getControllerAction();
        try {
            $this->_validateCartAddFields($controller->getRequest()->getParams());
        } catch (Exception $ex) {
            $controller->setFlag('', $controller::FLAG_NO_DISPATCH, true);

            //$thrownMessage = $ex->getMessage();
            Mage::logException($ex);

            $exceptionMessage = $controller->__('Cannot add the item to shopping cart.');
            if ($this->_getCheckoutSession()->getUseNotice(true)) {
                $this->_getCheckoutSession()->addNotice(Mage::helper('core')->escapeHtml($exceptionMessage));
            } else {
                $messages = array_unique(explode("\n", $exceptionMessage));
                foreach ($messages as $message) {
                    $this->_getCheckoutSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

            $url = $this->_getCheckoutSession()->getRedirectUrl(true);
            if ($url) {
                $controller->getResponse()->setRedirect($url);
            } else {
                $controller->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        }
    }

    /**
     * @param array $requestParams
     *
     * @throws Mage_Core_Exception
     */
    protected function _validateCartAddFields($requestParams)
    {
        $qty = isset($requestParams['qty']) ? $requestParams['qty'] : null;
        $related = isset($requestParams['related_product']) ? $requestParams['related_product'] : null;
        $superAttributes = isset($requestParams['super_attribute']) ? $requestParams['super_attribute'] : null;

        if (!empty($qty)) {
            $filter = new Zend_Filter_LocalizedToNormalized(
                array('locale' => Mage::app()->getLocale()->getLocaleCode())
            );
            try {
                $qty = $filter->filter($qty);
            } catch (Zend_Locale_Exception $ex) {
                // silent catch
            }
            // check qty if decimal or int
            if (!is_numeric($qty)) {
                $this->_throwCartException(sprintf('"qty" not numeric (%s)', $qty));
            }
        }

        if (!empty($related)) {
            $relatedIds = explode(',', $related);
            // check if they are numeric
            foreach ($relatedIds as $relatedId) {
                if (!is_numeric($relatedId)) {
                    $this->_throwCartException(sprintf('"related_product" not numeric (%s)', $related));
                }
            }
        }
        if (!empty($superAttributes)) {
            foreach ($superAttributes as $attributeId => $optionId) {
                if (!is_numeric($attributeId) || !is_numeric($optionId)) {
                    $this->_throwCartException(sprintf('"super_attribute" not numeric (%s => %s)', $attributeId, $optionId));
                }
            }
        }
    }

    /**
     * @param $message
     *
     * @throws Mage_Core_Exception
     */
    protected function _throwCartException($message)
    {
        throw new Mage_Core_Exception($message);
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }
}
