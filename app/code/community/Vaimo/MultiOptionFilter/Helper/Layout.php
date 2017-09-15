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

class Vaimo_MultiOptionFilter_Helper_Layout extends Mage_Core_Helper_Abstract
{
    /**
     * @var array
     */
    protected $_layerNames = array();

    /**
     * @var object
     */
    protected $_dummyView;

    public function getLayerBlocks($layout, $createIfNotFound = false)
    {
        $action = Mage::app()->getFrontController()->getAction();
        $actionKey = $action->getFullActionName();

        if ($handles = $layout->getUpdate()->getHandles()) {
            $actionKey = md5(implode('::', $handles));
        }

        $cacheId = $actionKey
            . '_navigation_layer_layout_names_'
            . (int)$action->getRequest()->isXmlHttpRequest();

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

                $this->_saveCache(
                    serialize($navLayerNames),
                    $cacheId,
                    array('LAYOUT_GENERAL_CACHE_TAG'),
                    86400
                );

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

                    Mage::helper('multioptionfilter/registry')->set(
                        Vaimo_MultiOptionFilter_Helper_Registry::LAYER,
                        $layer
                    );
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

    public function addUpdateHandles($layout, $handles)
    {
        $update = $layout->getUpdate();

        foreach ($handles as $handle) {
            $update->addHandle($handle);
        }
    }
}