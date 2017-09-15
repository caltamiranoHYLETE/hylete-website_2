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
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_MultiOptionFilter
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Vaimo_MultiOptionFilter_Helper_Filter extends Mage_Core_Helper_Abstract
{
    const TARGETED_OPTION_ITEM = '_current_option_item';

    /**
     * @var Vaimo_MultiOptionFilter_Helper_App
     */
    protected $_appHelper;

    /**
     * @var Vaimo_MultiOptionFilter_Model_Cache_Layer
     */
    protected $_cacheLayer;

    /**
     * @var Vaimo_MultiOptionFilter_Model_Layer_FilterInfo
     */
    protected $_layerFilterInfo;

    /**
     * @deprecated Used for old external module dependencies
     * @var array
     */
    protected $_externalHandlerInstances;

    public function __construct()
    {
        $this->_appHelper = Mage::helper('multioptionfilter/app');
        $this->_cacheLayer = Mage::getSingleton('multioptionfilter/cache_layer');
        $this->_layerFilterInfo = Mage::getSingleton('multioptionfilter/layer_filterInfo');

        $this->_externalHandlerInstances = Mage::getSingleton('multioptionfilter/factory')
            ->createInstances(array(
                'Icommerce_CategoryAttributeBinder' => 'multioptionfilter/external_attributeBinder'
            ));
    }

    public function getRequestVarForFilterBlock($block)
    {
        return $this->_cacheLayer->get(
            'vmof_request_var_' . $block->getBlockAlias(),
            'layout',
            array(Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG),
            array($this->_layerFilterInfo, 'getRequestVarForFilterBlock', $block)
        );
    }

    public function getFilterBlockClone($block)
    {
        $layer = $block->getLayer();

        $layerModelClass = get_class($layer);
        $filterBlockClass = get_class($block);

        $partialLayer = Mage::getModel('multioptionfilter/runtime_proxies_modelProxy');
        $partialLayer->setDelegate(new $layerModelClass());

        $partialLayer->setOverride(
            'getAggregator',
            Mage::getSingleton('multioptionfilter/layer_aggregation')
        );

        $clone = new $filterBlockClass();

        $clone->addData(array(
            'type' => $block->getData('type'),
            'layer' => $partialLayer
        ));

        if ($block->hasData('attribute_model') || method_exists($block, 'getAttributeModel')) {
            $clone->setAttributeModel($block->getAttributeModel());
        }

        return $clone;
    }

    public function getOptionUrl($attributeCode, $optionId)
    {
        foreach ($this->_externalHandlerInstances as $instance) {
            if ($url = $instance->getOptionUrl($attributeCode, $optionId)) {
                return $url;
            }
        }

        $url = null;

        if ($category = Mage::registry(Vaimo_MultiOptionFilter_Helper_Registry::CATEGORY)) {
            $url = $category->getUrl();
        }

        if (!$url) {
            static $currentUrl;

            if ($currentUrl === null) {
                $currentUrl = Mage::helper('core/url')->getCurrentUrl();

                $querySeparatorIndex = strpos($currentUrl, '?');

                if ($querySeparatorIndex !== false) {
                    $currentUrl = substr($currentUrl, 0, $querySeparatorIndex);
                }
            }

            $url = $currentUrl;
        }

        Mage::dispatchEvent('ic_mof_get_url_after', array(
            'url' => &$url
        ));

        return $url;
    }

    public function getQueryString($attributeCode, $optionId, $includeQuerySeparator = false)
    {
        $paramLookupTable = $this->_appHelper->getRequestedState();

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
                } else if ($lookupCode !== 'price' && $lookupCode !== 'cat') {
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

            if (count($values)) {
                $queryPieces[] = $lookupCode . '=' . implode(',', array_keys($values));
            }
        }

        if ($attributeCode && !$isAttributeCodeUsed && $optionId !== '___ALL___') {
            $queryPieces[] = $attributeCode . '=' . $optionId;
        }

        $val = implode('&amp;', $queryPieces);
        $queryString = str_replace(array('%2C', '%20'), array(',', ' '), $val);
        $val = $includeQuerySeparator ? '?' . $queryString : $queryString;

        return trim($val) == '?' ? '' : $val;
    }
}
