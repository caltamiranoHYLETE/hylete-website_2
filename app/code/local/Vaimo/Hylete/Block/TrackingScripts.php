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
 * @package     Vaimo_Hylete
 * @author      Scott Kennerly <skennerly@hylete.com>
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Vaimo_Hylete_Block_TrackingScripts extends Vaimo_GoogleAddons_Block_TrackingScripts
{
    public function getDynamicRemarketingCustomerData() {
        $accumulatedCustomerData = array();

        $accumulatedCustomerData['customer_group_id'] = '0';
        $accumulatedCustomerData['customer_logged_in'] = '0';
        $accumulatedCustomerData['customer_gender'] = '';
        $accumulatedCustomerData['customer_id'] = '0';
        $accumulatedCustomerData['customer_email'] = '';


// Should not query session specific data due FPC and pages being unique
// ER> Moved to vendor/vaimo/hylete/app/design/frontend/carbon/hylete/template/checkout/cart/header.phtml

//        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
//            $customer = Mage::getSingleton('customer/session')->getCustomer();
//            $accumulatedCustomerData['customer_group_id'] = $customer->getGroupId();
//            $accumulatedCustomerData['customer_logged_in'] = 1;
//            $accumulatedCustomerData['customer_gender'] = $customer->getGender();
//            $accumulatedCustomerData['customer_id'] = $customer->getId();
//            $accumulatedCustomerData['customer_email'] = $customer->getEmail();
//        }

        return $accumulatedCustomerData;
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
                    $accumulatedProductData['product_gender'] = Mage::registry('current_product')->getAttributeText('gender');
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

        $accumulatedProductData['product_skus_array'] = $productSkus;
        $accumulatedProductData['product_skus'] = Mage::helper('core')->jsonEncode($productSkus);
        return $accumulatedProductData;
    }
}