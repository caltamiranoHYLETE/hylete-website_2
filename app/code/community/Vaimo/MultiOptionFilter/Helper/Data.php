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
 * @package     Vaimo_MultiOptionFilter
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Vaimo_MultiOptionFilter_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @var Vaimo_MultiOptionFilter_Helper_App
     */
    protected $_appHelper;

    /**
     * @var Vaimo_MultiOptionFilter_Helper_Layout
     */
    protected $_layoutHelper;

    /**
     * @var Vaimo_MultiOptionFilter_Helper_Filter
     */
    protected $_filterHelper;

    /**
     * @var Vaimo_MultiOptionFilter_Helper_Context
     */
    protected $_contextHelper;

    /**
     * @var Vaimo_MultiOptionFilter_Model_Filter_OptionsGenerator
     */
    protected $_filterOptionsGenerator;

    /**
     * @var Vaimo_MultiOptionFilter_Model_Filter_ItemsRetriever
     */
    protected $_filterItemsRetriever;

    /**
     * @var array
     */
    protected $_excludedAttributes = array();

    /**
     * @var null|array
     */
    protected $_filterItems;

    /**
     * @deprecated Used for old external module dependencies
     * @var array
     */
    protected $_externalHandlerInstances;

    public function __construct()
    {
        $this->_appHelper = Mage::helper('multioptionfilter/app');
        $this->_layoutHelper = Mage::helper('multioptionfilter/layout');
        $this->_filterHelper = Mage::helper('multioptionfilter/filter');
        $this->_contextHelper = Mage::helper('multioptionfilter/context');
        $this->_filterOptionsGenerator = Mage::getSingleton('multioptionfilter/filter_optionsGenerator');
        $this->_filterItemsRetriever = Mage::getSingleton('multioptionfilter/filter_itemsRetriever');

        $this->_externalHandlerInstances = Mage::getSingleton('multioptionfilter/factory')->createInstances(array(
            'Icommerce_AttributeOptionGroups' => 'attributeoptiongroups/observer'
        ));
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
        return $this->_layoutHelper->getLayerBlocks(Mage::app()->getLayout(), $createIfNotFound);
    }

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
            foreach (array_values($items) as $index => $item) {
                $value = $item->getValue();

                if (!$this->isChecked($selection['code'], $value)) {
                    continue;
                }

                $selection['selected'][$value] = $index;
            }
        }

        if (!$selection['selected']) {
            return false;
        }

        return $selection;
    }

    public function getAllSelectedLayerOptions($onlyRendered = false)
    {
        $layerView = $this->getLayerBlock();

        $selections = array();
        $filterBlocks = $layerView->getChild();

        foreach ($filterBlocks as $filterBlock) {
            if ($onlyRendered && !$filterBlock->getIsRendered()) {
                continue;
            }

            if (!$selection = $this->getSelectedLayerOptions($filterBlock)) {
                continue;
            }

            $selections[] = $selection;
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

    public function isChecked($attributeCode, $optionId = '__ANY__')
    {
        $paramLookupTable = $this->_appHelper->getRequestedState();

        if (!isset($paramLookupTable[$attributeCode])) {
            return false;
        }

        if ($optionId != '__ANY__') {
            return isset($paramLookupTable[$attributeCode][$optionId]);
        } else {
            return count($paramLookupTable[$attributeCode]) > 0;
        }
    }

    public function getAttributeCode($block)
    {
        return $this->_filterHelper->getRequestVarForFilterBlock($block);
    }

    /**
     * @param $block Mage_Catalog_Block_Layer_Filter_Abstract
     * @return array
     */
    public function getItems($block)
    {
        $attributeCode = $this->getAttributeCode($block);

        if (!isset($this->_filterItems[$attributeCode])) {
            $items = $block->getItems();

            $paramLookupTable = $this->_appHelper->getRequestedState();

            if (isset($paramLookupTable[$this->getAttributeCode($block)])) {
                $items = $this->getOriginalItems($block);
            }

            $itemsByLabel = $this->_filterOptionsGenerator->generate(
                $this->getAttributeCode($block),
                $items
            );

            foreach ($this->_externalHandlerInstances as $instance) {
                $itemsByLabel = $instance->updateItems($block, $itemsByLabel);
            }

            $this->_filterItems[$attributeCode] = $itemsByLabel;
        }


        return $this->_filterItems[$attributeCode];
    }

    /**
     * @param $block Mage_Catalog_Block_Layer_Filter_Abstract
     * @return array
     */
    public function getOriginalItems($block)
    {
        if (!$block instanceof Mage_Catalog_Block_Layer_Filter_Abstract) {
            return array();
        }

        if (!$requestCode = $this->_filterHelper->getRequestVarForFilterBlock($block)) {
            return array();
        }

        return $this->_contextHelper->withModifierRequest(
            array($requestCode => ''),
            $this->_filterItemsRetriever,
            array($block, $requestCode)
        );
    }

    public function getUrl($attributeCode = null, $optionId = null)
    {
        return $this->_filterHelper->getOptionUrl($attributeCode, $optionId);
    }

    public function displayAttribute($attributeCode)
    {
        if (!$attributeCode) {
            return false;
        }

        if ($category = $this->getCurrentCategory()) {
            if (!$this->shouldDisplayAttributeFilterForCategory($category, $attributeCode)) {
                return false;
            }
        }

        switch ($attributeCode) {
            case 'price':
                return (bool)Mage::getStoreConfig('multioptionfilter/settings/price_filter');
            case 'cat':
                return (bool)Mage::getStoreConfig('multioptionfilter/settings/cat_filter');
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

    public function getLayerSingleton()
    {
        if (!$layer = Mage::registry(Vaimo_MultiOptionFilter_Helper_Registry::LAYER)) {
            $layer = Mage::getSingleton('catalog/layer');
        }

        return $layer;
    }

    public function isEnterpriseThirdPartySearchEnabled()
    {
        if (!Mage::helper('core')->isModuleEnabled('Enterprise_Enterprise')) {
            return false;
        }

        /** @var Enterprise_Search_Helper_Data $searchHelper */
        $searchHelper = Mage::helper('enterprise_search');

        return $searchHelper->isThirdPartSearchEngine() && $searchHelper->isActiveEngine();
    }
}
