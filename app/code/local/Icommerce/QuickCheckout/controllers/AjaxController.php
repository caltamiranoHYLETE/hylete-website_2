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
 * @package     Icommerce_QuickCheckout
 * @copyright   Copyright (c) 2009-2011 Icommerce Nordic AB
 * @author      Wilko Nienhaus
 */

class Icommerce_QuickCheckout_AjaxController extends Mage_Core_Controller_Front_Action
{
    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    /**
     * Add a Discount code to the shopping basket AJAX style (return html of updated totals, discount code block)
     *
     * @return Icommerce_QuickCheckout_AjaxController
     * @see Mage_Checkout_CartController::couponPostAction()
     */
    public function addCouponAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if ($this->_getCart()->getQuote()->getItemsCount()) {

            $couponCode = (string) $this->getRequest()->getParam('coupon_code');
            if ($this->getRequest()->getParam('remove') == 1) {
                $couponCode = '';
            }
            $oldCouponCode = $this->_getQuote()->getCouponCode();

            if (strlen($couponCode) || strlen($oldCouponCode)) {

                try {
                    $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
                    $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                            ->collectTotals()
                            ->save();

                    if ($couponCode) {
                        if ($couponCode == $this->_getQuote()->getCouponCode()) {
                            $success[] = $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode));
                        }
                        else {
                            $error[] = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
                        }
                    } else {
                        $success[] = $this->__('Coupon code was canceled.');
                    }

                }
                catch (Mage_Core_Exception $e) {
                    $error[] = $e->getMessage();
                }
                catch (Exception $e) {
                    $error[] = $this->__('Cannot apply the coupon code.');
                }
            }
        }

        // build response
        $result = array();
        
        if (isset($success)) {
            $this->loadLayout('checkout_onepage_index');
            $result['update_section'] = array(
                'name' => 'coupon',
                'html' => $this->getLayout()->getBlock('checkout.cart.coupon')->toHtml(),
            );
            $result['success'] = $success;
        }
        if (isset($error)) {
            $result['error'] = $error;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

        return $this;
    }

    /**
     * Add a a Gift card to the shopping basket AJAX style (return html of updated totals, gift card block)
     *
     * @return Icommerce_QuickCheckout_AjaxController
     * @see Enterprise_GiftCardAccount_CartController::addAction()
     */
    public function addGiftCardAction()
    {
        //param: giftcard_code
        $data = $this->getRequest()->getPost();
        if (isset($data['giftcard_code'])) {
            $code = $data['giftcard_code'];
            try {
                Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                        ->loadByCode($code)
                        ->addToCart();
                $success[] = $this->__('Gift Card "%s" was added.', Mage::helper('core')->htmlEscape($code));
            } catch (Mage_Core_Exception $e) {
                Mage::dispatchEvent('enterprise_giftcardaccount_add', array('status' => 'fail', 'code' => $code));
                $error[] = $e->getMessage();
            } catch (Exception $e) {
                $error[] = $this->__('Cannot apply gift card.');
                $error[] = $e->getMessage();
            }
        }

        // build response
        $result = array();

        if (isset($success)) {
            $this->loadLayout('checkout_onepage_index');
            $result['update_section'] = array(
                'name' => 'giftcards',
                'html' => $this->getLayout()->getBlock('checkout.cart.giftcardaccount')->toHtml(),
            );
            $result['success'] = $success;
        }
        if (isset($error)) {
            $result['error'] = $error;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

        return $this;
    }

    public function removeGiftCardAction()
    {
        if ($code = $this->getRequest()->getParam('code')) {
            try {
                Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                        ->loadByCode($code)
                        ->removeFromCart();
                $success[] = $this->__('Gift Card "%s" was removed.', Mage::helper('core')->htmlEscape($code));
            } catch (Mage_Core_Exception $e) {
                $error[] = $e->getMessage();
            } catch (Exception $e) {
                $error[] = $this->__('Cannot remove gift card.');
                $error[] = $e->getMessage();
            }
        }

        // build response
        $result = array();

        if (isset($success)) {
            $this->loadLayout('checkout_onepage_index');
            $result['update_section'] = array(
                'name' => 'giftcards',
                'html' => $this->getLayout()->getBlock('checkout.cart.giftcardaccount')->toHtml(),
            );
            $result['success'] = $success;
        }
        if (isset($error)) {
            $result['error'] = $error;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

        return $this;
   }
   
   public function checkGiftCardStatusAction()
   {
       $result = array();

       /* @var $card Enterprise_GiftCardAccount_Model_Giftcardaccount */
       $card = Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
           ->loadByCode($this->getRequest()->getParam('giftcard_code', ''));
       Mage::register('current_giftcardaccount', $card);
       try {
           $card->isValid(true, true, true, false);
       }
       catch (Mage_Core_Exception $e) {
           $card->unsetData();
           $result['error'] = $e->getMessage();
       }

       $this->loadLayout('enterprise_giftcardaccount_cart_check');

       $result['update_section'] = array(
            'name' => 'giftcard_balance_lookup',
            'html' => $this->getLayout()->getBlock('check.result')->toHtml(),
        );

       $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

       return $this;
   }

    /**
     * To allow remote logging for possible error messages in client-side checkout. All alerts will be posted to server
     * side and saved in specified log file
     *
     * @return Icommerce_QuickCheckout_AjaxController
     */
    public function logAction()
    {
        $data = $this->getRequest()->getPost();

        if (isset($data['msg'])) {
            Icommerce_Default::logAppend($data['msg'], 'var/quickcheckout_ajax.log');
        }

        return $this;
    }
}