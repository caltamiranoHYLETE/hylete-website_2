<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @package     Vaimo_BlockAjax
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Vaimo_BlockAjax_Model_Layout_Analyzer
{
    const XPATH_AJAX_NODES = '//%s[text()="%s"]';

    protected $_cacheTags = array(Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG);
    protected $_ajaxBlocksByGroup = array();

    public function getBlocksByAjaxGroup(Mage_Core_Model_Layout $layout, $groupName)
    {
        $allBlocks = $layout->getAllBlocks();
        $ajaxBlocks = array();

        if (!$allBlocks) {
            Mage::throwException('Ajax blocks called before layout has been loaded');
        }

        $targetedBlocks = $this->_getBlockReferencesForAjaxGroup($layout, $groupName);
        $allBlocksWithIndexInLayout = array_flip(array_keys($allBlocks));

        foreach($targetedBlocks as $blockReference => $targetData) {
            if ($blockReference && isset($allBlocksWithIndexInLayout[$blockReference])) {
                $block = $allBlocks[$blockReference];

                if ($block->getData($groupName . '_ajax_disabled')) {
                    continue;
                }

                $ajaxBlocks[$allBlocksWithIndexInLayout[$blockReference]] = array(
                    'target' => $targetData,
                    'block' => $block
                );
            }
        }

        return $ajaxBlocks;
    }

    protected function _getBlockReferencesForAjaxGroup($layout, $groupName)
    {
        $cacheKey = $this->_getCacheKey($groupName);

        if ($this->_shouldLoadCache($cacheKey)) {
            if ($serializedData = Mage::app()->loadCache($cacheKey)) {
                $this->_ajaxBlocksByGroup[$groupName] = unserialize($serializedData);
            }
        }

        if ($this->_shouldUpdateCache($cacheKey)) {
            $layoutXml = $layout->getNode();

            if (!$layoutXml) {
                Mage::throwException('Layout XML loading failed');
            }

            $xpathSelector = sprintf(self::XPATH_AJAX_NODES, Vaimo_BlockAjax_Helper_Data::AJAX_TAG_NAME, $groupName);
            $ajaxNodes = $layoutXml->xpath($xpathSelector);
            $ajaxBlocksConfig = array();

            /**
             * Detect enabled/disabled nodes
             */
            $disabledBlocks = array();
            foreach($ajaxNodes as $node) {
                $parent = $node->xpath('..');
                $parentName = $parent[0]->getAttribute('name');
                $disabledFlag = $node->getAttribute('disabled');

                if ($disabledFlag == 'true' || $disabledFlag == 'false') {
                    $disabledBlocks[$parentName] = filter_var($disabledFlag, FILTER_VALIDATE_BOOLEAN);
                }
            }

            foreach($ajaxNodes as $node) {
                $parent = $node->xpath('..');
                $parentName = $parent[0]->getAttribute('name');

                if (isset($disabledBlocks[$parentName])) {
                    continue;
                }

                $selector = $node->getAttribute(Vaimo_BlockAjax_Helper_Data::AJAX_REQUEST_CONTAINER_SELECTOR_ATTRIBUTE);
                $isScript = $node->getAttribute(Vaimo_BlockAjax_Helper_Data::AJAX_REQUEST_CONTAINER_JAVASCRIPT_ATTRIBUTE);

                $ajaxBlocksConfig[$parentName] = array(
                    'selector' => $selector,
                    'script' => (int)(bool)$isScript
                );
            }

            Mage::app()->saveCache(serialize($ajaxBlocksConfig), $cacheKey, $this->_cacheTags);

            $this->_ajaxBlocksByGroup[$groupName] = $ajaxBlocksConfig;
        }

        return $this->_ajaxBlocksByGroup[$groupName];
    }

    protected function _getCacheKey($prefix)
    {
        return $prefix . '_' . 'AJAX_BLOCKS' . '_' . Mage::helper('blockajax/cache')->getCacheKey();
    }

    protected function _shouldLoadCache($cacheKey)
    {
        return !isset($this->_ajaxBlocksByGroup[$cacheKey]) && Mage::helper('blockajax/cache')->test($cacheKey, 'layout');
    }

    protected function _shouldUpdateCache($cacheKey)
    {
        return !isset($this->_ajaxBlocksByGroup[$cacheKey]) || !Mage::helper('blockajax/cache')->test($cacheKey, 'layout');
    }
}