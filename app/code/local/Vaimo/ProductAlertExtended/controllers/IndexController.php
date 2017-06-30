<?php
/**
 * Copyright (c) 2009-2013 Vaimo AB
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
 * @package     Vaimo_ProductAlertExtended
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Giorgos Tsioutsiouliklis <giorgos@vaimo.com>
 */
class Vaimo_ProductAlertExtended_IndexController extends Mage_Core_Controller_Front_Action {
    
    public function addAction() {
        $result = array ();
        $productId = ( int ) $this->getRequest ()->getParam ( 'product_id' );
        $customerMail = $this->getRequest ()->getParam ( 'email' );
        
        if (! $productId) {
            $result ['status'] = 0;
            $result ['message'] = $this->__ ( 'Error' ) . ' : ' . $this->__ ( 'No product id specified' );
            $encodedResult = json_encode ( $result );
            $this->getResponse ()->setBody ( $encodedResult );
        }
        
        if (! $customerMail) {
            $result ['status'] = 0;
            $result ['message'] = $this->__ ( 'Error' ) . ' : ' . $this->__ ( 'No customer mail specified' );
            $encodedResult = json_encode ( $result );
            $this->getResponse ()->setBody ( $encodedResult );
        }
        
        if (! $product = Mage::getModel ( 'catalog/product' )->load ( $productId )) {
            /* @var $product Mage_Catalog_Model_Product */
            $result ['status'] = 0;
            $result ['message'] = $this->__ ( 'Error' ) . ' : ' . $this->__ ( 'Product could not be loaded' );
            $encodedResult = json_encode ( $result );
            $this->getResponse ()->setBody ( $encodedResult );
        }
        
        try {
            if (Icommerce_Default::isLoggedIn ()) {
                $model = Mage::getModel ( 'productalertextended/stock' )->setCustomerId ( Mage::getSingleton ( 'customer/session' )->getId () )->setEmail ( $customerMail )->setProductId ( $product->getId () )->setWebsiteId ( Mage::app ()->getStore ()->getWebsiteId () );
                $model->save ();
                $result ['status'] = 1;
                $result ['message'] = $this->__ ( 'Success' ) . ' : ' . $this->__ ( 'Alert has been added' );
                $encodedResult = json_encode ( $result );
                $this->getResponse ()->setBody ( $encodedResult );
            } else {
                $model = Mage::getModel ( 'productalertextended/stock' )->setEmail ( $customerMail )->setProductId ( $product->getId () )->setWebsiteId ( Mage::app ()->getStore ()->getWebsiteId () );
                $model->save ();
                $result ['status'] = 1;
                $result ['message'] = $this->__ ( 'Success' ) . ' : ' . $this->__ ( 'Alert has been added' );
                $encodedResult = json_encode ( $result );
                $this->getResponse ()->setBody ( $encodedResult );
            }
        } catch ( Exception $e ) {
            $result ['status'] = 0;
            $result ['message'] = $this->__ ( 'Error' ) . ' : ' . $e->getText ();
        }
        
        $encodedResult = json_encode ( $result );
        $this->getResponse ()->setBody ( $encodedResult );
    }
    
    public function unsubscribeAllAction()
    {
        $session = Mage::getSingleton('customer/session');
        /* @var $session Mage_Customer_Model_Session */
        $params = $this->getRequest()->getParams();
        try {
            Mage::getModel('productalertextended/stock')->deleteCustomerByMail(
            $params['customerMail'],
            Mage::app()->getStore()->getWebsiteId()
            );
            $session->addSuccess($this->__('You will no longer receive stock alerts.'));
        }
        catch (Exception $e) {
            $session->addException($e, $this->__('Unable to update the alert subscription.'));
        }
        $this->_redirect('/');
    }
    
    public function unsubscribeProductAction()
    {
        $params = $this->getRequest()->getParams();
        $productId  = (int) $params['configurableId'];
        $simpleProductId  = (int) $params['simpleId'];
        $customerMail = $params['customerMail'];
        
        if (!$productId || !$customerMail) {
            $this->_redirect('');
            return;
        }

        $session = Mage::getSingleton('catalog/session');
        /* @var $session Mage_Catalog_Model_Session */
        $product = Mage::getModel('catalog/product')->load($productId);
        /* @var $product Mage_Catalog_Model_Product */
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            Mage::getSingleton('customer/session')->addError($this->__('The product was not found.'));
            $this->_redirect('/');
            return ;
        }

        try {
            $model = Mage::getModel('productalertextended/stock')
                ->setEmail($customerMail)
                ->setProductId($simpleProductId)
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
            
            $model->loadByParam();
            if ($model->getId()) {
                $model->delete();
                $session->addSuccess($this->__('You will no longer receive stock alerts for this product.'));
            } else {
                $session->addError('Unable to locate the record in the database.');
            }
        }
        catch (Exception $e) {
            $session->addException($e, $this->__('Unable to update the alert subscription.'));
        }
        $this->_redirectUrl($product->getProductUrl());
    }
}