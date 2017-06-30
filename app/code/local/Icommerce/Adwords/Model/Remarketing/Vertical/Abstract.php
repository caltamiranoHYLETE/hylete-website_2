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
 * Class Icommerce_Adwords_Model_Remarketing_Vertical_Abstract
 */
abstract class Icommerce_Adwords_Model_Remarketing_Vertical_Abstract
{

    /** @var Icommerce_Adwords_Helper_Data */
    protected $_helper;

    /** @var Icommerce_Adwords_Helper_Config */
    protected $_configHelper;

    /** @var Icommerce_Adwords_Helper_Remarketing */
    protected $_remarketingHelper;

    /** @var Mage_Checkout_Helper_Cart */
    protected $_cartHelper;

    /** @var string Vertical code. E.g. "retail" */
    protected $_verticalCode;

    /**
     * @param string|false $verticalCode
     */
    public function __construct($verticalCode = false)
    {
        /** @var Icommerce_Adwords_Helper_Config $configHelper */
        $configHelper = $this->_getConfigHelper();

        if (!$verticalCode) {
            $verticalCode = $configHelper->getCurrentVertical();
        }

        $this->_verticalCode = $verticalCode;
    }

    /**
     * In all child classes, this method should return an array of google_tag_params
     *
     * @link https://support.google.com/adwords/answer/3103357?hl=en
     * @link https://developers.google.com/adwords-remarketing-tag/parameters?hl=en
     * @return array
     */
    public abstract function getGoogleTagParamsArray();

    /**
     * Get label for vertical. For use in Magento config
     *
     * @return string
     */
    public abstract function getLabel();

    /**
     * @return string
     */
    public function getVerticalCode()
    {
        return $this->_verticalCode;
    }


    /**
     * Get SKUs on current page
     *
     * @return array
     */
    protected function _getSkusOnPage()
    {
        /** @var array|Mage_Catalog_Model_Resource_Product_Collection $productsOnPage */
        $productsOnPage = $this->_getProductsOnCurrentPage();

        /** @var array $skusOnPage */
        $skusOnPage = array();

        /** @var Mage_Catalog_Model_Product|Mage_Sales_Model_Quote_Item $product */
        foreach ($productsOnPage as $product) {
            $skusOnPage[] = $product->getSku();
        }

        return $skusOnPage;
    }

    /**
     * Get all products on current page
     *
     * @return Mage_Sales_Model_Quote_Item[]|Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _getProductsOnCurrentPage()
    {
        /** @var Icommerce_Adwords_Helper_Remarketing $remarketingHelper */
        $remarketingHelper = $this->_getRemarketingHelper();

        /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
        $productCollection = $this->_getEmptyProductCollection();

        if ($remarketingHelper->onSearchResultPage()) {
            $productCollection = $this->_getProductsOnSearchResultPage();
        } else if ($remarketingHelper->onHomePage()) {
            $productCollection = $this->_getProductsOnHomePage();
        } else if ($remarketingHelper->onCategoryPage()) {
            $productCollection = $this->_getProductsOnCategoryPage();
        } else if ($remarketingHelper->onProductPage()) {
            $productCollection = $this->_getSingleProductOnProductPage();
        } else if ($remarketingHelper->onCartPage()) {

            /** @var array $productCollection */
            $productCollection = $this->_getItemsFromQuote();
        } else if ($remarketingHelper->onCheckoutPage()) {

            /** @var array $productCollection */
            $productCollection = $this->_getItemsFromQuote();
        }

        return $productCollection;
    }

    /**
     * Get total value of products on page
     *
     * @todo: Rewrite this so it gets total value of all products on page regardless of
     *      what page it is. It is not the responsibility of this abstract class to
     *      decide if total value should be fetched or not.
     *
     * @return float|string
     */
    protected function _getTotalValueOnPage()
    {
        $totalValue = '';

        /** @var Icommerce_Adwords_Helper_Remarketing $remarketingHelper */
        $remarketingHelper = $this->_getRemarketingHelper();


        if ($remarketingHelper->onProductPage()) {

            $product = Mage::registry('current_product');

            if (is_object($product) && $product->getId()) {
                $totalValue = $product->getFinalPrice();
            }
        } else if ($remarketingHelper->onCartPage() || $remarketingHelper->onCheckoutPage()) {

            $items = $this->_getItemsFromQuote();

            $totalValue = 0.0000;

            /** @var Mage_Sales_Model_Quote_Item $item */
            foreach ($items as $item) {
                $totalValue += $item->getRowTotalInclTax();
            }
        }

        return $totalValue;
    }

    /**
     * Get products from quote object
     *
     * @return Mage_Sales_Model_Quote_Item[]
     */
    protected function _getItemsFromQuote()
    {
        /** @var Icommerce_Adwords_Helper_Remarketing $remarketingHelper */
        $remarketingHelper = $this->_getRemarketingHelper();

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $remarketingHelper->getQuote();

        $visibleItems = $quote->getAllVisibleItems();

        return $visibleItems;

    }

    /**
     * Get one - and only one - product from product page
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _getSingleProductOnProductPage()
    {
        /** @var false|Mage_Catalog_Model_Product $product */
        $product = Mage::registry('current_product');

        /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
        $productCollection = $this->_getEmptyProductCollection();

        if (!$product) {
            return $productCollection;
        }

        /** @var string $typeId */
        $typeId = $product->getTypeId();

        if ($typeId == 'configurable') {
            $productCollection->addItem($product);
        } else if ($typeId == 'bundle') {
            // Add itself to collection of child products
            $productCollection->addItem($product);

        } else {
            // Add current product to empty product collection
            $productCollection->addItem($product);
        }

        return $productCollection;
    }

    /**
     * Get collection of products on product page
     *
     * There is some uncertainty if we should indeed include the skus of child
     * products or not. This method might be a lot easier to write if all we need is to
     * get the visible product's sku
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _getAllProductsOnProductPage()
    {
        /** @var false|Mage_Catalog_Model_Product $product */
        $product = Mage::registry('current_product');

        /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
        $productCollection = $this->_getEmptyProductCollection();

        if (!$product) {
            return $productCollection;
        }

        /** @var string $typeId */
        $typeId = $product->getTypeId();

        if ($typeId == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {

            /** @var Mage_Catalog_Model_Product_Type_Configurable $productTypeConfigurable */
            $productTypeConfigurable = $product->getTypeInstance(true);

            /** @var Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Type_Configurable_Product_Collection
             *  $productCollection */
            $productCollection = $productTypeConfigurable->getUsedProductCollection($product);

            // Add itself to collection of child products
            $productCollection->addItem($product);

        } else if ($typeId == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {

            /** @var Mage_Bundle_Model_Product_Type $productTypeBundle */
            $productTypeBundle = $product->getTypeInstance(true);

            /** @var array $optionIds */
            $optionIds = $productTypeBundle->getOptionsIds($product);

            /** @var Mage_Bundle_Model_Mysql4_Selection_Collection $selectionsCollection */
            $selectionsCollection = $productTypeBundle->getSelectionsCollection($optionIds, $product);

            // Add itself to collection of child products
            $selectionsCollection->addItem($product);

        } else {

            // Add current product to empty product collection
            $productCollection->addItem($product);
        }

        return $productCollection;
    }

    /**
     * Get products on category page
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _getProductsOnCategoryPage()
    {
        /** @var Icommerce_Adwords_Helper_Remarketing $remarketingHelper */
        $remarketingHelper = $this->_getRemarketingHelper();

        /** @var Mage_Catalog_Model_Layer $layer */
        $layer = $remarketingHelper->getCatalogLayer();

        /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
        $productCollection = $layer->getProductCollection();

        if ($productCollection->isLoaded()) {
            return $productCollection;
        }

        return $this->_getEmptyProductCollection();
    }

    /**
     * Get products on home page
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _getProductsOnHomePage()
    {
        /** @var Mage_Catalog_Model_Layer $layer */
        $layer = Mage::getSingleton('catalog/layer');

        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = $layer->getProductCollection();

        /*
         * Do not return product collection unless it is loaded
         */
        if ($collection->isLoaded()) {
           return $collection;
        }

        return $this->_getEmptyProductCollection();
    }

    /**
     * Get products on search result page
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _getProductsOnSearchResultPage()
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
        $productCollection = Mage::getSingleton('catalogsearch/layer')->getProductCollection();

        return $productCollection;
    }

    /**
     * Get product collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _getProductCollection()
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
        $productCollection = Mage::getResourceModel('catalog/product_collection');

        /** @var Mage_Catalog_Model_Config $catalogConfig */
        $catalogConfig = Mage::getSingleton('catalog/config');

        // Preparing query in case we need to get new products
        $productCollection
            ->addAttributeToSelect($catalogConfig->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents();

        return $productCollection;
    }

    /**
     * Get product collection with 0 items
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _getEmptyProductCollection()
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
        $productCollection = $this->_getProductCollection();

        $productCollection->addAttributeToFilter('entity_id', array('eq', 'NULL'));

        return $productCollection;

    }

    /**
     * Gets current page type.
     *
     * Should be rewritten in child classes so that page type is one of the allowed
     * options for the current business type as defined in Google Adwords documentation
     *
     * @link https://support.google.com/adwords/answer/3103357?hl=en
     * @link https://developers.google.com/adwords-remarketing-tag/parameters?hl=en
     * @see Icommerce_Adwords_Model_Remarketing_Vertical_Retail::_getCurrentPageType()
     *
     * @return string
     */
    protected function _getCurrentPageType()
    {
        /** @var Icommerce_Adwords_Helper_Remarketing $remarketingHelper */
        $remarketingHelper = $this->_getRemarketingHelper();

        /** @var string $pageType */
        $pageType = $remarketingHelper->getCurrentControllerName();

        return $pageType;
    }

    /**
     * Get Adwords config helper
     *
     * @return Icommerce_Adwords_Helper_Config
     */
    protected function _getConfigHelper()
    {
        if (!isset($this->_configHelper)) {

            /** @var Icommerce_Adwords_Helper_Config $configHelper */
            $configHelper = Mage::helper('adwords/config');

            $this->_configHelper = $configHelper;
        }

        return $this->_configHelper;
    }

    /**
     * Get Adwords helper
     *
     * @return Icommerce_Adwords_Helper_Data
     */
    protected function _getHelper()
    {
        if (!isset($this->_helper)) {

            /** @var Icommerce_Adwords_Helper_Data $helper */
            $helper = Mage::helper('adwords');

            $this->_helper = $helper;
        }

        return $this->_helper;
    }

    /**
     * Get Remarketing helper
     *
     * @return Icommerce_Adwords_Helper_Remarketing
     */
    protected function _getRemarketingHelper()
    {
        if (!isset($this->_remarketingHelper)) {

            /** @var Icommerce_Adwords_Helper_Remarketing $helper */
            $helper = Mage::helper('adwords/remarketing');

            $this->_remarketingHelper = $helper;
        }

        return $this->_remarketingHelper;
    }
}
