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

class Vaimo_Cms_Helper_Widget extends Vaimo_Cms_Model_Abstract
{
    protected $_basePackageTheme;

    protected function _construct()
    {
        $this->_basePackageTheme = join('/', array(
            Mage_Core_Model_Design_Package::BASE_PACKAGE,
            Mage_Core_Model_Design_Package::DEFAULT_THEME
        ));

        parent::_construct();
    }

    protected function _addPageGroups($instancePageCollection, $changes = array(), $pageGroup = false)
    {
        $pageGroups = array();

        foreach ($instancePageCollection as $instancePage) {
            $pageGroupCode = !$pageGroup ? $instancePage->getPageGroup() : $pageGroup;

            $pageId = $instancePage->getPageId();

            $groupData = array(
                'page_id' => $instancePage->getPageId(),
                'layout_handle' => $instancePage->getLayoutHandle(),
                'for' => $instancePage->getPageFor(),
                'block' => $instancePage->getBlockReference(),
                'template' => $instancePage->getPageTemplate()
            );

            if (isset($changes[$pageId])) {
                $groupData = array_merge($groupData, $changes[$pageId]);
            } else if (isset($changes[null])) {
                $groupData = array_merge($groupData, $changes[null]);
            }

            $pageGroups[] = array(
                'page_group' => $pageGroupCode,
                $pageGroupCode => $groupData
            );
        }

        return $pageGroups;
    }

    protected function _getWidgetInstance($widgetId)
    {
        $factory = $this->getFactory();

        $widgetInstance = $factory->getModel('vaimo_cms/widget_instance');

        $widgetInstance->load($widgetId);

        $instancePageCollection = $factory->getModel('vaimo_cms/widget_instance_page')->getCollection()
            ->addFieldToFilter('instance_id', $widgetId);

        $pageGroups = $this->_addPageGroups($instancePageCollection);
        $widgetInstance->setPageGroups($pageGroups);

        return $widgetInstance;
    }

    public function removePage($widgetPageId)
    {
        $widgetPage = $this->getFactory()->getModel('vaimo_cms/widget_instance_page')
            ->load($widgetPageId);

        $widgetInstance = $this->_getWidgetInstance($widgetPage->getInstanceId());

        if (!$widgetInstance->getId()) {
            return false;
        }

        $newPageGroups = array();
        foreach ($widgetInstance->getPageGroups() as $group) {
            $groupName = $group['page_group'];
            if ($group[$groupName]['page_id'] == $widgetPageId) {
                continue;
            }

            $newPageGroups[] = $group;
        }

        $widgetInstance->setPageGroups($newPageGroups);
        $widgetInstance->save();

        return true;
    }

    public function create($widgetData, $pages = array(), $storeIds = null)
    {
        $factory = $this->getFactory();

        $widgetInstance = $factory->getModel('vaimo_cms/widget_instance');

        if (isset($widgetData['widget_type'])) {
            $widgetInstance->setType($widgetData['widget_type']);
        } else {
            throw Mage::exception('Vaimo_Cms', 'Widget create called without widget type');
        }

        if (isset($widgetData['title'])) {
            $widgetInstance->setTitle($widgetData['title']);
        }

        $parameters = array();

        if (isset($widgetData['parameters'])) {
            $widgetInstance->setWidgetParameters($widgetData['parameters']);
            $parameters = $widgetData['parameters'];
        }

        if ($pages && !isset($parameters['template'])) {
            throw Mage::exception('Vaimo_Cms', 'Widget template not defined in parameters');
        }

        $groups = array();
        foreach ($pages as $page) {
            $groups[] = new Varien_Object(array(
                'page_group' => 'pages',
                'page_id' => 0,
                'layout_handle' => $page['handle'],
                'page_for' => 'all',
                'block_reference' => $page['reference'],
                'page_template' => $parameters['template']
            ));
        }

        if ($groups) {
            $pageGroups = $this->_addPageGroups($groups);
            $widgetInstance->setPageGroups($pageGroups);
        }

        if ($storeIds == null) {
            $storeIds = array($this->getApp()->getStore()->getId());
        }

        $widgetInstance->setStoreIds($storeIds);
        $widgetInstance->setPackageTheme($this->_basePackageTheme);

        $widgetInstance->save();

        /**
         * Needed to get correct page_ids
         */
        return $widgetInstance->load($widgetInstance->getId());
    }

    public function detachWidgetPageFromStore($pageId, $storeIdsToRemove)
    {
        if (!is_array($storeIdsToRemove)) {
            $storeIdsToRemove = array($storeIdsToRemove);
        }

        $factory = $this->getFactory();
        $page = $factory->getModel('vaimo_cms/widget_instance_page')->load($pageId);

        $instanceId = $page->getInstanceId();
        $instance = $factory->getModel('vaimo_cms/widget_instance')->load($instanceId);

        if (!$instance->getId()) {
            return;
        }

        $storeIds = $this->_getInstanceStoreIds($instance);
        $storeIds = array_diff($storeIds, $storeIdsToRemove);

        if (!array_filter($storeIds)) {
            $instance->setStoreIds(array());
            $instance->save();
            return;
        }

        $this->update(array(
            'instance_id' => $instance->getId(),
            'store_ids' => array_values($storeIds)
        ));
    }

    protected function _getInstanceStoreIds($instance)
    {
        $allStoreStoresIds = array_keys($this->getApp()->getStores());

        return array_intersect($allStoreStoresIds, $instance->getStoreIds());
    }

    public function update($widgetData, $storeIds = null)
    {
        $factory = $this->getFactory();

        $widgetInstance = $factory->getModel('vaimo_cms/widget_instance');

        $key = !isset($widgetData['instance_id']) && isset($widgetData['page_id']) ? 'page_id' : 'instance_id';

        $widgetInstance->load($widgetData[$key], $key);

        if (!$widgetInstance->getId()) {
            return;
        }

        $typeChanged = false;
        if (isset($widgetData['widget_type'])) {
            if ($widgetInstance->getType() != $widgetData['widget_type']) {
                $typeChanged = true;
            }

            $widgetInstance->setType($widgetData['widget_type']);
        }

        $parameters = array();
        if (isset($widgetData['parameters'])) {
            $widgetInstance->setWidgetParameters($widgetData['parameters']);
            $parameters = $widgetData['parameters'];
        } else if($typeChanged) {
            $widgetInstance->setWidgetParameters(array());
        }

        $instancePageCollection = $factory->getModel('vaimo_cms/widget_instance_page')->getCollection()
            ->addFieldToFilter('instance_id', $widgetInstance->getId());

        $custom = array();
        $pageId = isset($widgetData['page_id']) ? $widgetData['page_id'] : null;

        $custom[$pageId] = array();

        if (isset($parameters) && isset($parameters['template'])){
            $custom[$pageId]['template'] = $parameters['template'];
        }

        if (isset($widgetData['reference'])) {
            $custom[$pageId]['block'] = $widgetData['reference'];
        }

        if (isset($widgetData['handle'])) {
            $custom[$pageId]['layout_handle'] = $widgetData['handle'];
        }

        if (isset($widgetData['store_ids'])) {
            $widgetInstance->setStoreIds($widgetData['store_ids']);
        }

        $widgetInstanceStoreIds = $this->_getInstanceStoreIds($widgetInstance);
        
        if ($storeIds && array_diff($storeIds, $widgetInstanceStoreIds)) {
            $newStoreIds = array_values(array_unique(array_merge($widgetInstanceStoreIds, $storeIds)));
            $widgetInstance->setStoreIds($newStoreIds);
        }

        if (isset($widgetData['package_theme'])) {
            $widgetInstance->setPackageTheme($widgetData['package_theme']);
        }

        if (!$widgetInstance->hasDataChanges() && !array_filter($custom)) {
            return;
        }

        $pageGroups = $this->_addPageGroups($instancePageCollection, $custom, 'pages');
        $widgetInstance->setPageGroups($pageGroups);
        $widgetInstance->save();
    }

    public function parseWidgetParameters($widgetParameters)
    {
        if (!is_string($widgetParameters)) {
            return $widgetParameters;
        }

        $parameters = array();
        parse_str($widgetParameters, $parameters);

        return $parameters;
    }

    public function getAllRelevantLayoutHandles($instance)
    {
        $handles = array();

        foreach (array_filter((array)$instance->getData('page_groups')) as $group) {
            $handles[] = $group['layout_handle'];
        }

        foreach (array_filter((array)$instance->getOrigData('page_groups')) as $group) {
            $handles[] = $group['layout_handle'];
        }

        return $handles;
    }

    public function getUsageId($widget)
    {
        $pageGroups = $widget->getPageGroups();

        $pageIds = array();

        if ($pageGroups) {
            foreach ($pageGroups as $group) {
                $pageIds[] = $group['page_id'];
            }
        }

        return reset($pageIds);
    }
}
