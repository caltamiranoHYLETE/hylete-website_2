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

class Vaimo_Cms_Model_Fallback_Scope extends Vaimo_Cms_Model_Abstract
{
    const STORE = 1;
    const WEBSITE = 2;
    const BASE = 3;

    protected $_requiredArguments = array('store');

    protected $_storeId;

    protected $_websiteId;

    public function getStoreId()
    {
        return $this->_storeId;
    }

    public function ownsEntity(Vaimo_Cms_Model_Fallback_Scope_Interface $entity)
    {
        if ($this->_belongsToStoreScope($entity)) {
            return true;
        }

        if ($this->_isWebsiteScope() && $this->_belongsToWebsiteScope($entity)) {
            return true;
        }

        if ($this->_isBaseScope() && $this->_belongsToBaseScope($entity)) {
            return true;
        }

        return false;
    }

    public function sortByImportance($entities)
    {
        usort($entities, function($a, $b) {
            return $a->getScope() - $b->getScope();
        });

        return $entities;
    }

    public function apply(Vaimo_Cms_Model_Fallback_Scope_Interface $entity)
    {
        $scope = $this->_getScopeForEntity($entity);

        $entity->setScope($scope);

        if ($scope == Vaimo_Cms_Model_Fallback_Scope::STORE) {
            $entity->setScopeEntityId($this->_storeId);
        }

        if ($scope == Vaimo_Cms_Model_Fallback_Scope::WEBSITE) {
            $entity->setScopeEntityId($this->_websiteId);
        }

        if ($scope == Vaimo_Cms_Model_Fallback_Scope::BASE) {
            $entity->unsScopeEntityId();
        }
    }

    public function resolveFallback($entities, $groupingKey)
    {
        $resolvedEntities = array();
        foreach ($entities as $entity) {
            $group = $entity->getData($groupingKey);

            $current = isset($resolvedEntities[$group]) ? $resolvedEntities[$group] : false;

            if ($current && $current->getScope() >= $entity->getScope()) {
                unset($resolvedEntities[$group]);
            }

            if (isset($resolvedEntities[$group])) {
                continue;
            }

            $resolvedEntities[$group] = $entity;
        }

        return $resolvedEntities;
    }

    public function __construct(array $args = array())
    {
        $required = $this->_extractRequiredArgs($args);

        $this->_storeId = $required['store'];

        parent::__construct($args);
    }

    protected function _construct($parameters = array())
    {
        $store = $this->getApp()->getStore($this->_storeId);
        $website = $store->getWebsite();

        $this->_websiteId = $website->getId();
    }

    protected function _getStore()
    {
        $app = $this->getApp();

        return $app->getStore($this->_storeId);
    }

    protected function _isWebsiteScope()
    {
        $currentWebsiteDefaultStoreId = $this->_getStore()->getGroup()->getDefaultStoreId();

        return $currentWebsiteDefaultStoreId == $this->_storeId;
    }

    protected function _isBaseScope()
    {
        return $this->_getStore()->getWebsite()->getIsDefault() && $this->_isWebsiteScope();
    }

    protected function _belongsToStoreScope(Vaimo_Cms_Model_Fallback_Scope_Interface $entity)
    {
        $entityId = $entity->getScopeEntityId();

        return $entityId == $this->_storeId && $entity->getScope() == Vaimo_Cms_Model_Fallback_Scope::STORE;
    }

    protected function _belongsToWebsiteScope(Vaimo_Cms_Model_Fallback_Scope_Interface $entity)
    {
        $entityId = $entity->getScopeEntityId();

        return $entityId == $this->_websiteId && $entity->getScope() == Vaimo_Cms_Model_Fallback_Scope::WEBSITE;
    }

    protected function _belongsToBaseScope(Vaimo_Cms_Model_Fallback_Scope_Interface $entity)
    {
        return $entity->getScope() == Vaimo_Cms_Model_Fallback_Scope::BASE;
    }

    public function _getScopeForEntity(Vaimo_Cms_Model_Fallback_Scope_Interface $entity)
    {
        $scope = $entity->getScope();

        if ($scope == Vaimo_Cms_Model_Fallback_Scope::STORE) {
            return $scope;
        }

        if ($this->_isBaseScope()) {
            return Vaimo_Cms_Model_Fallback_Scope::BASE;
        }

        if ($this->_isWebsiteScope()) {
            return Vaimo_Cms_Model_Fallback_Scope::WEBSITE;
        }

        return Vaimo_Cms_Model_Fallback_Scope::STORE;
    }
}