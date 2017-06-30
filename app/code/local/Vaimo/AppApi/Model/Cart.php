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

class Vaimo_AppApi_Model_Cart extends Vaimo_AppApi_Model_Abstract
{

    protected function _getParentProduct($product)
    {
        $productParent = Mage::getModel('catalog/product')->setTypeId('configurable');
        $parentId = $productParent->getTypeInstance()->getParentIdsByChild($product->getId());
        if (isset($parentId)) {
            return Mage::getModel('catalog/product')
                ->setStoreId($product->getStoreId())
                ->load($parentId);
        }
        return null;
    }

    public function productsToCart($websiteId, $storeId, $products, $redirectUrl)
    {
        try {
            $sku = '';
            // Whole concept of selectedStoreId in here is no longer required, I think...
            $selectedStoreId = $this->_getHelper()->getStoreId($websiteId, $storeId);

            $cart = Mage::getModel('checkout/cart');
            $cart->init();

            $productAdd = json_decode($products, true);
            foreach($productAdd as $productCombo) {
                $sku = $productCombo['sku'];
                $parentSku = $productCombo['parent_sku'];
                $qty = $productCombo['qty'];
                $product = Mage::getModel('catalog/product')->setStoreId($selectedStoreId);
                $product->load($product->getIdBySku($sku));
                if (!$product || !$product->getId()) {
                    return Mage::helper('appapi')->__('Product not found');
                }
                $options = array();
                if (!$parentSku) {
                    $parentProduct = $this->_getParentProduct($product);
                } else {
                    $parentProduct = Mage::getModel('catalog/product')->setStoreId($selectedStoreId);
                    $parentProduct->load($parentProduct->getIdBySku($parentSku));
                }
                if ($parentProduct){
                    Mage::helper('appapi')->patchSetIgnorePrices(true);
                    $productAttributesOptions = $parentProduct->getTypeInstance(true)->getConfigurableAttributesAsArray($parentProduct);
                    Mage::helper('appapi')->patchSetIgnorePrices(false);
                    $options['product'] = $parentProduct->getId();
                    $options['qty'] = $qty;
                    foreach ($productAttributesOptions as $opt_vals){
                        $options['super_attribute'][$opt_vals['attribute_id']] = $product->getData($opt_vals['attribute_code']);
                    }
                    $product = $parentProduct;
                }

                $cart->addProduct($product , $options);
            }
            $payment = $cart->getQuote()->getPayment();
            if ($payment) {
                if (!$redirectUrl) {
                    $redirectUrl = 'appapi/cart/complete';
                }
                $payment->setAdditionalInformation('redirect_to_app_url', $redirectUrl);
            }
            $cart->save();

        } catch (Mage_Api_Exception $e) {
            if ($e->getCustomMessage()) {
                return $e->getCustomMessage() . ' (SKU: ' . $sku . ')';
            } else {
                return $e->getMessage() . ' (SKU: ' . $sku . ')';
            }
        } catch (Exception $e) {
            return $e->getMessage() . ' (SKU: ' . $sku . ')';
        }
        return true;
    }

}
