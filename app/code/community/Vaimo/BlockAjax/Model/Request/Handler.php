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
 * @package     Vaimo_BlockAjax
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 */

class Vaimo_BlockAjax_Model_Request_Handler
{
    protected $_selectorsByName = false;
    protected $_cacheTags = array(Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG);
    protected $_pageCacheContainer = 'BLOCKAJAX_REQUESTID container=Vaimo_BlockAjax_Model_PageCache_Container_Request';
    protected $_requestId = false;

    protected function _removeNestedAjaxBlocks($configuration)
    {
        $names = array();

        foreach ($configuration as $item) {
            $block = $item['block'];
            $names[$block->getNameInLayout()] = true;
        }

        $_configuration = array();
        foreach ($configuration as $item) {
            $block = $item['block'];
            while ($parent = $block->getParentBlock()) {
                if (isset($names[$parent->getNameInLayout()])) {
                    $item = false;
                    break;
                }

                $block = $parent;
            }

            if ($item) {
                $_configuration[] = $item;
            }
        }

        return $_configuration;
    }

    protected function _getBlockHtmlUpdatesForGroup($groupName)
    {
        $layoutAnalyzer = Mage::getSingleton('blockajax/layout_analyzer');
        $ajaxReplyBlockConfiguration = $layoutAnalyzer->getBlocksByAjaxGroup(Mage::app()->getLayout(), $groupName);
        $ajaxReplyBlockConfiguration = $this->_removeNestedAjaxBlocks($ajaxReplyBlockConfiguration);

        return $this->_getBlocksHtmlResponseData($ajaxReplyBlockConfiguration);
    }

    public function getResponse($request)
    {
        /**
         * Remove request_id and request token from request variables so that system would not end up inserting them somewhere
         */
        $requestId = $this->_extractParamFromRequest($request, Vaimo_BlockAjax_Helper_Data::AJAX_REQUEST_ID_PARAM);
        $requestToken = $this->_extractParamFromRequest($request, Vaimo_BlockAjax_Helper_Data::AJAX_REQUEST_PARAM);

        $blocksData = $this->_getBlockHtmlUpdatesForGroup($requestToken);

        $blocksTotalSize = 0;
        foreach ($blocksData as $block) {
            $blocksTotalSize += strlen($block['html']);
        }

        $responseData = array(
            'blocks' => $blocksData,
            'size' => $blocksTotalSize,
            Vaimo_BlockAjax_Helper_Data::AJAX_REQUEST_PARAM => $requestToken,
            Vaimo_BlockAjax_Helper_Data::AJAX_REQUEST_ID_PARAM => $requestId
        );

        if (Mage::helper('blockajax')->isEnterprisePageCache()) {
            /**
             * This is needed for the FPC record to take a note about the request-token
             */
            $request->setQuery(Vaimo_BlockAjax_Helper_Data::AJAX_REQUEST_PARAM, $requestToken);
        }

        if (!$requestId) {
            return $responseData;
        }

        if (Mage::helper('blockajax')->isEnterprisePageCache()) {


            $placeholder = Mage::getModel('enterprise_pagecache/container_placeholder', $this->_pageCacheContainer);
            $responseData[Vaimo_BlockAjax_Helper_Data::AJAX_REQUEST_ID_PARAM] = $placeholder->getReplacer();
        } else {
            $responseData[Vaimo_BlockAjax_Helper_Data::AJAX_REQUEST_ID_PARAM] = $this->_requestId;
        }

        $this->_requestId = $requestId;

        return $responseData;
    }

    public function encodeWithLatestRequestId($responseData)
    {
        $this->addResponseId($responseData);

        return Vaimo_BlockAjax_Json_Encoder::encode($responseData);
    }

    public function addResponseId(&$responseData)
    {
        if (!$this->_requestId) {
            return;
        }

        $responseData[Vaimo_BlockAjax_Helper_Data::AJAX_REQUEST_ID_PARAM] = $this->_requestId;
    }

    protected function _extractParamFromRequest($request, $name)
    {
        $value = $request->getParam($name);

        /**
         * There seems to be no other way to make sure that the param will not end up in generated URLs
         */
        unset($_GET[$name]);

        return $value;
    }

    protected function _getBlocksHtmlResponseData($ajaxReplyBlockConfiguration)
    {
        $blocksData = array();
        foreach ($ajaxReplyBlockConfiguration as $configuration) {
            $block = $configuration['block'];

            $blockHtml = $block->toHtml();

            $blocksData[$block->getNameInLayout()] = array(
                'target' => $configuration['target'],
                'html' => $blockHtml
            );
        }

        return $this->_validateContainerSelectors($blocksData);
    }

    protected function _validateContainerSelectors($blocksData)
    {
        $cacheKey = $this->_getCacheKey();

        if ($this->_shouldLoadCache($cacheKey)) {
            if ($serializedData = Mage::app()->loadCache($cacheKey)) {
                $this->_selectorsByName = unserialize($serializedData);
            }
        }

        $blocksDataWithValidatedSelectors = array();
        foreach ($blocksData as $blockName => $blockData) {
            $targetData = &$blockData['target'];

            if (!$targetData['selector'] && !$targetData['script']) {
                $targetData['selector'] = $this->_resolveSelectorFromHtml($blockName, $blockData['html']);
            }

            if ($targetData['selector'] || $targetData['script']) {
                $blockData['target'] = array_filter($blockData['target']);
                $blocksDataWithValidatedSelectors[] = $blockData;
            }

            unset($targetData);
            unset($blockData);
        }

        if ($this->_shouldUpdateCache($cacheKey)) {
            Mage::app()->saveCache(serialize($this->_selectorsByName), $cacheKey, $this->_cacheTags);
        }

        return $blocksDataWithValidatedSelectors;
    }

    protected function _resolveSelectorFromHtml($blockName, $html)
    {
        if (!$html) {
            $this->_selectorsByName[$blockName] = false;
        }

        if (!isset($this->_selectorsByName[$blockName])) {
            /**
             * This is done due to the fact that DomDocument does not support inline scripts and will halt when
             * script that is hit.
             */
            $html = str_replace(array('<script', '</script>'), array('<__script', '</__script>'), $html);

            $bodyNode = Mage::helper('blockajax/dom')->createNodeTreeFromHtml($html);

            $this->_selectorsByName[$blockName] = false;

            if ($bodyNode) {
                try {
                    foreach($bodyNode->childNodes as $firstContentNode) {
                        if ($firstContentNode->tagName == '__script') {
                            continue;
                        }

                        if ($selector = $firstContentNode->getAttribute('id')) {
                            $this->_selectorsByName[$blockName] = '#' . $selector;
                        } elseif ($selector = $firstContentNode->getAttribute('class')) {
                            $classes = array_map(function($class) {
                                return '.' . $class;
                            }, explode(' ', $selector));
                            $this->_selectorsByName[$blockName] = implode('', $classes);
                        }

                        break;
                    }
                } catch (Exception $e) {
                    $this->_selectorsByName[$blockName] = false;
                }
            }
        }

        return $this->_selectorsByName[$blockName];
    }

    protected function _getCacheKey()
    {
        return 'AJAX_BLOCK_CONTAINER_SELECTORS_' . Mage::helper('blockajax/cache')->getCacheKey();
    }

    protected function _shouldLoadCache($cacheKey)
    {
        return !$this->_selectorsByName && Mage::helper('blockajax/cache')->test($cacheKey, 'layout');
    }

    protected function _shouldUpdateCache($cacheKey)
    {
        return !$this->_selectorsByName || !Mage::helper('blockajax/cache')->test($cacheKey, 'layout');
    }
}