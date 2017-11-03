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

/**
 * Class Vaimo_Cms_Model_Structure
 * @method string getHandle();
 * @method object setHandle(string $value);
 * @method object setStoreId(int $id);
 * @method string getBlockReference();
 * @method object setBlockReference(string $value);
 * @method object setShouldDelete(bool $shouldDeleteStructure);
 * @method bool getShouldDelete();
 */
class Vaimo_Cms_Model_Structure extends Vaimo_Cms_Model_Abstract implements
    Vaimo_Cms_Model_Fallback_Scope_Interface
{
    const CMS_BLOCK_WIDGET_TYPE = 'cms/widget_block';

    protected $_hasWidgetDataChanges = false;
    protected $_allowedItemKeys = array('col', 'row', 'size_x', 'size_y', 'widget_page_id', 'widget_id',
        'widget_parameters', 'widget_new_cms_block', 'clone_of', 'widget_type');

    protected function _construct($parameters = array())
    {
        $this->_init('vaimo_cms/structure');
    }

    public function hasWidgetDataChanges()
    {
        return $this->_hasWidgetDataChanges;
    }

    protected function _getItemsWithPageIds($structureData)
    {
        $itemsByPageId = array();
        foreach ($structureData as $item) {
            if (!isset($item['widget_page_id'])) {
                continue;
            }

            $itemsByPageId[$item['widget_page_id']] = $item;
        }

        return $itemsByPageId;
    }

    public function save()
    {
        if ($this->getShouldDelete()) {
            return $this;
        }

        return parent::save();
    }

    protected function _beforeSave()
    {
        if ($this->getId() || $this->getOrigData('structure')) {
            $removedWidgets = $this->_removeWidgets();

            if (!empty($removedWidgets)) {
                $this->_hasWidgetDataChanges = true;
            }

            $updatedWidgets = $this->_updateWidgets();

            if (!empty($updatedWidgets)) {
                $this->_hasWidgetDataChanges = true;
            }
        }

        $updates = $this->_createWidgetsFromStructure();

        $structureItems = $this->_restoreCloneMarkers($updates['updated_structure']);

        foreach($structureItems as $index => &$item) {
            unset($item['widget_type']);
            unset($item['widget_parameters']);
        }

        $this->setStructureData($structureItems);

        if (!empty($updates['created_widgets'])) {
            $this->_hasWidgetDataChanges = true;
        }

        parent::_beforeSave();
    }

    protected function _restoreCloneMarkers($structure)
    {
        $origItemsWithPageId = $this->_getItemsWithPageIds($this->getOrigStructureData());

        foreach ($structure as &$item) {
            if (!isset($item['widget_page_id'])) {
                continue;
            }

            if (!isset($origItemsWithPageId[$item['widget_page_id']])) {
                continue;
            }

            $origItem = $origItemsWithPageId[$item['widget_page_id']];
            if (!isset($origItem['clone_of'])) {
                continue;
            }

            $item['clone_of'] = $origItem['clone_of'];
        }

        return $structure;
    }

    protected function _removeWidgets()
    {
        $structureBefore = $this->getOrigStructureData();

        if (!$structureBefore) {
            return array();
        }

        $structureAfter = $this->getStructureData();

        $factory = $this->getFactory();
        $widgetsToRemove = $this->diffWidgets($structureBefore, $structureAfter, 'widget_page_id');

        $storeIds = $this->getStoreIds();

        $widgetHelper = $factory->getHelper('vaimo_cms/widget');
        foreach ($widgetsToRemove as $widgetPageId) {
            if (!is_int($widgetPageId) || !$widgetPageId) {
                continue;
            }

            $widgetHelper->detachWidgetPageFromStore($widgetPageId, $storeIds);
        }

        return $widgetsToRemove;
    }

    protected function _updateWidgets()
    {
        $structure = $this->getStructureData();

        $factory = $this->getFactory();

        /* @var $widgetEditor Vaimo_Cms_Model_Widget_Editor */
        $widgetHelper = $factory->getHelper('vaimo_cms/widget');

        /** @var Vaimo_Cms_Model_Structure_Widgets $structureWidgets */
        $structureWidgets = $factory->getSingleton('vaimo_cms/structure_widgets');

        $storeIds = $this->getStoreIds();
        $storeIdsPerPageId = $this->_getCurrentStructureItemStoreIds();

        $blockReference = $this->getBlockReference();

        $isPublishAction = !$this->getOrigData('published') && $this->getData('published');

        $shouldUpdateBlockReference = $this->getOrigData('block_reference') !== $blockReference;
        $shouldUpdateHandle = $this->getOrigData('handle') !== $this->getHandle() || $isPublishAction;

        if ($isPublishAction) {
            $widgetParamsPerItem = $this->getParametersForStructureWidgets();
        } else {
            $widgetParamsPerItem = array();
        }

        $updatedWidgets = array();
        foreach($structure as $index => &$item) {
            if (empty($item['widget_page_id'])) {
                continue;
            }

            $pageId = $item['widget_page_id'];

            $widgetData = array(
                'page_id' => $pageId
            );

            if ($handle = $this->getWidgetHandle()) {
                $widgetData['handle'] = $handle;
            }

            if ($shouldUpdateBlockReference) {
                $widgetData['reference'] = $blockReference;
            }

            if (isset($item['widget_parameters'])) {
                $parameters = $widgetHelper->parseWidgetParameters($item['widget_parameters']);

                if (isset($parameters['parameters'])) {
                    $widgetData = array_merge($widgetData, $parameters);
                } else {
                    $widgetData['parameters'] = $parameters;
                }
            }

            if ($isPublishAction && $widgetParamsPerItem) {
                $params = $widgetParamsPerItem[$pageId];
                $publishedParameters = $structureWidgets->publishParameters(
                    $params['type'],
                    $params['parameters']
                );

                if ($publishedParameters && array_diff($params['parameters'], $publishedParameters)) {
                    $widgetData['parameters'] = $publishedParameters;
                }
            }

            if ($storeIdsUpdateRequired = (bool)$storeIds) {
                if (isset($storeIdsPerPageId[$pageId]) && $storeIds) {
                    $storeIdsUpdateRequired = (bool)array_diff($storeIds, $storeIdsPerPageId[$pageId]);
                }
            }

            if ($this->getResetWidgetStores()) {
                if (!isset($storeIdsPerPageId[$pageId])) {
                    Mage::throwException('Structure save (id=' . $this->getId() . '): No store ids for widget page=' . $pageId);
                }

                $detachStoreIds = array_diff($storeIdsPerPageId[$pageId], $storeIds);

                if ($detachStoreIds) {
                    $widgetHelper->detachWidgetPageFromStore($pageId, $detachStoreIds);
                }
            }

            if ($storeIdsUpdateRequired
                || isset($widgetData['parameters'])
                || (isset($widgetData['reference']) && isset($widgetData['handle']))
                || ($shouldUpdateHandle && isset($widgetData['handle']))
            ) {
                $widgetHelper->update($widgetData, $storeIds);
                $updatedWidgets[] = $item;

                unset($item['widget_parameters']);
            }
        }

        $this->setResetWidgetStores(false);

        if ($updatedWidgets) {
            $this->setStructureData($structure);
        }

        return $updatedWidgets;
    }

    protected function _getPageIdsForWidget($widget)
    {
        $pageGroups = $widget->getPageGroups();

        $pageIds = array();

        if ($pageGroups) {
            foreach ($pageGroups as $group) {
                $pageIds[] = $group['page_id'];
            }
        }

        return $pageIds;
    }

    protected function _getWidgetHandle()
    {
        return $this->hasWidgetHandle() ? $this->getWidgetHandle() : $this->getHandle();
    }

    protected function _createWidgetsFromStructure()
    {
        $factory = $this->getFactory();

        /* @var $widgetCreator Vaimo_Cms_Helper_Widget */
        $widgetCreator = $factory->getHelper('vaimo_cms/widget');

        /** @var Vaimo_Cms_Model_Structure_ItemManager $itemManager */
        $itemManager = $factory->getSingleton('vaimo_cms/structure_itemManager');

        $structureItems = $this->getStructureData();
        $handle = $this->_getWidgetHandle();

        $pages = array(array(
            'handle' => $handle,
            'reference' => $this->getBlockReference()
        ));

        $createdWidgets = array();

        $handle = $this->getHandle();
        $reference = $this->getBlockReference();

        foreach($structureItems as $index => &$item) {
            if (isset($item['widget_page_id'])) {
                continue;
            }

            $itemConfig = $itemManager->getWidgetParameters($item, $handle, $reference);

            if (empty($itemConfig)) {
                continue;
            }

            if ($itemConfig['parameters'] === false) {
                throw Mage::exception(
                    'Vaimo_Cms',
                    'Widget creation failure. Could not solve parameters'
                );
            }

            $itemConfig['title'] = $factory->getHelper('vaimo_cms')->__('Widget created by Vaimo_Cms');

            $widget = $widgetCreator->create(
                $itemConfig,
                $pages,
                $this->getStoreIds()
            );

            $createdWidgets[] = $widget;

            $item['widget_page_id'] = $widgetCreator->getUsageId($widget);
        }

        return array(
            'updated_structure' => $structureItems,
            'created_widgets' => $createdWidgets
        );
    }

    protected function _getStructurePageIds()
    {
        $pageIds = array();
        foreach ($this->getStructureData() as $item) {
            if (!isset($item['widget_page_id'])) {
                continue;
            }

            $pageIds[] = $item['widget_page_id'];
        }

        return $pageIds;
    }

    protected function _getCurrentStructureItemStoreIds()
    {
        $pageIds = $this->_getStructurePageIds();
        $storeIdsPerPage = $this->_getResource()->getStoreIdsForPageIds($pageIds);

        return is_array($storeIdsPerPage) ? $storeIdsPerPage : array();
    }

    /**
     * Return widgets found in $structure1 that does exists in $structure2
     *
     * @param array $structure1
     * @param array $structure2
     * @param string $uniqueIdKey
     * @return array
     */
    public function diffWidgets(array $structure1, array $structure2, $uniqueIdKey = 'widget_id')
    {
        if (empty($structure1) && empty($structure2)) {
            return array();
        }

        $widgetsBefore = array();
        foreach ($structure1 as $item) {
            if (isset($item[$uniqueIdKey])) {
                $widgetsBefore[] = $item[$uniqueIdKey];
            }
        }

        $widgetsAfter = array();
        foreach ($structure2 as &$dataItem) {
            if (isset($dataItem[$uniqueIdKey])) {
                $id = $dataItem[$uniqueIdKey];
                $widgetsAfter[] = $id;
            }
        }

        return array_values(array_diff($widgetsBefore, $widgetsAfter));
    }

    public function setWidgetParameters($pageId, array $parameters)
    {
        $structureData = $this->getStructureData();

        foreach ($structureData as &$item) {
            if ($item['widget_page_id'] == $pageId) {
                $item['widget_parameters'] = $parameters;
                break;
            }
        }

        $this->setStructureData($structureData);

        return $this;
    }

    public function getItem($id, $field = 'widget_page_id')
    {
        $structureData = $this->getStructureData();

        foreach ($structureData as &$item) {
            if (!isset($item[$field])) {
                continue;
            }

            if ($item[$field] == $id) {
                return $item;
            }
        }

        return array();
    }

    public function findItem($id, $fields = array('widget_page_id'))
    {
        $widget = array();

        foreach ($fields as $field) {
            $widget = $this->getItem($id, $field);

            if ($widget) {
                break;
            }
        }

        return $widget;
    }

    protected function _decodeStructure($structure)
    {
        if (!$structure) {
            return array();
        }

        if (is_array($structure)) {
            return $structure;
        }

        $decodedStructure = Zend_Json_Decoder::decode($structure);

        return $decodedStructure ? $decodedStructure : array();
    }

    protected function _encodeStructure($structure)
    {
        if (!$structure) {
            $structure = array();
        }

        $filter = array_flip($this->_allowedItemKeys);

        $_structure = array();
        foreach ($structure as &$item) {
            if (!is_array($item)) {
                continue;
            }

            $_structure[] = array_intersect_key($item, $filter);
        }

        return Vaimo_Cms_Json_Encoder::encode($_structure);
    }

    public function getStructureData()
    {
        return $this->_decodeStructure($this->getStructure());
    }

    public function getStructureDataForLayout($layout)
    {
        $structureData = $this->getStructureData();

        $widgetInfo = $this->getFactory()->getHelper('vaimo_cms/layout')
            ->getWidgetLayoutUpdateDataGroupedByPageId($layout->getUpdate());

        foreach ($structureData as &$item) {
            if (!isset($item['class'])) {
                $item['class'] = '';
            }

            if (!isset($item['widget_page_id'])) {
                continue;
            }

            $pageId = $item['widget_page_id'];

            if (!isset($widgetInfo[$pageId])) {
                continue;
            }

            $item['name'] = $widgetInfo[$pageId]['name'];

            unset($item);
        }

        return $structureData;
    }

    public function getOrigStructureData()
    {
        return $this->_decodeStructure($this->getOrigData('structure'));
    }

    public function setStructureData($structure)
    {
        $encodedStructure = $this->_encodeStructure($structure);

        return $this->setStructure($encodedStructure);
    }

    public function setOrigStructureData($structure)
    {
        $encodedStructure = $this->_encodeStructure($structure);

        return $this->setOrigData('structure', $encodedStructure);
    }
    
    public function delete()
    {
        $this->setStructureData(array());
        $this->_removeWidgets();

        parent::delete();
    }

    public function getParametersForStructureWidgets()
    {
        $pageIds = $this->_getStructurePageIds();

        $configurations = $this->_getResource()
            ->getWidgetTypeAndParametersForPageIds($pageIds);

        foreach ($configurations as &$item) {
            $item['parameters'] = unserialize($item['parameters']);
        }

        return $configurations;
    }

    public function getParametersForItem($id, $key = 'widget_page_id')
    {
        $item = $this->getItem($id, $key);

        if (!$item) {
            return false;
        }

        $pageId = $item['widget_page_id'];
        $parameters = $this->getParametersForStructureWidgets();

        if (!isset($parameters[$pageId])) {
            return false;
        }

        return $parameters[$pageId]['parameters'];
    }

    public function getScope()
    {
        return $this->getData('scope');
    }

    public function setScope($scopeId)
    {
        return $this->setData('scope', $scopeId);
    }

    public function getScopeEntityId()
    {
        return $this->getData('scope_entity_id');
    }

    public function setScopeEntityId($entityId)
    {
        return $this->setData('scope_entity_id', $entityId);
    }
}