<?php
/**
 * Copyright (c) 2009-2016 Vaimo AB
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
 * @package     Vaimo_MultiOptionFilter
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 */

class Vaimo_MultiOptionFilter_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_configValues = array();
    protected $_paramLookupTable = null;
    protected $_excludedAttributes = array();
    protected $_layerNames = array();
    protected $_dummyView;
    protected $_items;
    protected $_isAttributeGroupingActive;

    public static function makeFilterCanonical(&$filter)
    {
        ksort($filter);

        foreach ($filter as $attributeCode => $val) {
            if (!is_string($val)) {
                continue;
            }

            $values = explode(',', $val);
            sort($values);
            $filter[$attributeCode] = implode(',', $values);
        }
    }

    protected function _getParamLookupTable()
    {
        if ($this->_paramLookupTable === null) {
            $this->_paramLookupTable = array();
            $params = Mage::app()->getRequest()->getParams();

            $ignoreKeys = explode(',', (string)$this->_getConfigValue('multioptionfilter/settings/params_ignore_list'));

            foreach ($params as $key => $value) {
                if ($value === '') {
                    continue;
                }

                if ($key == 'id' || $key == '?id') {
                    continue;
                }

                if ($key === 'price') {
                    $this->_paramLookupTable[$key][$value] = true;
                    continue;
                }

                if (in_array($key, $ignoreKeys)) {
                    continue;
                }

                if (!is_array($value)) {
                    $value = explode(',', $value);
                }

                foreach ($value as $optionId) {
                    if (is_array($optionId)) {
                        continue;
                    }

                    $this->_paramLookupTable[$key][$optionId] = true;
                }
            }
        }

        return $this->_paramLookupTable;
    }

    public function isFilterControllerAction($action)
    {
        $fullActionName = $action->getFullActionName();

        if ($fullActionName == 'catalog_category_view') {
            $handles = array_flip($action->getLayout()->getUpdate()->getHandles());

            return isset($handles['catalog_category_layered']);
        }

        if ($fullActionName == 'catalogsearch_result_index') {
            return true;
        }

        if ($fullActionName == 'catalog_category_searchResult') {
            return true;
        }

        return false;
    }

    public function getLayerBlocks($createIfNotFound = false)
    {
        $action = Mage::app()->getFrontController()->getAction();

        $actionKey = $action->getFullActionName();
        if ($layout = Mage::app()->getLayout()) {
            if ($handles = $layout->getUpdate()->getHandles()) {
                $actionKey = md5(implode('::', $handles));
            }
        }

        $cacheId = $actionKey . '_navigation_layer_layout_names_' . (int)$action->getRequest()->isXmlHttpRequest();

        if (!isset($this->_layerNames[$cacheId])) {
            $this->_layerNames[$cacheId] = unserialize($this->_loadCache($cacheId));
        }

        $navLayerNames = $this->_layerNames[$cacheId];

        if ($blocks = $layout->getAllBlocks()) {
            $layerNamesNotSpecified = !$navLayerNames && !is_array($navLayerNames);

            if (!$layerNamesNotSpecified) {
                $blocks = array_intersect_key($blocks, array_filter((array)$navLayerNames));
            }

            if ($layerNamesNotSpecified || (!$blocks && $navLayerNames)) {
                $navLayerNames = array();

                foreach ($blocks as $nameInLayout => $block) {
                    if ($block instanceof Mage_Catalog_Block_Layer_View) {
                        $navLayerNames[$nameInLayout] = true;
                    }
                }

                $this->_saveCache(serialize($navLayerNames), $cacheId, array('LAYOUT_GENERAL_CACHE_TAG'), 86400);
                $this->_layerNames[$cacheId] = $navLayerNames;
            }
        } else {
            if (!$navLayerNames) {
                $navLayerNames = array();
            }
        }

        $blocks = array_intersect_key($blocks, $navLayerNames);

        if (is_object($blocks)) {
            $blocks = array($blocks);
        }

        if ($createIfNotFound && !$blocks) {
            if (!$this->_dummyView) {
                $blocksBefore = $layout->getAllBlocks();

                if (!Mage::helper('core')->isModuleEnabled('Enterprise_Enterprise')) {
                    $this->_dummyView = $layout->createBlock('catalog/layer_view');
                } else {
                    $layer = Mage::registry(Vaimo_MultiOptionFilter_Helper_Registry::LAYER);

                    if (Mage::helper('enterprise_search')->getIsEngineAvailableForNavigation()) {
                        $layer = Mage::getSingleton('enterprise_search/catalog_layer');
                    }

                    $this->_dummyView = $layout->createBlock('enterprise_search/catalog_layer_view');

                    Mage::helper('multioptionfilter/registry')
                        ->set(Vaimo_MultiOptionFilter_Helper_Registry::LAYER, $layer);
                }

                foreach ($layout->getAllBlocks() as $block) {
                    $name = $block->getNameInLayout();

                    if (isset($blocksBefore[$name])) {
                        continue;
                    }

                    $layout->unsetBlock($name);
                }
            }

            $blocks = array($this->_dummyView);
        }

        return $blocks;
    }

    /**
     * Return first layer navigation block name
     *
     * @param bool $createIfNotFound
     *
     * @return mixed
     */
    public function getLayerBlock($createIfNotFound = false)
    {
        $blocks = $this->getLayerBlocks($createIfNotFound);

        return reset($blocks);
    }

    public function getSelectedLayerOptions($filterBlock)
    {
        $selection = array(
            'code' => $this->getAttributeCode($filterBlock),
            'selected' => array()
        );

        $items = $filterBlock->getItems();
        if ($items && $selection['code']) {
            $i = 0;

            foreach ($items as $item) {
                if ($this->isChecked($selection['code'], $item->getValue())) {
                    $selection['selected'][$item->getValue()] = $i;
                }

                $i++;
            }
        }

        if ($selection['selected']) {
            return $selection;
        }

        return false;
    }

    public function getAllSelectedLayerOptions($onlyRendered = false)
    {
        $layerView = $this->getLayerBlock();

        $selections = array();
        $filterBlocks = $layerView->getChild();

        foreach ($filterBlocks as $filterBlock) {
            if (!$onlyRendered || $filterBlock->getIsRendered()) {
                if ($selection = $this->getSelectedLayerOptions($filterBlock)) {
                    $selections[] = $selection;
                }
            }
        }

        return $selections;
    }

    public function hasLayerBlock()
    {
        return (bool)$this->getLayerBlock();
    }

    public function getFilterRequestVarsInRenderSequence()
    {
        $layerView = $this->getLayerBlock();
        $requestVars = $layerView->getRenderedFilterRequestVars();

        return $requestVars;
    }

    public function getQueryStringWithDelta($attributeCode, $optionId, $includeQuestionMark = false)
    {
        $paramLookupTable = $this->_getParamLookupTable();

        $isAttributeCodeUsed = false;
        $queryPieces = array();

        foreach ($paramLookupTable as $lookupCode => $values) {
            if (!$lookupCode || $lookupCode == 'id') {
                continue;
            }

            if ($lookupCode == $attributeCode) {
                $isAttributeCodeUsed = true;

                if ($optionId === '___ALL___') {
                    $values = array();
                } else {
                    if ($lookupCode !== 'price' && $lookupCode !== 'cat') {
                        if (isset($values[$optionId])) {
                            unset($values[$optionId]);
                        } else {
                            $values[$optionId] = true;
                        }
                    } else {
                        $isValueSet = isset($values[$optionId]);
                        $values = array();
                        if (!$isValueSet) {
                            $values[$optionId] = true;
                        }
                    }
                }
            }

            if (count($values)) {
                $queryPieces[] = $lookupCode . '=' . implode(',', array_keys($values));
            }
        }

        if ($attributeCode && !$isAttributeCodeUsed && $optionId !== '___ALL___') {
            $queryPieces[] = $attributeCode . '=' . $optionId;
        }

        $val = implode('&amp;', $queryPieces);
        $queryString = str_replace(array('%2C', '%20'), array(',', ' '), $val);
        $val = $includeQuestionMark ? '?' . $queryString : $queryString;

        return trim($val) == '?' ? '' : $val;
    }

    public function isChecked($attributeCode, $optionId = '__ANY__')
    {
        $paramLookupTable = $this->_getParamLookupTable();

        if (!isset($paramLookupTable[$attributeCode])) {
            return false;
        }

        if ($optionId != '__ANY__') {
            return isset($paramLookupTable[$attributeCode][$optionId]);
        } else {
            return count($paramLookupTable[$attributeCode]) > 0;
        }
    }

    public function getOrigItems($block)
    {
        if (!$block instanceof Mage_Catalog_Block_Layer_Filter_Abstract) {
            return array();
        }

        $filterBlockClass = get_class($block);
        $currentRequestCode = Mage::helper('multioptionfilter/filter')
            ->getRequestVarForFilterBlock($block);

        if (!$currentRequestCode) {
            return array();
        }

        $request = Mage::app()->getRequest();
        $originalRequest = clone $request;

        $fullLayer = $block->getLayer();
        $LayerModelClass = get_class($fullLayer);

        $request->setParam($currentRequestCode, '');

        $partialLayer = Mage::getModel('multioptionfilter/proxy');
        $partialLayer->setDelegate(new $LayerModelClass);

        $partialLayer->setOverride(
            'getAggregator', Mage::getSingleton('multioptionfilter/layer_aggregation'));

        $filterBlock = new $filterBlockClass;

        $filterBlock->addData(array(
            'type' => $block->getData('type'),
            'layer' => $partialLayer
        ));

        if ($block->hasData('attribute_model') || method_exists($block, 'getAttributeModel')) {
            $filterBlock->setAttributeModel($block->getAttributeModel());
        }

        $stateFilterItems = $fullLayer->getState()->getFilters();

        $filterModels = array();
        foreach($stateFilterItems as $item) {
            $filter = $item->getFilter();
            $requestVar = $filter->getRequestVar();
            $filterModels[$requestVar] = $filter;
        }
        
        foreach ($filterModels as $requestCode => $filter) {
            if ($requestCode === $currentRequestCode) {
                continue;
            }

            $filter->setLayer($partialLayer);
            $filter->apply($request, $filterBlock);
            $filter->setLayer($fullLayer);
        }

        $partialLayer->apply();

        $items = $filterBlock->init()->getItems();

        Mage::app()->setRequest($originalRequest);

        return $items;
    }

    public function getAttributeCode($block)
    {
        return Mage::helper('multioptionfilter/filter')->getRequestVarForFilterBlock($block);
    }

    protected function _constructFilterItem($attribute, $label, $value, $count)
    {
        $url = $this->getUrl($attribute, $value, false);

        return new Varien_Object(array(
            'label' => $label,
            'value' => $value,
            'count' => $count,
            'url' => $url . $this->getQueryStringWithDelta($attribute, $value, true, false)
        ));
    }

    public function prepareItems($block)
    {
        $items = array();

        if ($originalItems = $this->getOrigItems($block)) {
            $items = $this->_getItemsArray($this->getAttributeCode($block), $originalItems);
        }

        return $items;
    }

    protected function _getItemsArray($attributeCode, $items)
    {
        $itemsByLabel = array();

        foreach ($items as $item) {
            $label = $item->getData('label');

            $itemsByLabel[$label] = $this->_constructFilterItem(
                $attributeCode, $label, $item->getData('value'), $item->getData('count'));
        }

        return $itemsByLabel;
    }

    /**
     * getItems
     *
     * @param $block Mage_Catalog_Block_Layer_Filter_Attribute
     * @return array
     */
    public function getItems($block)
    {
        $attributeCode = $this->getAttributeCode($block);

        if (!isset($this->_items[$attributeCode])) {
            $items = $block->getItems();

            $paramLookupTable = $this->_getParamLookupTable();

            if (isset($paramLookupTable[$this->getAttributeCode($block)])) {
                $items = $this->getOrigItems($block);
            }

            $itemsByLabel = $this->_getItemsArray($this->getAttributeCode($block), $items);

            if ($this->_isAttributeGroupingActive === null) {
                $this->_isAttributeGroupingActive = Mage::helper('core')
                    ->isModuleEnabled('Icommerce_AttributeOptionGroups');
            }

            if ($this->_isAttributeGroupingActive) {
                $attributeCode = $block->getData('attribute_model')->getData('attribute_code');

                if ($model = Mage::getModel('attributeoptiongroups/observer')) {
                    $model->fillOptionItems($itemsByLabel, $attributeCode, Mage::app()->getStore()->getId());
                }
            }

            $this->_items[$attributeCode] = $itemsByLabel;
        }


        return $this->_items[$attributeCode];
    }

    protected function _getRootUrlKey()
    {
        static $categoryUrlKey;

        if (null !== $categoryUrlKey) {
            return $categoryUrlKey;
        }

        $categoryUrlKey = '';

        if ($categoryId = $this->_getConfigValue('multioptionfilter/settings/default_category_id')) {
            $categoryUrlKey = Mage::getResourceModel('catalog/category')
                ->getAttributeRawValue($categoryId, 'url_key', Mage::app()->getStore()->getId());
        }

        return $categoryUrlKey;
    }

    public function getUrl($attributeCode = null, $opt = null)
    {
        static $isCategoryAttributeBinderActive;

        if ($attributeCode && $opt && $isCategoryAttributeBinderActive !== false) {
            if ($isCategoryAttributeBinderActive === null) {
                $isCategoryAttributeBinderActive = Mage::helper('core')
                    ->isModuleEnabled('Icommerce_CategoryAttributeBinder');
            }

            if ($isCategoryAttributeBinderActive) {
                if ($rootUrlKey = $this->_getRootUrlKey()) {
                    $attributes = array();

                    foreach ($this->_getParamLookupTable() as $attributeKey => $options) {
                        if ($attributeKey !== 'price' && $attributeKey !== 'cat') {
                            foreach ($options as $optionKey => $optionVal) {
                                $attributes[$attributeKey] = $optionKey;
                            }
                        }
                    }

                    if (isset($attributes[$attributeCode]) && $opt == $attributes[$attributeCode]) {
                        unset($attributes[$attributeCode]);
                    } else {
                        $attributes[$attributeCode] = $opt;
                    }

                    $url = Mage::helper('categoryattributebinder')->optionValsToUrl(
                        $attributes, null, null, array($rootUrlKey), null, false);

                    if ($url) {
                        return Mage::getBaseUrl() . $url;
                    }
                }
            }
        }

        $url = null;

        if ($category = $this->getCurrentCategory()) {
            $url = $category->getUrl();
        }

        if (!$url) {
            static $currentUrl;

            if (null === $currentUrl) {
                $currentUrl = Mage::helper('core/url')->getCurrentUrl();
                if (($idx = strpos($currentUrl, '?')) !== false) {
                    $currentUrl = substr($currentUrl, 0, $idx);
                }
            }

            $url = $currentUrl;
        }

        Mage::dispatchEvent('ic_mof_get_url_after', array('url' => &$url));

        return $url;
    }

    public function displayAttribute($attributeCode)
    {
        if ($category = $this->getCurrentCategory()) {
            if (!$this->shouldDisplayAttributeFilterForCategory($category, $attributeCode)) {
                return false;
            }
        }

        switch ($attributeCode) {
            case 'price':
                return (bool)$this->_getConfigValue('multioptionfilter/settings/price_filter');
            case 'cat':
                return (bool)$this->_getConfigValue('multioptionfilter/settings/cat_filter');

            default:
                return true;
        }
    }

    public function getCurrentCategory()
    {
        return Mage::registry(Vaimo_MultiOptionFilter_Helper_Registry::CATEGORY);
    }

    /**
     * @param object $category
     * @return bool
     */
    public function shouldSuppressAllFilters($category = null)
    {
        $category = $category ?: $this->getCurrentCategory();

        return (bool)$category->getData('suppress_filters');
    }

    /**
     * @param object $category
     * @param string $attributeCode
     * @return bool
     */
    public function shouldDisplayAttributeFilterForCategory($category, $attributeCode)
    {
        if ($this->shouldSuppressAllFilters($category)) {
            return false;
        }

        if ($categoryId = $category->getEntityId()) {
            if (!isset($this->_excludedAttributes[$categoryId])) {
                if ($exclusionList = $category->getExcludedAttributeFilters()) {
                    $this->_excludedAttributes[$categoryId] = array_flip(explode(',', $exclusionList));
                } else {
                    $this->_excludedAttributes[$categoryId] = array();
                }
            }

            return !isset($this->_excludedAttributes[$categoryId][$attributeCode]);
        }

        return true;
    }

    protected function _getConfigValue($path)
    {
        if (!isset($this->_configValues[$path])) {
            $this->_configValues[$path] = Mage::getStoreConfig($path);
        }

        return $this->_configValues[$path];
    }

    public function getLayerSingleton()
    {
        if (!$layer = Mage::registry(Vaimo_MultiOptionFilter_Helper_Registry::LAYER)) {
            $layer = Mage::getSingleton('catalog/layer');
        }

        return $layer;
    }

    public function isEnterpriseThirdPartSearchEnabled()
    {
        if (!Mage::helper('core')->isModuleEnabled('Enterprise_Enterprise')) {
            return false;
        }

        $searchHelper = Mage::helper('enterprise_search');

        if (!$searchHelper->isThirdPartSearchEngine() || !$searchHelper->isActiveEngine()) {
            return false;
        }

        return true;
    }
}
