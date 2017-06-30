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
 * @package     Vaimo_Menu
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Vaimo_Menu_Model_Catalog_Category_Widget extends Vaimo_Menu_Model_Abstract
{
    protected $_references = array();
    protected $_attributeCodes = null;
    protected $_containersForAttribute = null;
    protected $_usedWidgetAttributeCodes = null;
    protected $_cacheKeyBase = 'vaimo_menu_used_widget_attribute_codes_';

    protected function _construct()
    {
        parent::_construct();
        $this->_init('vaimo_menu/catalog_category_widget');
    }

    protected function _getCacheKey($prefix)
    {
        return $this->_cacheKeyBase . $prefix;
    }

    protected function _isCacheValid($key)
    {
        return Mage::helper('vaimo_menu/cache')->test($key);
    }

    protected function _getWidgetAttributeCodes()
    {
        $attributes = array();
        foreach ($this->getResource()->getWidgetAttributes() as $attribute) {
            $attributes[] = $attribute['attribute_code'];
        }
        return $attributes;
    }

    public function getWidgetAttributeCodes()
    {
        $cacheKey = $this->_getCacheKey('attributes');
        if (Mage::app()->useCache(Vaimo_Menu_Model_Navigation::CACHE_FLAG_NAME) && $this->_attributeCodes === null) {
            if ($serializedData = Mage::app()->loadCache($cacheKey)) {
                if ($data = unserialize($serializedData)) {
                    $this->_attributeCodes = $data;
                }
            }
        }

        if ($this->_attributeCodes === null || !$this->_isCacheValid($cacheKey)) {
            $data = $this->_getWidgetAttributeCodes();
            $tags = array(Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG);
            Mage::app()->saveCache(serialize($data), $cacheKey, $tags, 0);
            $this->_attributeCodes = $data;
        }

        return $this->_attributeCodes;
    }

    public function getLayoutNameForWidget($handle, $widgetId)
    {
        if (!isset($this->_references[$handle])) {
            $this->_references[$handle] = $this->getResource()->getWidgetBlockInfoForBlockReferences(array($handle));
        }

        if (isset($this->_references[$handle][$widgetId])) {
            return $this->_references[$handle][$widgetId]['name'];
        }

        return false;
    }

    protected function _getWidgetContainerBlocksWithAttributeReferences($storeId)
    {
        $analyser = $this->_factory->getSingleton('vaimo_menu/layout_analyser', array('factory' => $this->_factory));
        $containers = $analyser->getWidgetContainersForHandle('default', $storeId, '[@attributes]');

        return $containers;
    }

    protected function _getWidgetContainersForAttributeCode($storeId)
    {
        $blocks = $this->_getWidgetContainerBlocksWithAttributeReferences($storeId);
        $containers = array();

        foreach ($blocks as $name => $block) {
            $attributeCodes = explode(',', $block->label->getAttribute('attributes'));

            foreach ($attributeCodes as  $attributeCode) {
                if (!isset($containers[$attributeCode])) {
                    $containers[$attributeCode] = array();
                }

                $containers[$attributeCode][] = $name;
            }
        }

        return $containers;
    }

    public function getWidgetContainersForAttributeCode($attributeCode, $storeId)
    {
        $cacheKey = $this->_getCacheKey('containers_' . $storeId);
        if ($this->_containersForAttribute === null) {
            if ($serializedData = Mage::app()->loadCache($cacheKey)) {
                if ($data = unserialize($serializedData)) {
                    $this->_containersForAttribute = $data;
                }
            }
        }

        if ($this->_containersForAttribute === null || !$this->_isCacheValid($cacheKey)) {
            $data = $this->_getWidgetContainersForAttributeCode($storeId);

            $tags = array(
                Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG
            );

            Mage::app()->saveCache(serialize($data), $cacheKey, $tags, 0);
            $this->_containersForAttribute = $data;
        }

        if (!isset($this->_containersForAttribute[$attributeCode])) {
            return array();
        }

        return $this->_containersForAttribute[$attributeCode];
    }

    protected function _getUsedWidgetAttributeCodes($storeId)
    {
        $attributeCodes = $this->getWidgetAttributeCodes();
        $targetedAttributes = array();

        if ($attributeCodes) {
            $containers = array();
            $attributesPerContainer = array();

            foreach ($attributeCodes as $code) {
                $containersForAttribute = $this->getWidgetContainersForAttributeCode($code, $storeId);
                $containers = array_merge($containers, $containersForAttribute);

                foreach ($containers as $container) {
                    if (!isset($attributesPerContainer[$container])) {
                        $attributesPerContainer[$container] = array();
                        $attributesPerContainer[$container][] = $code;
                    }
                }
            }

            if ($containers) {
                $blocks = $this->getResource()->getWidgetBlockInfoForBlockReferences(array_unique($containers));

                $blockNames = array_map(function($item) {
                    return $item['reference'];
                }, $blocks);

                foreach ($blockNames as $blockName) {
                    $targetedAttributes = array_merge($targetedAttributes, $attributesPerContainer[$blockName]);
                }
            }
        }

        return array_unique($targetedAttributes);
    }

    public function getUsedWidgetAttributeCodes($storeId)
    {
        $cacheKey = $this->_getCacheKey($storeId);
        if (!isset($this->_usedWidgetAttributeCodes[$storeId])) {
            if ($serializedData = Mage::app()->loadCache($cacheKey)) {
                if ($data = unserialize($serializedData)) {
                    $this->_usedWidgetAttributeCodes[$storeId] = $data;
                }
            }
        }

        if (!isset($this->_usedWidgetAttributeCodes[$storeId]) || !$this->_isCacheValid($cacheKey)) {
            $data = $this->_getUsedWidgetAttributeCodes($storeId);
            $tags = array(
                Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG,
                Vaimo_Menu_Model_Navigation::CACHE_TAG
            );
            Mage::app()->saveCache(serialize($data), $cacheKey, $tags);
            $this->_usedWidgetAttributeCodes[$storeId] = $data;
        }

        return $this->_usedWidgetAttributeCodes[$storeId];
    }
}