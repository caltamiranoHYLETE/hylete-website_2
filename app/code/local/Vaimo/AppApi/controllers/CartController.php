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
 * @package     Vaimo_AppApi
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Kjell Holmqvist
 */

class Vaimo_AppApi_CartController extends Vaimo_AppApi_Controller_Action
{
    /**
     * checkoutAction does basic authentication with post form fields, if required
     *
     * completeAction does not require any authentication at all
     *
     * @return Mage_Core_Controller_Front_Action
     */
    public function preDispatch()
    {
        if ($this->_request->getActionName()=='complete') {
            $this->_skipAuthentication();
        } else {
            if (!$this->_request->getServer('PHP_AUTH_USER')) {
                $username = $this->_request->getParam('username', null);
                $password = $this->_request->getParam('password', null);

                $_SERVER['PHP_AUTH_USER'] = $username;
                $_SERVER['PHP_AUTH_PW'] = $password;
            }
        }

        $result = parent::preDispatch();

        return $result;
    }

    /**
     * Retrive a list of stores in the database
     *
     * Expects website (optional), store (optional), base 64 encoded product/qty list, redirectUrl (optional)
     *
     * @return void
     */
    public function checkoutAction()
    {
        $errorStr = NULL;

        if ($this->_isRequestPost($result)) {

            $productsBase64 = $this->_request->getParam('products', null);
            $websiteId = $this->_request->getParam('website', 0);
            $storeId = $this->_request->getParam('store', null);
            $redirectUrl = $this->_request->getParam('redirecturl', null);

            $products = base64_decode($productsBase64);

            try {
                Mage::register('isSecureArea', true);

                $selectedStoreId = Mage::helper('appapi')->getStoreId($websiteId, $storeId);
                $prevStore = Mage::app()->getStore()->getCode();
                Mage::app()->setCurrentStore(Mage::app()->getStore($selectedStoreId)->getCode());

                $apiResult = Mage::getModel('appapi/cart')->productsToCart($websiteId, $storeId, $products, $redirectUrl);

//                Mage::app()->setCurrentStore($prevStore);

                Mage::unregister('isSecureArea');

                if (is_string($apiResult)) {
                    $errorStr  = $apiResult;
                }
            }
            catch (Exception $e) {
                $errorStr  = $e->getMessage();
            }
        }

        if ($errorStr) {
            $this->_response
                ->setHttpResponseCode(400)
                ->setBody($errorStr)
                ->sendResponse();
            exit;
        } else {
            $cartUrl = Mage::getStoreConfig('appapi/appapi_settings/cart_url');
            $this->_response
                ->setRedirect(Mage::getUrl('appapi/web/browse', array('webpage' => $cartUrl)))
                ->sendResponse();
            exit;
        }
    }

    /**
     * The default
     *
     * Expects website (optional), store (optional), base 64 encoded product/qty list,
     *
     * @return void
     */
    public function completeAction()
    {
        $incrementId = Mage::helper('appapi')->escapeHtml($this->_request->getParam('increment_id', null));
        $this->_response
            ->setHttpResponseCode(200)
            ->setBody('Checkout complete, this page should never appear, it should be picked up by App (Order Number: ' . $incrementId . ')')
            ->sendResponse();
        exit;
    }

}