<?php
/**
 * Copyright (c) 2009-2017 Vaimo Group
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
 * @package     Vaimo_Cms
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Vaimo_Cms_Helper_Data extends Vaimo_Cms_Helper_Abstract
{
    const TOOLBAR_NAME = 'cms.admin.toolbar';
    const TOOLBAR_ITEMS = 'cms.toolbar.items';
    const PAGE_TYPE_ATTRIBUTE_CODE = 'page_type';
    const TREE_ICON_STARTPAGE = 'vcms-icon-startpage';
    const TREE_ICON_CONTENT = 'vcms-icon-content';

    const CMS_HOME_PAGE_CONTROLLER_ACTION = 'cms_index_index';
    const CMS_PAGE_CONTROLLER_ACTION = 'cms_page_view';
    const CATEGORY_VIEW_CONTROLLER_ACTION = 'catalog_category_view';
    const PRODUCT_VIEW_CONTROLLER_ACTION = 'catalog_product_view';

    const XPATH_CONFIG_STAGING_ENABLED = 'vaimo_cms/settings/staging_enabled';
    const XPATH_CONFIG_REWRITE_URLS = 'vaimo_cms/settings/rewrite_navigate_urls';
    const XPATH_CONFIG_REUSE_CMS_BLOCKS = 'vaimo_cms/settings/reuse_cms_blocks';

    const FPC_NO_CACHE_REQUEST_PARAM = '___from_store';
    const FPC_CACHE_FLAG = 'full_page';

    const REGISTRY_CATEGORY_ACTIVATION = 'allow_category_activation';

    const AMPERSAND = '&';

    protected $_packageThemeForStore = array();

    public function getDefaultStore()
    {
        $app = $this->getApp();

        $websites = $app->getWebsites();
        $website = reset($websites);
        $store = $app->getStore($website->getDefaultStore()->getCode());

        return $store;
    }

    public function getFirstStoreForCategory($category)
    {
        $defaultStore = $this->getDefaultStore();
        $storeId = $defaultStore->getId();

        $categoryPath = array_flip(explode('/', $category->getPath()));

        foreach ($this->getApp()->getStores() as $store) {
            if (!isset($categoryPath[$store->getRootCategoryId()])) {
                continue;
            }

            return $store->getId();
        }

        return $storeId;
    }

    public function getDefaultStoreId()
    {
        return $this->getDefaultStore()->getId();
    }

    public function isCurrentCategoryInCurrentRootCategory($category, $storeId)
    {
        $rootCategoryId = $this->getApp()->getStore($storeId)->getRootCategoryId();

        return in_array($rootCategoryId, $category->getPathIds());
    }

    public function getCmsConfigurationFormAttributeCodes()
    {
        return array(
            'name',
            'is_active',
            'url_key',
            'page_layout',
            'meta_keywords',
            'meta_description'
        );
    }

    public function isCmsPage($category)
    {
        if (!$category) {
            return false;
        }

        if ($category->hasLevel() && $category->getLevel() <= 1) {
            return true;
        }

        return $category->hasPageType() && $category->getPageType() == \Vaimo_Cms_Model_Page_Type::TYPE_CMS;
    }

    public function getEditableBlocksSelectors()
    {
        /**
         * note that we're excluding items that are targeting nested block output - those blocks we do not allow to be
         * edited.
         */
        return array(
            '[data-block-id]:not([data-block-content])'
        );
    }

    public function isStoreCodeEnabled()
    {
        return Mage::getStoreConfigFlag('web/url/use_store');
    }

    public function setNoCacheHeadersForAction($action)
    {
        $request = $action->getRequest();
        $response = $action->getResponse();

        $app = $this->getApp();

        $request->setParam(\Vaimo_Cms_Helper_Data::FPC_NO_CACHE_REQUEST_PARAM, $app->getStore()->getId());
        $app->getCacheInstance()->banUse(self::FPC_CACHE_FLAG);
        $app->getCacheInstance()->banUse(Mage_Core_Block_Abstract::CACHE_GROUP);

        $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $response->setHeader('Pragma', 'no-cache');
    }

    public function getWidgetErrorHtml($id, $exception)
    {
        $errorMessage = '';

        $isDevMode = Mage::getIsDeveloperMode();
        $updateHandles = $this->getApp()->getLayout()->getUpdate()->getHandles();
        $includeMessage = array_search(Vaimo_Cms_Model_Mode::EDIT_MODE_HANDLE, $updateHandles) !== false || $isDevMode;

        $errorWrapperAttributes = ' data-vcms-error-reference="' . $id . '"';

        if (!$isDevMode && !$includeMessage) {
            $errorWrapperAttributes .= ' data-vcms-error-code="' . $exception->getCode() . '"';
        }

        if ($includeMessage) {
            $errorMessage = '<strong>' . $exception->getMessage() . '</strong>';
        }

        if ($isDevMode) {
            $errorMessage = $errorMessage . '<br><br>' . str_replace("\n", '<br>', $exception->getTraceAsString());
        }

        return '<div' . $errorWrapperAttributes . '>' . $errorMessage . '</div>';
    }

    /**
     * Retrieve current url with unescaped
     * ampersands
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return str_replace(
            htmlspecialchars(self::AMPERSAND),
            self::AMPERSAND,
            $this->getFactory()->getHelper('core/url')->getCurrentUrl()
        );
    }

    public function formatValues($values, $format)
    {
        $formattedValues = array();

        foreach ($values as $key => $value) {
            if ($value && isset($format[$key])) {
                $value = sprintf($format[$key], $value);
            }

            $formattedValues[$key] = $value;
        }

        return $formattedValues;
    }

    public function implodeSelected($glue, $values, $keys)
    {
        $selection = array_fill_keys($keys, false);

        $sortedSelection = array_replace(
            $selection,
            array_intersect_key($values, $selection)
        );

        $filteredSelection = array_intersect_key($sortedSelection, $values);

        return implode($glue, $filteredSelection);
    }

    public function sortArrayItemsByKey($items, $key, $direction = Varien_Data_Collection::SORT_ORDER_ASC)
    {
        $direction = strtoupper($direction);

        usort($items, function($a, $b) use ($key, $direction) {
            $result = $a[$key] > $b[$key] ? 1 : ($a[$key] < $b[$key] ? -1 : 0);

            return Varien_Data_Collection::SORT_ORDER_ASC === $direction ? $result : -$result;
        });

        return $items;
    }
}