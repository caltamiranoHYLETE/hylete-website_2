<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Icommerce_QuickCheckout_QuickCheckoutController extends Mage_Checkout_Controller_Action
{
    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
                ->setHeader('HTTP/1.1', '403 Session Expired')
                ->setHeader('Login-Required', 'true')
                ->sendResponse();
        return $this;
    }

    protected function _expireAjax()
    {
        $onepage = Mage::getSingleton('checkout/type_onepage');

        if (!$onepage->getQuote()->hasItems()
            || ($onepage->getQuote()->getHasError() && !Mage::getStoreConfig('quickcheckout/settings/not_redirect_onepage_to_cart_on_error'))
            || $onepage->getQuote()->getIsMultiShipping()) {
            $this->_ajaxRedirectResponse();
            exit;
        }
        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))) {
            $this->_ajaxRedirectResponse();
            exit;
        }
        Mage::getSingleton('core/translate_inline')->setIsAjaxRequest(true);
    }

    public function getShippingMethodsHtmlAction()
    {
        $this->_expireAjax();

        if ($this->getRequest()->isPost()) {

            $customerShippingAddressId = $this->getRequest()->getPost('shipping_address_id', false);
            $customerBillingAddressId = $this->getRequest()->getPost('billing_address_id', false);

            if(!empty($customerShippingAddressId)){
                $data = Mage::getModel('customer/address')->load($customerShippingAddressId);
            }
            else if(!empty($customerBillingAddressId)){
                $data = Mage::getModel('customer/address')->load($customerBillingAddressId);
            }
            else {
                $data = $this->getRequest()->getPost('billing', array());
                if(is_array($data) && (count($data) < 1) ){
                    $data = $this->getRequest()->getPost('shipping', array());
                }
            }

            $result = Mage::getSingleton('checkout/type_onepage')->saveCheckoutData($data);

            if (!isset($result['error'])) {
                $result['update_section'] = array(
                    'name' => 'shipping-method',
                    'html' => $this->_getShippingMethodsHtml()
                );
            }

            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    protected function _getShippingMethodsHtml()
    {
        // If we do not have InvoiceCost or Klarna installed we do not get any shipping methods when
        // arriving tocheckout (why?). Quick fix for now, should not affect performance too much.
        $onepage = Mage::getSingleton('checkout/type_onepage');
        $onepage->getQuote()->getShippingAddress()->setCollectShippingRates(true);

        // Call collectTotals so that the correct shipping methods are selected based on package_qty.
        $onepage->getQuote()->collectTotals();

        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_shippingmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    public function getPaymentMethodsHtmlAction()
    {
        $this->_expireAjax();

        if ($this->getRequest()->isPost()) {

            $customerBillingAddressId = $this->getRequest()->getPost('billing_address_id', false);

            if(!empty($customerBillingAddressId)){
                $data = Mage::getModel('customer/address')->load($customerBillingAddressId);
            }
            else {
                $data = $this->getRequest()->getPost('billing', array());
            }

            $result = Mage::getSingleton('checkout/type_onepage')->saveCheckoutData($data, false);

            if (!isset($result['error'])) {
                $result['update_section'] = array(
                    'name' => 'payment-method',
                    'html' => $this->_getPaymentMethodsHtml()
                );
            }

            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    protected function _getPaymentMethodsHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_paymentmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();

        return $output;
    }
}
