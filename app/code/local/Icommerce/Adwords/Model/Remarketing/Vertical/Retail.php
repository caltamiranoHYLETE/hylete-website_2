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
 * Class Icommerce_Adwords_Model_Remarketing_Vertical_Retail
 */
class Icommerce_Adwords_Model_Remarketing_Vertical_Retail
    extends Icommerce_Adwords_Model_Remarketing_Vertical_Abstract
{

    /** @var array  Which page types to show the parameter "ecomm_prodid" on
     *              @link https://developers.google.com/adwords-remarketing-tag/parameters?hl=en */
    protected $_pageTypesToShowProdIdOn = array(
        'cart',
        'purchase',
        'product',
    );

    /** @var array  Which page types to show the parameter "ecomm_totalvalue" on
     *              @link https://developers.google.com/adwords-remarketing-tag/parameters?hl=en */
    protected $_pagesToShowTotalValueOn = array(
        'cart',
        'purchase',
        'product',
    );

    /** @var array  Which page types to show the parameter "ecomm_category" on
     *              @link https://developers.google.com/adwords-remarketing-tag/parameters?hl=en */
    protected $_pagesToShowCategoryOn = array(
        'category',
        'product',
    );

    /**
     * Get label of vertical. E.g. "Retail", "Jobs", "Local deals"
     *
     * @return string
     */
    public function getLabel()
    {
        $label = Mage::getStoreConfig('adwords/verticals/retail/label');

        /** @var Icommerce_Adwords_Helper_Data $helper */
        $helper = $this->_getHelper();

        return $helper->__($label);
    }

    /**
     * Get google_tag_params in array format.
     *
     * @link https://support.google.com/adwords/answer/3103357?hl=en
     * @link https://developers.google.com/adwords-remarketing-tag/parameters?hl=en
     * @return array
     */
    public function getGoogleTagParamsArray()
    {
        /** @var array $params */
        $params = array();

        /** @var string $pageType */
        $pageType = $this->_getCurrentPageType();

        /** @var Icommerce_Adwords_Helper_Remarketing $remarketingHelper */
        $remarketingHelper = $this->_getRemarketingHelper();

        if (in_array($pageType, $this->_pageTypesToShowProdIdOn)) {

            /** @var string $productSkus Comma separated string of skus */
            $productSkus = $this->_getSkusOnPage();
            $params['ecomm_prodid'] = $productSkus;
        }

        if (in_array($pageType, $this->_pagesToShowTotalValueOn)) {

            /** @var float|string $totalValue */
            $totalValue = $this->_getTotalValueOnPage();
            $params['ecomm_totalvalue'] = $totalValue;
        }

        if (in_array($pageType, $this->_pagesToShowCategoryOn)) {

            /** @var Mage_Catalog_Model_Category $category */
            $category = $remarketingHelper->getCurrentCategory();
            $params['ecomm_category'] = $category->getName();

        }

        $params['ecomm_pagetype'] = $pageType;

        return $params;

    }

    /**
     * Get current page type as allowed by Google Remarketing
     *
     * Unless special page default to "other".
     *
     * @link https://support.google.com/adwords/answer/3103357?hl=en
     * @link https://developers.google.com/adwords-remarketing-tag/parameters?hl=en
     * @return string
     */
    protected function _getCurrentPageType()
    {
        /** @var Icommerce_Adwords_Helper_Remarketing $remarketingHelper */
        $remarketingHelper = $this->_getRemarketingHelper();

        /** @var string $currentPageType */
        $currentPageType = 'other';

        if ($remarketingHelper->onCheckoutPage()) {
            $currentPageType = 'purchase';
        } else if ($remarketingHelper->onCartPage()) {
            $currentPageType = 'cart';
        } else if ($remarketingHelper->onHomePage()) {
            $currentPageType = 'home';
        } else if ($remarketingHelper->onSearchResultPage()) {
            $currentPageType = 'searchresults';
        } else if ($remarketingHelper->onCategoryPage()) {
            $currentPageType = 'category';
        } else if ($remarketingHelper->onProductPage()) {
            $currentPageType = 'product';
        }

        return $currentPageType;
    }
}
