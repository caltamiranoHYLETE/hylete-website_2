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
* @package     Vaimo_Carbon
* @copyright   Copyright (c) 2009-2015 Vaimo AB
*/

class Vaimo_Carbon_Block_Catalog_Product_List_Item extends Mage_Catalog_Block_Product_View_Abstract
{
    protected function _construct()
    {
        parent::_construct();

        $this->setCacheLifetime(3600);
    }

    protected function _getFormKeyPlaceholder($cacheKey = null)
    {
        if (is_null($cacheKey)) {
            $cacheKey = $this->getCacheKey();
        }

        return '<!--FORM_KEY=' . $cacheKey . '-->';
    }

    public function getCacheKeyInfo()
    {
        $localCacheKeyInfo = array(
            'product_id-' . $this->getProduct()->getId(),
            'customer_group-' . Mage::getSingleton('customer/session')->getCustomerGroupId(),
            'currency_code-' . Mage::app()->getStore()->getCurrentCurrencyCode(),
            'tax_class-' . $this->getProduct()->getTaxClassId(),
            'layout_updates-' . $this->getLayout()->getUpdate()->getCacheId()
        );

        $this->setCacheKeyInfo(array_merge(parent::getCacheKeyInfo(), $localCacheKeyInfo));

        return $this->getData('cache_key_info');
    }

    public function getAddToCartUrl($product, $additional = array())
    {
        $addToCartUrl = parent::getAddToCartUrl($product, $additional);

        return str_replace(
            '/' . $this->_getSingletonModel('core/session')->getFormKey() . '/',
            '/' . $this->_getFormKeyPlaceholder($this->getCacheKey()) . '/',
            $addToCartUrl
        );
    }

    protected function _loadCache()
    {
        if (!$output = parent::_loadCache()) {
            return $output;
        }

        return $this->_replaceDynamicValueTokens($output);
    }

    protected function _afterToHtml($output)
    {
        return $this->_replaceDynamicValueTokens(parent::_afterToHtml($output));
    }

    protected function _replaceDynamicValueTokens($output)
    {
        return str_replace(
            $this->_getFormKeyPlaceholder($this->getCacheKey()),
            $this->_getSingletonModel('core/session')->getFormKey(),
            $output
        );
    }

    protected function _toHtml()
    {
        $product = $this->getProduct();

        if (!$product || !$product instanceof Mage_Catalog_Model_Product) {
            return '';
        }

        return parent::_toHtml();
    }    
}