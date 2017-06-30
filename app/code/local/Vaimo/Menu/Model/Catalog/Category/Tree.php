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

/**
 * Class Vaimo_Menu_Model_Catalog_Category_Tree
 *
 * @method setDataCacheLifetime(string $name)
 */
class Vaimo_Menu_Model_Catalog_Category_Tree extends Vaimo_Menu_Model_Abstract
{
    const CACHE_LIFETIME = Vaimo_Menu_Helper_Cache::CACHE_LIFETIME;

    protected $_tree = null;
    protected $_lastLoadedCacheKey = null;
    protected $_lastLoadedCacheData = array('attributes' => array(), 'groups' => array());
    protected $_instanceCacheKey = null;
    protected $_dataCacheKey;
    protected $_cacheTags;
    protected $_helper;
    protected $_defaultAttributes = array(
        'entity_id', 'name', 'path', 'level', 'position', 'include_in_menu', 'is_active', 'url_key', 'children_count',
        'parent_id', 'column_breakpoint', 'menu_group'
    );

    protected function _construct()
    {
        parent::_construct();
        $this->_init('vaimo_menu/catalog_category_tree');

        $this->_dataCacheKey = Vaimo_Menu_Model_Navigation::CACHE_FLAG_NAME . '_' . Mage::app()->getStore()->getId();
        $this->_cacheTags = Mage::helper('vaimo_menu/cache')->getDataCacheTags();
    }

    protected function _prepareCategoryEntityData($category)
    {
        $data = $category->getData();
        $categoryUrl = strtok($category->getUrl(), '?');
        $data['url'] = $categoryUrl;

        if ($data['url'] && (!isset($data['url_key']) || !$data['url_key'])) {
            try {
                $data['url_key'] = trim(substr($categoryUrl, strrpos(trim($categoryUrl, '/'), '/')), '/');
            } catch (Exception $e) {}
        }

        if (isset($data['url_path']) && $data['url_path'] && (!isset($data['url_key']) || !$data['url_key'])) {
            $data['url_key'] = $data['url_path'];
        }

        return $data;
    }

    protected function _getCategories($attributes, $ids, $storeId)
    {
        $categories = array();

        /**
         * boolean refers to direction. true => asc | false => desc
         */
        $order = array('position' => true);

        $codes = Mage::getSingleton('vaimo_menu/catalog_category_widget')->getUsedWidgetAttributeCodes($storeId);
        $attributes = array_unique(array_merge($attributes, $codes));

        $categoryCollection = $this->getResource()->getCategoryCollection($storeId, $attributes, $ids);

        $transport = new Varien_Object(array('collection' => $categoryCollection, 'order' => $order));
        Mage::dispatchEvent('vaimo_menu_load_categories_before', array('transport' => $transport));
        $order = $transport->getOrder();

        $this->getResource()->appendOrderBy($categoryCollection, $order);

        foreach ($categoryCollection as $entityId => $category) {
            if ($category->hasIsActive() && !$category->getIsActive()) {
                continue;
            }

            $categories[$entityId] = $this->_prepareCategoryEntityData($category);
        }

        $transport = new Varien_Object(array('categories' => $categories));
        Mage::dispatchEvent('vaimo_menu_load_categories_after', array('transport' => $transport));
        $categories = $transport->getCategories();

        return $categories;
    }

    protected function _shouldLoadCache($cacheKey)
    {
        return $this->_tree == null && Mage::helper('vaimo_menu/cache')->test($cacheKey);
    }

    protected function _shouldUpdateCache($cacheKey)
    {
        return $this->_tree == null || !Mage::helper('vaimo_menu/cache')->test($cacheKey);
    }

    protected function _getCacheKey($lifetime)
    {
        return $this->_dataCacheKey .
        '_' . Mage::getSingleton('customer/session')->getCustomerGroupId() .
        '_' . $lifetime;
    }

    protected function _prepareAttributes($attributes)
    {
        if (!$attributes) {
            $attributes = array();
        }

        if (is_string($attributes)) {
            $attributes = explode(',', $attributes);
        }

        if (is_object($attributes)) {
            $attributes = array();
        }
        return $attributes;
    }

    protected function _removeDuplicateAndInvalidAttributes($attributes)
    {
        return array_unique(array_filter($attributes));
    }

    public function getCategoryTree($attributes = array(), $ids = null, $storeId = null, $cacheLifetime = self::CACHE_LIFETIME)
    {
        $cacheKey = $this->_getCacheKey($cacheLifetime);
        $attributes = $this->_prepareAttributes($attributes);

        if (array_diff($attributes, $this->_lastLoadedCacheData['attributes'])) {
            $this->_tree = null;
        }

        if ($this->_shouldLoadCache($cacheKey)) {
            if ($serializedData = Mage::app()->loadCache($cacheKey)) {
                if ($treeData = unserialize($serializedData)) {
                    if (array_diff($attributes, $treeData['attributes'])) {
                        $attributes = array_merge($attributes, $treeData['attributes']);
                        $this->_tree = null;
                    } else {
                        $this->_tree = $treeData['tree'];
                    }

                    $this->_lastLoadedCacheData = $treeData;
                }
            }
        }

        if ($this->_shouldUpdateCache($cacheKey)) {
            if (!$storeId) {
                $storeId = Mage::app()->getStore()->getId();
            }

            $attributes = $this->_removeDuplicateAndInvalidAttributes(array_merge($attributes, $this->_defaultAttributes));
            $categories = $this->_getCategories($attributes, $ids, $storeId);

            $this->_tree = Mage::helper('vaimo_menu/tree')->flatArrayToArrayTree($categories);

            $treeData = array('tree' => $this->_tree, 'attributes' => $attributes);
            Mage::app()->saveCache(serialize($treeData), $cacheKey, $this->_cacheTags, $cacheLifetime);
            $this->_lastLoadedCacheData = $treeData;
        }

        return Mage::helper('vaimo_menu/tree')->arrayTreeToObjectTree($this->_tree);
    }
}
