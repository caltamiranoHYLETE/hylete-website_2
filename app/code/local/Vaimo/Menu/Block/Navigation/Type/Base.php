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
 * Class Vaimo_Menu_Block_Navigation_Type_Base
 *
 * @method string getCurrentGroup()
 * @method object setCurrentGroup(string $groupName)
 * @method int getCurrentLevel()
 * @method array getExtraAttributes();
 * @method object setExtraAttributes(string $attributes);
 * @method object setIterationLevel(int $level);
 * @method int getIterationLevel();
 * @method setStartLevel(int $level)
 * @method object setDisplayLevels(int $amountOfLevelsToDisplay)
 * @method int getDisplayLevels()
 * @method object setType(string $type)
 * @method string getType()
 * @method object setDataCacheLifetime(int $lifeTimeInSeconds)
 * @method object getCategory()
 * @method object setCategory(Vaimo_Menu_Item $category)
 * @method int getCategoryId()
 * @method object setCategoryId(int $categoryId)
 * @method int getCustomRootId()
 * @method object setCustomRootId()
 */
class Vaimo_Menu_Block_Navigation_Type_Base extends Mage_Core_Block_Template
{
    protected $_allowGroups = Vaimo_Menu_Model_Group::ENABLED;
    protected $_allowWidgets = true;
    protected $_groupTemplates = array();
    protected $_menuNameInLayout = null;
    protected $_typeClasses = array();
    protected $_templateFiles = array();
    protected $_typeConfig = array();
    protected $_templates = array();
    protected $_childBlocks = array();
    protected $_menuItemTree;
    protected $_blockCacheLifetime;
    protected $_dataCacheKey;
    protected $_restrictedCategoryIds = null;
    protected $_storeId;
    protected $_currentCategoryKey;
    protected $_cacheTags;
    protected $_htmlPerCategoryId = array();
    protected $_currentCategoryPath;

    /**
     * Backwards compatibility: Only keeping this to support older templates
     */
    protected $_categoryHelper = null;

    public function addExtraAttributes($attributes)
    {
        $_attributes = explode(',', $this->getExtraAttributes() . ',' . $attributes);
        $this->setExtraAttributes(implode(',', array_filter($_attributes)));
    }

    protected function _construct()
    {
        $this->_cacheTags = Mage::helper('vaimo_menu/cache')->getDataCacheTags();
        $this->_storeId = Mage::app()->getStore()->getId();

        $this->setDataCacheKey('default');

        $this->setLinkClass('menu-link');

        $this->addData(array(
            'cache_lifetime' => false,
            'cache_tags' => $this->_cacheTags
        ));

        $this->_categoryHelper = Mage::helper('catalog/category');

        /**
         * Possibility to override type configuration. Mainly used by tests.
         */
        if ($this->hasData('_type_configuration')) {
            $configuration = $this->getData('_type_configuration');
            $this->_typeConfig = $configuration;
        }
    }

    protected function _getMenuItemTree($tree)
    {
        $i = 1;
        $parentId = false;
        $useGroups = $this->_allowGroups;

        Mage::helper('vaimo_menu/tree')->treeWalkStrict($tree, function($item, $parent) use (&$parentId, &$i, $useGroups) {
            /**
             * Calculate the hierarchy
             */
            $hierarchy = $parent['hierarchy'] ? $parent['hierarchy'] : '';
            if ($parent['entity_id'] !== $parentId) {
                $parentId = $parent['entity_id'];
                $i = 1;
            }

            $item->setHierarchy(($hierarchy ? ($hierarchy . '-') : '') . $i++);

            $groupName = $item->getDataSetDefault('menu_group', Vaimo_Menu_Model_Group::DEFAULT_GROUP);
            if ($useGroups != Vaimo_Menu_Model_Group::DISABLED && $parent) {
                if (($map = $parent->getGroupMap()) === null) {
                    $map = array();
                }

                if (!isset($map[$groupName])) {
                    $map[$groupName] = $parent->getChildCount();
                    $group = $parent->addPseudoChild();
                    $parent->setGroupMap($map);
                } else {
                    $group = $parent->getChild($map[$groupName]);
                }

                $group->addChild($item);
            } else {
                return $groupName == Vaimo_Menu_Model_Group::DEFAULT_GROUP;
            }

            return true;
        }, function($item) use ($useGroups) {
            if ($useGroups != Vaimo_Menu_Model_Group::DISABLED) {
                $item->setChildren(array());
            }
        }, true);

        return $tree;
    }

    public function setCategoryIdRestrictions($categoryIds)
    {
        if (empty($categoryIds)) {
            $categoryIds = array(0);
        }

        if (!is_array($categoryIds)) {
            $categoryIds = explode(',', $categoryIds);
        }

        $this->_restrictedCategoryIds = (array)$categoryIds;
        $this->setDataCacheKey(md5(implode(',', $categoryIds)));

        return $this;
    }

    public function getItemUrl()
    {
        $category = $this->getCategory();

        if (!Mage::helper('vaimo_menu')->getSessionIdUsedInUrl() && ($url = $category->getUrl())) {
            return $url;
        }

        $dataWithoutUrl = array_diff_key($category->getData(), array('url' => true));
        $generatedUrl = Mage::getSingleton('catalog/category')->setData($dataWithoutUrl)->getUrl();

        return $generatedUrl;
    }

    public function getItemLabel()
    {
        $category = $this->getCategory();

        return $category->getName();
    }

    public function setDataCacheKey($key)
    {
        $this->_dataCacheKey = sprintf(
            'menu_cache_%s_%s',
            $this->_storeId,
            $key
        );
    }

    protected function _getMenuStructureCacheKey($extraKey = false)
    {
        return $this->_dataCacheKey .
        '_' . ($this->getType() ? $this->getType() : get_class($this)) .
        '_' . $this->getDisplayLevels() .
        '_' . $this->getCustomRootId() .
        '_' . Mage::getSingleton('customer/session')->getCustomerGroupId() .
        '_' . $extraKey;
    }

    public function getCategoryTree($storeId)
    {
        $attributes = $this->getExtraAttributes();

        return Mage::getSingleton('vaimo_menu/catalog_category_tree')->getCategoryTree(
            $attributes,
            $this->_restrictedCategoryIds,
            $storeId,
            $this->getDataCacheLifetime()
        );
    }

    public function getMenuItemTree()
    {
        $cacheKey = $this->_getMenuStructureCacheKey();

        if ($this->_shouldLoadCache($cacheKey)) {
            if ($cachedData = Mage::app()->loadCache($cacheKey)) {
                if ($arrayTree = unserialize($cachedData)) {
                    $this->_menuItemTree = Mage::helper('vaimo_menu/tree')->arrayTreeToObjectTree($arrayTree);
                }
            }
        }

        if ($this->_shouldUpdateCache($cacheKey)) {
            $storeId = Mage::app()->getStore()->getId();
            $rootId = $this->_getRootId($storeId);
            $tree = $this->getCategoryTree($storeId);

            if ($rootId != null && $tree) {
                $tree = Mage::helper('vaimo_menu/tree')->find($tree, $rootId);

                if ($tree) {
                    // @todo: fix all unit-tests that expect parent_id to be removed -- this walkUpdate is actually not needed
                    Mage::helper('vaimo_menu/tree')->treeWalkUpdate($tree, function(&$item) {
                        unset($item['parent_id']);
                    });
                }

                $tree = array($tree);
            }

            $this->_menuItemTree = $this->_getMenuItemTree(array_filter($tree));

            $this->_htmlPerCategoryId = array();

            Mage::app()->saveCache(
                Mage::helper('vaimo_menu/tree')->serializeObjectTree($this->_menuItemTree),
                $cacheKey,
                $this->_cacheTags,
                $this->getDataCacheLifetime()
            );
        }

        return $this->_menuItemTree;
    }

    /**
     * The main reason for caching template files is to get nested function down for recursive rendering
     *
     * @return string
     */
    public function getTemplateFile()
    {
        $template = $this->getTemplate();
        if (!isset($this->_templateFiles[$template])) {
            $this->_templateFiles[$template] = parent::getTemplateFile();
        }

        return $this->_templateFiles[$template];
    }

    public function isInCurrentPath()
    {
        $categoryId = $this->getCategory()->getEntityId();

        if (!$categoryId) {
            return false;
        }

        if ($this->_currentCategoryPath === null) {
            $this->_currentCategoryPath = Mage::getSingleton('vaimo_menu/navigation')->getCurrentCategoryPathAsArrayKeys();
        }

        if (!$this->_currentCategoryPath) {
            return false;
        }

        return isset($this->_currentCategoryPath[$categoryId]);
    }

    public function getItemPlacementClass()
    {
        return '';
    }

    public function getMenuItemClass()
    {
        $placement = $this->getItemPlacementClass();
        $hierarchy = $this->getItemHierarchyClass();

        return trim($hierarchy . ($placement ? ' ' . $placement : ''));
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheIdValues = array(
            Vaimo_Menu_Model_Navigation::CACHE_TAG,
            $this->_storeId,
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
            Mage::getSingleton('customer/session')->getCustomerGroupId(),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            'type' => $this->getType(),
            $this->getCurrentCategoryKey(),
            (int)Mage::app()->getStore()->isCurrentlySecure()
        );

        $cacheIdValues['short_cache_id'] = md5(implode('|', $cacheIdValues));
        $cacheIdValues['category_path'] = $this->getCurrentCategoryKey();

        return $cacheIdValues;
    }

    protected function _updateMarkersForItem($item, $itemIndex, $itemsCount)
    {
        $markers = !($itemIndex % 2) ? 'even' : 'odd';
        $markers .= ($itemIndex == 0 ? ' first' : ($itemIndex == $itemsCount - 1 ? ' last' : ''));
        $this->setItemMarkers($markers);

        $class = 'level' . $this->getRelativeLevel();

        if ($item->getUrlKey()) {
            $class .= ' nav-' . $item->getUrlKey();
        }

        if ($this->isInCurrentPath()) {
            $class .= ' active';
        }

        if ($markers) {
            $class .= ' ' . $markers;
        }

        if ($this->hasChildren()) {
            $class .= ' parent';
        }

        if ($hierarchy = $item->getHierarchy()) {
            $class .= ' nav-' . $hierarchy;
        }

        $this->setItemClass($class);
    }

    public function getMenuTypeClass()
    {
        return implode(' ', $this->_typeClasses);
    }

    public function getRelativeLevel()
    {
        return $this->_getLevel() - $this->getStartLevel();
    }

    public function updateTypeConfig($configUpdates)
    {
        foreach ($configUpdates as $level => $update) {
            if (!isset($this->_typeConfig[$level])) {
                if ($level > 0 && !isset($this->_typeConfig[$level - 1])) {
                    throw Mage::exception('Vaimo_Menu', 'Inconsistent type configuration',
                        Vaimo_Menu_Exception::INVALID_MENU_TYPE_CONFIGURATION);
                }

                $this->_typeConfig[$level] = array();
            }

            $config = array_merge($this->_typeConfig[$level], $update);
            $this->_typeConfig[$level] = array();

            foreach ($config as $key => $value) {
                if ($value === false) {
                    continue;
                }

                $this->_typeConfig[$level][$key] = $value;
            }
        }

        return $this;
    }

    public function getChildHtmlByLevel($name = '')
    {
        $itemsRendered = false;
        $html = '';
        $level = $this->getRelativeLevel();

        if (!isset($this->_childBlocks[$name][$level]) || $this->_childBlocks[$name][$level]) {
            if ($child = $this->getChild($name)) {
                foreach ($child->getChild() as $child) {
                    if (($componentLevel = $child->getMenuLevel()) === false) {
                        $componentLevel = $this->getDefaultComponentLevel();
                    }

                    if ($componentLevel === false || $componentLevel === $level) {
                        $child->setMenuItem($this);
                        if ($child->getShouldRender()) {
                            $html .= $child->renderView();
                        }
                        $itemsRendered = true;
                    }
                }
            }

            if (!isset($this->_childBlocks[$name][$level])) {
                $this->_childBlocks[$name][$level] = $itemsRendered;
            }
        }

        return $html;
    }

    public function hasMultipleVisibleGroups()
    {
        if ($map = $this->getCategory()->getGroupMap()) {
            $visibleGroups = 0;
            $groups = array_keys($map);
            foreach ($groups as $group) {
                if ($this->shouldShowChildren($group)) {
                    $visibleGroups++;
                }

                if ($visibleGroups > 1) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getItemHierarchyClass()
    {
        return $this->getItemClass();
    }

    /**
     * Render children of current item
     *
     * @param string|bool $group
     * @return string
     */
    public function renderChildren($group = false)
    {
        /**
         * Determine if we should render children as group (if there are other groups that will be rendered)
         */
        if ($group && $this->getItemHierarchyClass()) {
            return $this->renderGroup($group);
        }

        /**
         * Render without the group wrapper -- no other groups are in place
         */
        if (!$group && $this->getCurrentGroup()) {
            $group = $this->getCurrentGroup();
        }

        if ($group == false) {
            $group = Vaimo_Menu_Model_Group::DEFAULT_GROUP;
        }

        if ($group != Vaimo_Menu_Model_Group::DEFAULT_GROUP && !$this->shouldShowChildren($group)) {
            return '';
        }

        return $this->renderNextLevel(false, $group);
    }

    protected function _increaseIterationLevel()
    {
        $this->setIterationLevel($this->getIterationLevel() + 1);
    }

    protected function _getPreRenderedHtml($categoryId)
    {
        $key = $this->_getMenuStructureCacheKey($categoryId);

        if (isset($this->_htmlPerCategoryId[$key])) {
            return $this->_htmlPerCategoryId[$key];
        }

        return false;
    }

    /**
     * Returns new tree level - if there is one
     *
     * @param bool $children
     * @param string $group
     * @return string
     */
    public function renderNextLevel($children = false, $group = Vaimo_Menu_Model_Group::DEFAULT_GROUP)
    {
        if ($children === false) {
            if ($group == Vaimo_Menu_Model_Group::DEFAULT_GROUP) {
                $html = $this->_getPreRenderedHtml($this->getCategory()->getEntityId());

                if ($html !== false) {
                    return $html;
                }
            }

            $children = $this->getChildren($group);
        }

        $this->_increaseIterationLevel();

        return $this->renderItems($children);
    }

    /**
     * This is the main loop enabling the recursive rendering of the menu tree
     *
     * @param array $items
     * @return string
     */
    public function renderItems($items)
    {
        if (!$items) {
            return '';
        }

        $html = '';
        $itemsCount = count($items);
        $itemIndex = 0;
        $originalData = $this->getData();

        if ($this->hasDisplayLevels()) {
            $relativeLevel = $this->getRelativeLevel();
            if ($relativeLevel >= $relativeLevel + $this->getDisplayLevels()) {
                return '';
            }
        }

        foreach($items as $item) {
            $this->setData($originalData);
            $this->setCategory($item);

            $this->setData('current_category_key', $item->getUrlPath());

            $this->_updateMarkersForItem($item, $itemIndex, $itemsCount);

            if ($this->hasParentGroup()) {
                $template = $this->getTemplateForLevel($this->getRelativeLevel(), $this->getParentGroup());
            } else {
                $template = $this->getTemplateForLevel($this->getRelativeLevel());
            }

            $this->setTemplate($template);

            $html .= $this->_toHtml();
            $itemIndex++;
        }

        $this->setData($originalData);

        return $html;
    }

    public function getMenuTypeConfig()
    {
        if ($this->_typeConfig && !is_object(reset($this->_typeConfig))) {
            foreach ($this->_typeConfig as &$config) {
                $config = new Varien_Object($config);
            }
        }

        return $this->_typeConfig;
    }

    protected function _getRootId($storeId)
    {
        return $this->hasCustomRootId() && $this->getCustomRootId() ? $this->getCustomRootId() : Mage::app()->getStore($storeId)->getRootCategoryId();
    }

    protected function _shouldLoadCache($cacheKey)
    {
        return $this->_menuItemTree == null && Mage::helper('vaimo_menu/cache')->test($cacheKey);
    }

    protected function _shouldUpdateCache($cacheKey)
    {
        return $this->_menuItemTree == null || !Mage::helper('vaimo_menu/cache')->test($cacheKey);
    }

    public function getConfigForAbsoluteLevel($absoluteLevel)
    {
        $level = $this->getRelativeLevelFromAbsolute($absoluteLevel);
        $config = $this->getMenuTypeConfig();
        if (isset($config[$level])) {
            return $config[$level];
        }

        return new Varien_Object();
    }

    protected function _preRenderLowerLevels($tree)
    {
        $items = array();
        Mage::helper('vaimo_menu/tree')->treeWalkStrict($tree, function($item, $parent, $level) use (&$items) {
            if ($item->getChildren()) {
                array_unshift($items, array('item' => $item, 'level' => $level));
            }
        });

        foreach ($items as $target) {
            $item = $target['item'];
            $this->setCategory($item);
            $this->setIterationLevel($target['level'] + $this->getStartLevel() - 1);
            if (!$item->hasMenuGroup() || $item->getMenuGroup() == Vaimo_Menu_Model_Group::DEFAULT_GROUP) {
                $key = $this->_getMenuStructureCacheKey($target['item']->getEntityId());
                $this->_htmlPerCategoryId[$key] = $this->renderNextLevel();
            }
        }

        $this->unsIterationLevel();
    }

    public function getRelativeLevelFromAbsolute($level)
    {
        return $level - $this->getStartLevel();
    }

    public function getStartLevel()
    {
        return $this->getDataSetDefault('start_level', Vaimo_Menu_Helper_Data::DEFAULT_ROOT_LEVEL);
    }

    public function getMenuLevelConfig($levelIncrement = 0)
    {
        $relativeLevel = $this->getRelativeLevel();

        if ($relativeLevel === false) {
            return false;
        }

        $relativeLevel += $levelIncrement;

        if ($typeConfig = $this->getMenuTypeConfig()) {
            if (isset($typeConfig[$relativeLevel])) {
                return $typeConfig[$relativeLevel];
            } else {
                if ($typeConfig) {
                    return $typeConfig[count($typeConfig) - 1];
                }
            }
        }

        return false;
    }

    public function getMenuLevelConfigForNextLevel($item = null)
    {
        if (!$item) {
            $item = $this;
        }

        $nextLevel = $item->getCurrentLevel() + 1;
        $config = $this->getMenuTypeConfig();

        if (isset($config[$nextLevel])) {
            return $config[$nextLevel];
        }

        $lastLevelConfig = end($config);
        if ($lastLevelConfig->getChildren()) {
            return $lastLevelConfig;
        } else {
            return false;
        }
    }

    public function hasChildren($group = null)
    {
        $category = $this->getCategory();
        $groupMap = $category->getGroupMap();

        return isset($groupMap[$group]) || ((!$group || $group == Vaimo_Menu_Model_Group::DEFAULT_GROUP) && $category->getChildren());
    }

    public function shouldShowChildren($group = Vaimo_Menu_Model_Group::DEFAULT_GROUP)
    {
        if (!$this->isInCurrentPath() && $this->getOnlySkipIfInCurrentPath()) {
            return false;
        }

        if (!$this->hasChildren($group)) {
            return false;
        }

        $config = $this->getMenuLevelConfig();

        if ($config && !$config->getChildren()) {
            return false;
        }

        if ($this->hasStartLevel() && $this->hasDisplayLevels()) {
            return $this->_getLevel() + 1 < $this->getStartLevel() + $this->getDisplayLevels();
        }

        return true;
    }

    public function setItemTemplate($template, $level = Vaimo_Menu_Model_Navigation::DEFAULT_LEVEL, $group = -1)
    {
        $this->_setItemTemplate($this->_templates, $template, $level, $group);

        return $this;
    }

    public function setGroupItemTemplate($template, $group = -1, $level = Vaimo_Menu_Model_Navigation::DEFAULT_LEVEL)
    {
        $this->_setItemTemplate($this->_templates, $template, $level, $group);

        return $this;
    }

    public function _setItemTemplate(&$templates, $template, $level = Vaimo_Menu_Model_Navigation::DEFAULT_LEVEL, $group = -1)
    {
        $templates[$group][$level] = $template;

        return $this;
    }

    public function setGroupTemplate($template, $group = Vaimo_Menu_Model_Group::DEFAULT_GROUP, $level = Vaimo_Menu_Model_Navigation::DEFAULT_LEVEL)
    {
        return $this->_setItemTemplate($this->_groupTemplates, $template, $level, $group);
    }

    public function getTemplateForLevel($level = Vaimo_Menu_Model_Navigation::DEFAULT_LEVEL, $group = -1)
    {
        return $this->_getTemplateForLevel($this->_templates, $level, $group);
    }

    protected function _getTemplateForLevel(&$templates, $level = Vaimo_Menu_Model_Navigation::DEFAULT_LEVEL, $group = -1)
    {
        if (isset($templates[$group][$level])) {
            return $templates[$group][$level];
        }

        if (isset($templates[$group][Vaimo_Menu_Model_Navigation::DEFAULT_LEVEL])) {
            return $templates[$group][Vaimo_Menu_Model_Navigation::DEFAULT_LEVEL];
        }

        if (isset($templates[Vaimo_Menu_Model_Group::DEFAULT_GROUP][Vaimo_Menu_Model_Navigation::DEFAULT_LEVEL])) {
            return $templates[Vaimo_Menu_Model_Group::DEFAULT_GROUP][Vaimo_Menu_Model_Navigation::DEFAULT_LEVEL];
        }

        if (isset($templates[-1][Vaimo_Menu_Model_Navigation::DEFAULT_LEVEL])) {
            return $templates[-1][Vaimo_Menu_Model_Navigation::DEFAULT_LEVEL];
        }

        return '';
    }

    public function belongsToNonDefaultMenuGroup()
    {
        $category = $this->getCategory();

        return ($category->hasMenuGroup() && $category->getMenuGroup() != Vaimo_Menu_Model_Group::DEFAULT_GROUP)
        || ($this->hasParentGroup() && $this->getParentGroup() != Vaimo_Menu_Model_Group::DEFAULT_GROUP);
    }

    public function renderGroup($group = null)
    {
        if ($this->shouldShowChildren($group)) {
            $_data = $this->getData();

            $template = $this->_getTemplateForLevel($this->_groupTemplates, $this->getRelativeLevel(), $group);
            $this->setCurrentGroup($group);
            $html = $this->setTemplate($template)
                ->setItemClass('menu-group-' . $group)
                ->renderView();

            $this->setData($_data);

            return $html;
        }

        return '';
    }

    public function renderGroupChildren($group = null)
    {
        if ($this->shouldShowChildren($group)) {
            $children = $this->_getGroupMembers($group);
            $html = '';

            foreach ($children as $child) {
                $_data = $this->getData();
                $this->setCategory($child);
                $this->setCategoryId($child->getEntityId());
                $this->setParentGroup($group);

                if ($this->_allowGroups == Vaimo_Menu_Model_Group::MERGED) {
                    $html .= $this->renderGroup(Vaimo_Menu_Model_Group::DEFAULT_GROUP);
                } else {
                    $template = $this->_getTemplateForLevel($this->_groupTemplates, $this->getRelativeLevel(), $group);
                    $html .= $this->setTemplate($template)
                        ->setItemClass('menu-group-' . $group)
                        ->renderView();
                }

                $this->setData($_data);
            }

            return $html;
        }

        return '';
    }

    public function _getGroupMembers($group)
    {
        return $this->getChildren($group);
    }

    public function getCurrentCategoryKey()
    {
        if (!$this->_currentCategoryKey) {
            if ($category = Mage::registry('current_category')) {
                $this->_currentCategoryKey = $category->getPath();
            } else {
                $this->_currentCategoryKey = Mage::app()->getStore()->getRootCategoryId();
            }
        }

        return $this->_currentCategoryKey;
    }

    public function setBlockCacheLifetime($lifetime)
    {
        $this->setData('cache_lifetime', $lifetime);
    }

    public function getDataCacheLifetime()
    {
        return $this->getDataSetDefault('data_cache_lifetime', Vaimo_Menu_Helper_Cache::CACHE_LIFETIME);
    }

    public function shouldShowLink()
    {
        return true;
    }

    protected function _getLevel()
    {
        return $this->getIterationLevel();
    }

    public function getChildListClass()
    {
        $category = $this->getCategory();

        if ($category->hasMenuGroup() && $category->getMenuGroup() != Vaimo_Menu_Model_Group::DEFAULT_GROUP) {
            return 'group-items';
        }

        $class = 'level' . $this->getRelativeLevel();

        $nextLevelConfig = $this->getMenuLevelConfigForNextLevel();
        if ($nextLevelConfig && $nextLevelConfig->getDirection() == Vaimo_Menu_Model_Type::DIRECTION_VERTICAL) {
            $class .= ' menu-vlist';
        }

        return $class;
    }

    public function getChildren($group = null)
    {
        $category = $this->getCategory();
        $children = $category->getChildren();

        if ($group === null) {
            return $children;
        }

        $groupMap = $category->getGroupMap();
        if ($groupMap) {
            if (isset($groupMap[$group])) {
                $groupId = $groupMap[$group];
                if (isset($children[$groupId])) {
                    $group = $children[$groupId];
                    $children = $group->getChildren();
                }
            }
        } elseif($group != Vaimo_Menu_Model_Group::DEFAULT_GROUP) {
            return array();
        }

        return $children;
    }

    public function _getMenuNameInLayout()
    {
        if ($this->_menuNameInLayout == null) {
            $nameInLayout = $this->getNameInLayout();

            if ($parent = $this->getParentBlock()) {
                if ($parent->getIsConfigurationContainer()) {
                    $nameInLayout = $parent->getNameInLayout();
                }
            }

            $this->_menuNameInLayout = $nameInLayout;
        }

        return $this->_menuNameInLayout;
    }

    public function hasWidget($attributeCode, $group = Vaimo_Menu_Model_Group::DEFAULT_GROUP)
    {
        if (!$this->_allowWidgets) {
            return false;
        }

        $currentGroup = $this->getCurrentGroup();
        if ($currentGroup && $currentGroup != $group) {
            return false;
        }

        $id = $this->_getWidgetId($attributeCode);

        return (bool)$this->_getWidgetName($id);
    }

    protected function _getWidgetId($attributeCode)
    {
        $category = $this->getCategory();

        if (!($widgetId = $category->getData($attributeCode))) {
            return '';
        }

        return $widgetId;
    }

    protected function _getWidgetName($widgetId)
    {
        $nameInLayout = $this->_getMenuNameInLayout();

        return Mage::getSingleton('vaimo_menu/catalog_category_widget')->getLayoutNameForWidget($nameInLayout, $widgetId);
    }

    public function getWidgetHtml($attributeCode)
    {
        if (!$this->_allowWidgets) {
            return '';
        }

        if ($id = $this->_getWidgetId($attributeCode)) {
            if ($widgetName = $this->_getWidgetName($id)) {
                if ($child = $this->getChild($widgetName)) {
                    return $child->toHtml();
                }
            }
        }

        return '';
    }

    /**
     * The main entry point for rendering a menu
     *
     * @return string
     */
    public function renderMenu()
    {
        $startLevel = $this->getStartLevel();
        $template = $this->getTemplate();

        $tree = $this->getMenuItemTree();

        if ($customRootFromLevel = $this->getCustomRootFromActiveItemsAncestorAtLevel()) {
            $itemFilter = Mage::getSingleton('vaimo_menu/navigation')->getCurrentCategoryPathAsArray();
            $tree = Mage::helper('vaimo_menu/tree')->treeExtract($tree, $customRootFromLevel, $itemFilter);
        }

        $tree = Mage::helper('vaimo_menu/tree')->treeExtract($tree, $startLevel);

        /**
         * Pre-rendering parts of the menu to avoid too much function call nesting (on menus with 3+ levels)
         */
        $this->_preRenderLowerLevels($tree);

        /**
         * Render menu
         */
        $this->setIterationLevel($startLevel - 1);
        $menuHtml = $this->renderNextLevel($tree);

        $this->unsIterationLevel();
        $this->setTemplate($template);

        return $menuHtml;
    }


    /**
     * Backwards compatibility function for initiating the menu rendering
     *
     * @return string
     */
    public function renderMenuItems()
    {
        return $this->renderMenu();
    }

    /**
     * Backwards compatibility function for initiating next menu level rendering. That's the function that used to
     * be called directly from templates. Note that none of the attributes are used anymore.
     *
     * @return string
     */
    public function renderMenuItem()
    {
        if ($this->_getLevel() < $this->getStartLevel()) {
            return '';
        }

        return $this->renderChildren('main');
    }

    /**
     * Backwards compatibility function for preparing certain values for old template to use
     */
    protected function _toHtml()
    {
        if ($category = $this->getCategory()) {
            $this->setCategoryId($category->getEntityId());

            $parameters = array(
                'markers' => $this->getItemMarkers()
            );

            if ($this->getRelativeLevel() == 0) {
                $parameters['markers'] .= ' level-top';
            }

            $hierarchy = $category->getHierarchy();

            if ($lastSeparator = (int)strrpos($hierarchy, '-')) {
                $parameters['hierarchy'] = substr($hierarchy, 0, $lastSeparator);
            }

            $parameters['no'] = substr($hierarchy, !$lastSeparator ? $lastSeparator : $lastSeparator + 1) - 1;

            $this->setParameters($parameters);
        }

        return parent::_toHtml();
    }
}