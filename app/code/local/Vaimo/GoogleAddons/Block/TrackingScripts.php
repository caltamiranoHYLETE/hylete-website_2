<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @package     Vaimo_GoogleAddons
 * @author      Giorgos Tsioutsiouliklis <giorgos@vaimo.com>
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Vaimo_GoogleAddons_Block_TrackingScripts extends Mage_Core_Block_Template
{
    
    var $_helper = null;

    private function _getHelper()
    {
        if (!$this->_helper) {
            $this->_helper = Mage::helper('googleaddons');
        }
        return $this->_helper;
    }

    public function isDynamicRemarketingEnabled()
    {
        return $this->_getHelper()->getUseDynamicRemarketing();
    }

    /**
     * Returns the page type as a string.
     * The available page types for dynamic remarketing can be found here :
     * https://support.google.com/tagmanager/answer/3002580?hl=en
     * Return defauls to 'other' so it should never be null.
     *
     * @return string
     */
    public function getDynamicRemarketingPageType()
    {
        switch ($this->getRequest()->getControllerName()) {
            case 'result':
                $pageType = 'searchresults';
                break;
            case 'category':
                $pageType = 'category';
                break;
            case 'product':
                $pageType = 'product';
                break;
            case 'cart':
                $pageType = 'cart';
                break;
            case 'checkout_onepage':
            case 'checkout':
            case 'onepage':
                $pageType = 'purchase';
                break;
            case 'klarna':
                if ($this->getRequest()->getRouteName() == 'checkout') {
                    $pageType = 'purchase';
                } else {
                    $pageType = 'other';
                }
                break;
            case 'index':
                if ($this->getRequest()->getRouteName() == 'cms') {
                    $pageType = 'home';
                } else {
                    $pageType = 'other';
                }
                break;
            default:
               $pageType = 'other';
               break;
        }

        return $pageType;
    }

    public function getDynamicRemarketingProductData($pageType = 'other')
    {
        $accumulatedProductData = array();
        $accumulatedProductData['total_price'] = 0;
        $productSkus = array();
        switch ($pageType) {
            case 'searchresult':
                $productCollection = Mage::getSingleton('catalogsearch/advanced')->getProductCollection();
                $productSkus = array();
                foreach ($productCollection as $product) {
                    $productSkus[] = $product->getSku();
                    $accumulatedProductData['total_price'] += $product->getFinalPrice();
                }
                break;
            case 'category':
                if ($category = Mage::registry('current_category')) {
                    $productCollection = $category->getProductCollection()->addFinalPrice();
                    $productSkus = array();
                    foreach ($productCollection as $product) {
                        $productSkus[] = $product->getSku();
                        $accumulatedProductData['total_price'] += $product->getFinalPrice();
                    }
                }
                break;
            case 'product':
                if ($product = Mage::registry('current_product')) {
                    $productSkus[] = Mage::registry('current_product')->getSku();
                    $accumulatedProductData['total_price'] = Mage::registry('current_product')->getPrice();
                }

                break;
            case 'cart':
            case 'purchase':
                $accumulatedProductData['total_price'] = Mage::helper('checkout/cart')->getQuote()->getGrandTotal();
                $visibleItems = Mage::helper('checkout/cart')->getQuote()->getAllVisibleItems();
                $productSkus = array();
                foreach ($visibleItems as $product) {
                    $productSkus[] = $product->getSku();
                }
                break;
            case 'home':
            default:
                $productSkus[] = '';
                $accumulatedProductData['total_price'] = '';
                break;
        }
        
        $accumulatedProductData['product_skus'] = Mage::helper('core')->jsonEncode($productSkus);
        return $accumulatedProductData;
    }

}
