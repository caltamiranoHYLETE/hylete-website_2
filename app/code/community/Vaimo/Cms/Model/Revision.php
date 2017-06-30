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

class Vaimo_Cms_Model_Revision extends Vaimo_Cms_Model_Abstract
{
    protected $_requiredArguments = array('handle', 'store');
    protected $_optionalArguments = array('structures', 'type');

    protected $_handle;
    protected $_storeId;

    protected $_type;

    protected $_scope;

    protected $_websiteId;
    protected $_website;
    protected $_websiteStoreIds;
    protected $_allWebsiteStoreIds;

    protected $_structuresLoaded = false;
    protected $_structureUsageInStores = false;
    protected $_structureUsageInStoresGlobal = false;
    protected $_hasStructureChanges = false;

    protected $_oldStructures = array();
    protected $_newStructures = array();

    protected $_unusedStoreIdsPerScope = array();
    protected $_allStoreIdsPerScope = array();

    public function __construct(array $args = array())
    {
        $required = $this->_extractRequiredArgs($args);
        $optional = $this->_extractOptionalArgs($args);

        $this->_handle = $required['handle'];
        $this->_storeId = $required['store'];

        parent::__construct($args);

        if (isset($optional['type'])) {
            $this->_type = $optional['type'];
        } else {
            $this->_type = $this->getFactory()->getModel('vaimo_cms/group_type_base');
        }

        if (isset($optional['structures'])) {
            $this->_loadCurrentStructures($optional['structures']);
        }
    }

    protected function _construct($parameters = array())
    {
        $this->_init('vaimo_cms/revision');

        $store = $this->getApp()->getStore($this->_storeId);
        $website = $store->getWebsite();

        $this->_websiteId = $website->getId();
        $this->_website = $website;
    }

    protected function _getScope()
    {
        if (!$this->_scope) {
            $this->_scope = $this->getFactory()->getModel('vaimo_cms/fallback_scope', array(
                'store' => $this->_storeId
            ));
        }

        return $this->_scope;
    }

    protected function _getWebsiteStoreIds()
    {
        if (!$this->_websiteStoreIds) {
            $factory = $this->getFactory();

            $website = $factory->getModel('core/website')->load($this->_websiteId);
            $this->_websiteStoreIds = $website->getStoreIds();
        }

        return $this->_websiteStoreIds;
    }

    protected function _getCurrentStructuresList()
    {
        $mergedStructureList = array();

        $usedReferences = array_keys(array_merge($this->_oldStructures, $this->_newStructures));
        foreach ($usedReferences as $reference) {
            if (isset($this->_oldStructures[$reference])) {
                $item = $this->_oldStructures[$reference];
            } else {
                $item = $this->_newStructures[$reference];
            }

            if (isset($this->_newStructures[$reference])) {
                $structure = $this->_newStructures[$reference];

                if ($structure->getScope() == Vaimo_Cms_Model_Fallback_Scope::STORE) {
                    $item = $structure;
                }
            }

            $mergedStructureList[$reference] = $item;
        }

        return $mergedStructureList;
    }

    protected function _loadCurrentStructures($structures = false)
    {
        if (!$this->_structuresLoaded) {
            if ($structures === false) {
                $resource = $this->getResource();
                $_structures = $resource->getStructureCollectionForHandleAndStore(
                    $this->_handle,
                    $this->_storeId,
                    $this->_type->getFilters()
                );

                $structures = array();
                foreach ($_structures as $structure) {
                    $structure->setDataChanges(false);

                    if ($this->_type->filterStructure($structure, $this->_getScope())) {
                        $structures[] = $structure;
                    }
                }
            }

            if (!is_array($structures)) {
                $structures = $structures->getItems();
            }

            $this->_oldStructures = $this->_getScope()
                ->resolveFallback($structures, 'block_reference');

            $this->_structuresLoaded = true;
        }

        return $this->_getCurrentStructuresList();
    }

    public function getStructures()
    {
        return $this->_loadCurrentStructures();
    }

    public function ownsStructure($structure)
    {
        return $this->_type->ownsStructure($structure) && $this->_getScope()->ownsEntity($structure);
    }

    public function getStructureForReference($reference)
    {
        $structures = $this->_loadCurrentStructures();

        if (isset($structures[$reference])) {
            return $structures[$reference];
        }

        return $this->getFactory()->getModel('vaimo_cms/structure');
    }

    public function hasAssignedStructures()
    {
        return (bool)$this->_newStructures;
    }

    public function assignStructure($structure)
    {
        $this->_type->assignStructure($structure);

        $structure->setHandle($this->_handle);

        $this->_getScope()->apply($structure);
        $reference = $structure->getBlockReference();

        $this->_newStructures[$reference] = $structure;

        return $structure;
    }

    protected function _parseStructureUsage($items)
    {
        $usage = array();

        foreach ($items as $item) {
            $reference = $item['block_reference'];

            if (!isset($usage[$reference])) {
                $usage[$reference] = array();
            }

            $usage[$reference][] = $item['store_id'];
        }

        return $usage;
    }

    public function hasStoreSpecificContent()
    {
        foreach ($this->_loadCurrentStructures() as $structure) {
            if (!$this->_getScope()->ownsEntity($structure)) {
                continue;
            }

            if ($structure->getStructure() != '[]') {
                return true;
            }
        }

        return false;
    }

    protected function _loadStoreScopeStructureUsageForWebsite()
    {
        if ($this->_structureUsageInStores === false) {
            $items = $this->getResource()->getStoreScopeStructureReferencesForHandleAndWebsite(
                $this->_handle,
                $this->_websiteId,
                $this->_type->getFilters()
            );

            $this->_structureUsageInStores = $this->_parseStructureUsage($items);
        }

        return $this->_structureUsageInStores;
    }

    protected function _loadStoreScopeStructureUsageForAllWebsites()
    {
        if ($this->_structureUsageInStoresGlobal === false) {
            $items = $this->getResource()->getStoreScopeStructureReferencesForHandle(
                $this->_handle,
                $this->_type->getFilters()
            );

            $this->_structureUsageInStoresGlobal = $this->_parseStructureUsage($items);
        }

        return $this->_structureUsageInStoresGlobal;
    }

    protected function _getUnusedStoresByReference($usageByReference, $allStoreIds)
    {
        $unused = array();
        foreach ($usageByReference as $reference => $usedIds) {
            $unused[$reference] = array_values(array_diff($allStoreIds, $usedIds));
        }

        return $unused;
    }

    protected function _getStoreIdsWithoutStructureByReference()
    {
        $usage = $this->_loadStoreScopeStructureUsageForWebsite();
        $storeIds = $this->_getWebsiteStoreIds();

        return $this->_getUnusedStoresByReference($usage, $storeIds);
    }

    protected function _getStoreIdsWithoutStructureByReferenceForAllWebsites()
    {
        $usage = $this->_loadStoreScopeStructureUsageForAllWebsites();

        $storeIds = $this->_getAllWebsiteStoreIds();

        return $this->_getUnusedStoresByReference($usage, $storeIds);
    }


    protected function _getAllWebsiteStoreIds()
    {
        if (!$this->_allWebsiteStoreIds) {
            $storeIds = $this->getResource()->getAllStoreIds();

            $websiteScopeUsage = $this->getResource()->getWebsiteScopeStructureReferencesForHandle(
                $this->_handle,
                $this->_type->getFilters()
            );

            $storeWebsites = $this->getResource()->getAllStoreWebsiteIds();

            $websiteStores = array();
            foreach ($storeWebsites as $storeId => $websiteId) {
                if (!isset($websiteStores[$websiteId])) {
                    $websiteStores[$websiteId] = array();
                }

                $websiteStores[$websiteId][] = $storeId;
            }

            foreach ($websiteScopeUsage as $structureUsage) {
                $websiteId = $structureUsage['website_id'];
                $storeIds = array_diff($storeIds, $websiteStores[$websiteId]);
            }

            $this->_allWebsiteStoreIds = array_values($storeIds);
        }

        return $this->_allWebsiteStoreIds;
    }

    protected function _areStructuresReplaceable($old, $new)
    {
        if (!$old->getId()) {
            return false;
        }

        if ($old->getId() == $new->getId()) {
            return false;
        }

        if ($old->getScope() != $new->getScope()) {
            return false;
        }

        return true;
    }

    protected function _getMergedStructureList()
    {
        $ids = array();
        foreach ($this->_newStructures as $reference => $structure) {
            if ($id = $structure->getId()) {
                $ids[$id] = $reference;
            }

            if (!isset($this->_oldStructures[$reference])) {
                continue;
            }

            $oldStructure = $this->_oldStructures[$reference];
            if (!$this->_areStructuresReplaceable($oldStructure, $structure)) {
                continue;
            }

            $oldStructure->setShouldDelete(true);
        }

        foreach ($this->_oldStructures as $reference => $structure) {
            if (!isset($ids[$structure->getId()])) {
                continue;
            }

            $_reference = $ids[$structure->getId()];
            $_structure = $this->_newStructures[$_reference];

            $structure->setData($_structure->getData());
            unset($this->_newStructures[$_reference]);
        }

        $structures = array_merge(
            array_values($this->_oldStructures),
            array_values($this->_newStructures)
        );

        return $structures;
    }

    protected function _getUnusedStoreIdsForScope($scope)
    {
        if (isset($this->_unusedStoreIdsPerScope[$scope])) {
            return $this->_unusedStoreIdsPerScope[$scope];
        }

        switch ($scope) {
            case Vaimo_Cms_Model_Fallback_Scope::WEBSITE:
                $storeIds = $this->_getStoreIdsWithoutStructureByReference();
                break;
            case Vaimo_Cms_Model_Fallback_Scope::BASE:
                $storeIds = $this->_getStoreIdsWithoutStructureByReferenceForAllWebsites();
                break;
            default:
                $storeIds = array();

        }

        $this->_unusedStoreIdsPerScope[$scope] = $storeIds;

        return $storeIds;
    }

    protected function _getStoreIdsForScope($scope)
    {
        if (isset($this->_allStoreIdsPerScope[$scope])) {
            return $this->_allStoreIdsPerScope[$scope];
        }

        switch ($scope) {
            case Vaimo_Cms_Model_Fallback_Scope::WEBSITE:
                $storeIds = $this->_getWebsiteStoreIds();
                break;
            case Vaimo_Cms_Model_Fallback_Scope::BASE:
                $storeIds = $this->_getAllWebsiteStoreIds();
                break;
            default:
                $storeIds = array($this->_storeId);
        }

        $this->_allStoreIdsPerScope[$scope] = $storeIds;

        return $storeIds;
    }

    protected function _getUnusedStoreIdsForScopeAndBlockReference($scope, $reference)
    {
        $unused = $this->_getUnusedStoreIdsForScope($scope);

        if (isset($unused[$reference])) {
            return $unused[$reference];
        }

        return $this->_getStoreIdsForScope($scope);
    }

    public function save()
    {
        if ($this->_newStructures) {
            $this->_loadCurrentStructures();
        }

        if (!$this->_structuresLoaded) {
            return;
        }

        $structures = $this->_getMergedStructureList();

        $this->_type->prepareStructuresForSave($structures, $this->_getScope());

        $structures = $this->_getScope()
            ->sortByImportance($structures);

        $structureForReferenceSaved = array();

        /** @var Vaimo_Cms_Helper_Structure $structureHelper */
        $structureHelper = $this->getFactory()->getHelper('vaimo_cms/structure');

        /** @var Vaimo_Cms_Model_Structure $structure */
        foreach ($structures as $structure) {
            $scope = $structure->getScope();
            $reference = $structure->getBlockReference();

            if (isset($structureForReferenceSaved[$reference])) {
                $structure->setDataChanges(true);
            }

            $structureForReferenceSaved[$reference] = true;

            if (!$structure->hasDataChanges()) {
                continue;
            }

            if (!$structure->hasStoreIds()) {
                $storeIds = $this->_getUnusedStoreIdsForScopeAndBlockReference($scope, $reference);
                $structure->setStoreIds($storeIds);
            }

            $structure->setResetWidgetStores(true);

            if ($structure->getShouldDelete()) {
                $structure->delete();
                $structure->unsStructureId();
            } else {
                $structure->save();
            }

            $validationResult = $structureHelper->validate($structure);

            if ($validationResult !== true) {
                throw Mage::exception(
                    'Vaimo_Cms', $validationResult, Vaimo_Cms_Exception::STRUCTURE_VALIDATION_FAILURE);
            }

            if ($structure->hasWidgetDataChanges()) {
                $this->_hasStructureChanges = true;
            }
        }

        /**
         * This is not used for anything at this point, but it gives a good point to intercept
         * page saves in tests. Used only for data-upgrade tests at this point. If we ever get
         * the reason to create an actual persistent resource out of page, this will become
         * more relevant.
         */
        $this->getResource()->save($this);
    }

    public function getHandle()
    {
        return $this->_handle;
    }

    public function hasStructureDataChanges()
    {
        return $this->_hasStructureChanges;
    }

    public function copyStructure($structure, $overwrite = true)
    {
        $factory = $this->getFactory();

        if ($this->ownsStructure($structure)) {
            if ($overwrite) {
                $this->assignStructure($structure);
            }

            return $structure;
        }

        $structureHelper = $factory->getHelper('vaimo_cms/structure');
        $currentStructure = $this->getStructureForReference($structure->getBlockReference());
        if ($this->ownsStructure($currentStructure)) {
            if (!$overwrite) {
                return $currentStructure;
            } else {
                $structureHelper->copyStructureData($structure, $currentStructure);
                $structure = $currentStructure;
            }
        } else if ($structure->getId()) {
            $structure = $structureHelper->getClone($structure);
        }

        $this->assignStructure($structure);

        return $structure;
    }

    public function invalidate()
    {
        foreach ($this->getStructures() as $structure) {
            $structure->setShouldDelete(true);
        }
    }
}