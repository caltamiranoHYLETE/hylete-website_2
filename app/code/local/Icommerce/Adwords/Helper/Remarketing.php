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
 * @package     Icommerce_Adwords
 * @copyright   Copyright (c) 2009-2015 Vaimo Norge AS
 * @author      Simen Thorsrud <simen.thorsrud@vaimo.com>
 */

/**
 * Class Icommerce_Adwords_Helper_Remarketing
 */
class Icommerce_Adwords_Helper_Remarketing extends Mage_Core_Helper_Abstract
{

    /**
     * Gets current request's controller name.
     *
     * Used to set page type for Google Remarketing google_tag_params
     *
     * @return string
     */
    public function getCurrentControllerName()
    {
        /** @var string $controllerName */
        $controllerName = Mage::app()->getRequest()->getControllerName();

        return $controllerName;
    }

    /**
     * Get the current request's action name
     *
     * @return string
     */
    public function getCurrentActionName()
    {
        /** @var string $actionName */
        $actionName = Mage::app()->getRequest()->getActionName();

        return $actionName;
    }

    /**
     * Get the current request's route name
     *
     * @return string
     */
    public function getCurrentRouteName()
    {
        /** @var string $routeName */
        $routeName = Mage::app()->getRequest()->getRouteName();

        return $routeName;
    }

    /**
     * Get the current request's controller module name
     *
     * @return string
     */
    public function getCurrentControllerModule()
    {
        /** @var string $moduleName */
        $moduleName = Mage::app()->getRequest()->getControllerModule();

        return $moduleName;
    }

    /**
     * Returns true if we are on a search result page
     *
     * @return bool
     */
    public function onSearchResultPage()
    {
        /** @var bool $onSearchResultPage */
        $onSearchResultPage = false;

        /** @var string $currentControllerName */
        $currentControllerName = $this->getCurrentControllerName();

        /** @var string $currentActionName */
        $currentActionName = $this->getCurrentActionName();

        /** @var string $currentControllerModule */
        $currentControllerModule = $this->getCurrentControllerModule();

        /** @var string $currentRouteName */
        $currentRouteName = $this->getCurrentRouteName();

        // Advanced search results page
        if ($currentControllerName == 'advanced' && $currentActionName == 'result') {
            $onSearchResultPage = true;
        }

        // Regular search results page
        if ($currentControllerName == 'result' && $currentActionName == 'index') {
            $onSearchResultPage = true;
        }

        /*
         * Had to add this because Icommerce_MultiOptionFilter overwrites
         * catalogsearch controller with its own "category" controller
         */
        if (
            $currentControllerModule == 'Icommerce_MultiOptionFilter'
            && $currentControllerName == 'category'
            && $currentActionName == 'searchResult'
        ) {
            $onSearchResultPage = true;
        }

        /*
         * Klevu search result page
         */
        if ($currentRouteName == 'search' && $currentControllerModule == 'Klevu_Search') {
            $onSearchResultPage = true;
        }

        return $onSearchResultPage;
    }

    /**
     * Check if we are on product page
     *
     * @return bool
     */
    public function onProductPage()
    {
        /** @var false|Mage_Catalog_Model_Product $product */
        $product = Mage::registry('current_product');

        return ($product) ? true : false;
    }

    /**
     * Check if we are on category page
     *
     * @return bool
     */
    public function onCategoryPage()
    {
        /** @var false|Mage_Catalog_Model_Product $product */
        $product = Mage::registry('current_product');

        /** @var false|Mage_Catalog_Model_Category $category */
        $category = Mage::registry('current_category');

        return (!$product && $category) ? true : false;
    }

    /**
     * Check if we are on home page
     *
     * @return bool
     */
    public function onHomePage()
    {
        /** @var string $currentControllerName */
        $currentControllerName = $this->getCurrentControllerName();

        /** @var string $currentActionName */
        $currentActionName = $this->getCurrentActionName();

        /** @var string $currentControllerModule */
        $currentControllerModule = $this->getCurrentControllerModule();

        if (
            ($currentControllerName == 'index' && $currentActionName == 'cms')
            || ($currentControllerName == 'index'
                && $currentActionName == 'index'
                && $currentControllerModule == 'Mage_Cms')
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check if we are in cart
     *
     * @return bool
     */
    public function onCartPage()
    {
        /** @var string $currentControllerName */
        $currentControllerName = $this->getCurrentControllerName();

        if ($currentControllerName == 'cart') {
            return true;
        }

        return false;
    }

    /**
     * Check if we are in checkout
     *
     * @return bool
     */
    public function onCheckoutPage()
    {
        /** @var string $currentControllerName */
        $currentControllerName = $this->getCurrentControllerName();

        if ($currentControllerName == 'onepage' || $currentControllerName == 'quickcheckout') {
            return true;
        }

        return false;
    }

    /**
     * Get current checkout session's quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        /** @var Mage_Checkout_Helper_Cart $cartHelper */
        $cartHelper = $this->getCartHelper();

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $cartHelper->getQuote();

        return $quote;
    }

    /**
     * Get cart helper
     *
     * @return Mage_Checkout_Helper_Cart
     */
    public function getCartHelper()
    {
        if (!isset($this->_cartHelper)) {

            /** @var Mage_Checkout_Helper_Cart $helper */
            $helper = Mage::helper('checkout/cart');

            $this->_cartHelper = $helper;
        }

        return $this->_cartHelper;
    }

    /**
     * Get current category
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory()
    {
        /** @var false|Mage_Catalog_Model_Category $currentCategory */
        $currentCategory = Mage::registry('current_category');

        if ($currentCategory instanceof Mage_Catalog_Model_Category) {
            return $currentCategory;
        }

        return Mage::getModel('catalog/category');
    }

    /**
     * Get current catalog layer
     *
     * @return Mage_Catalog_Model_Layer
     */
    public function getCatalogLayer()
    {
        /** @var false|Mage_Catalog_Model_Layer $layer */
        $layer = Mage::registry('current_layer');

        if ($layer) {
            return $layer;
        }

        return Mage::getSingleton('catalog/layer');
    }
}
