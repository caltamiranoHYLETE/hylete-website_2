<?php
/**
 * Copyright (c) 2009-2011 Icommerce Nordic AB
 *
 * Icommerce reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Icommerce, except as provided by licence. A licence
 * under Icommerce's rights in the Program may be available directly from
 * Icommerce.
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
 * @category    Icommerce
 * @package     Icommerce_
 * @copyright   Copyright (c) 2009-2011 Icommerce Nordic AB
 */
require_once "Mage/Checkout/controllers/OnepageController.php";

class Icommerce_QuickCheckout_OnepageController extends Mage_Checkout_OnepageController
{
    protected function _expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || ($this->getOnepage()->getQuote()->getHasError() && !Mage::getStoreConfig('quickcheckout/settings/not_redirect_onepage_to_cart_on_error'))
            || $this->getOnepage()->getQuote()->getIsMultiShipping()) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))) {
            $this->_ajaxRedirectResponse();
            return true;
        }

        return false;
    }

    public function indexAction()
    {
        // By standard, country ID gets set on quote in first checkout steps. This will be later used to assess, which
        // payment methods are allowed for that country. If we load all the information on the same page, the default
        // country will never be assigned before it's too late (payment methods are loaded at the same time) - resulting
        // in default payment selection to be wrong.
        $quote = $this->getOnepage()->getQuote();
        $countryId = $quote->getBillingAddress()->getCountryId();
        if (is_null($countryId)) {
            $countryId = Mage::getStoreConfig('general/country/default');
            $quote->getBillingAddress()->setCountryId($countryId);
        }
        
        if(Mage::getStoreConfig('quickcheckout/settings/redirect_not_logged_in_customer_to_registration',
            Mage::app()->getStore()->getId()) && !Mage::getSingleton('customer/session')->isLoggedIn()) {

            Mage::getSingleton('customer/session')->addNotice($this->__(
                'You have to be registered and logged in to be able to checkout.'
            ));

            $registerUrl = 'customer/account/create';

            Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('checkout/onepage'));

            $this->_redirectUrl(Mage::getUrl($registerUrl));
            return;
        }

        if(Mage::getStoreConfig('quickcheckout/settings/redirect_not_logged_in_customer_to_login',
            Mage::app()->getStore()->getId()) && !Mage::getSingleton('customer/session')->isLoggedIn()) {

            Mage::getSingleton('customer/session')->addNotice($this->__(
                'You have to be registered and logged in to be able to checkout.'
            ));

            $loginUrl = 'customer/account/login';

            Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('checkout/onepage'));

            $this->_redirectUrl(Mage::getUrl($loginUrl));
            return;
        }

        if(!Mage::getStoreConfig('quickcheckout/settings/not_redirect_onepage_to_cart_on_error',
            Mage::app()->getStore()->getId())) {
            parent::indexAction();
            return;
        }

        // This is needed for the "skip cart page and forward automatically to checkout" functionality in
        // Icommerce_QuickCheckoutCart
        Mage::getSingleton('checkout/session')->setVaimoQuickCheckoutFromOnepage(true);

        if (!Mage::helper('checkout')->canOnepageCheckout()) {
            Mage::getSingleton('checkout/session')->addError($this->__('The onepage checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getOnepage()->getQuote();
        if ($quote->getHasError()) {
            // Do not redirect to the cart
        }
        if (!$quote->hasItems()) {
            if (Mage::getStoreConfig('quickcheckout/settings/redirect_checkout_to_cart_when_cart_is_empty',Mage::app()->getStore()->getId())) {
                $this->_redirect('checkout/cart');
                return;
            }
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            Mage::getSingleton('checkout/session')->addError($error);

            // Do not redirect to the cart
            //$this->_redirect('checkout/cart');
            //return;
        }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure'=>true)));
        $this->getOnepage()->initCheckout();
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Checkout'));
        $this->renderLayout();
        Mage::getSingleton('checkout/session')->setVaimoQuickCheckoutFromOnepage(false);
    }

    public function saveOrderAction()
    {
        if (!Mage::getStoreConfig('quickcheckoutcart/settings/redirect_cart_to_onepage', Mage::app()->getStore()->getId())) {
            parent::saveOrderAction();
            return;
        }

        if ($this->_expireAjax()) {
            return;
        }

        $result = array();
        $quote = $this->getOnepage()->getQuote();

        if (!$quote->validateMinimumAmount()) {
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = Mage::getStoreConfig('sales/minimum_order/error_message');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        }

        parent::saveOrderAction();
    }
}

