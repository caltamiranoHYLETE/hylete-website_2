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

class Vaimo_Cms_Model_Resource_Revision extends Vaimo_Cms_Model_Resource_Abstract
{
    protected function _getStructureCollectionForHandle($handle, array $filters = array())
    {
        $collection = $this->getFactory()->getModel('vaimo_cms/structure')->getCollection()
            ->addFieldToFilter('handle', $handle);

        $collection->getSelect()->order('scope ASC');

        Mage::getResourceHelper('vaimo_cms')->addFilters(
            $collection,
            $filters
        );

        return $collection;
    }

    public function getStructureCollectionForHandleAndStore($handle, $storeId, array $filters = array())
    {
        $collection = $this->_getStructureCollectionForHandle($handle, $filters);

        $collection->addScopeFilter($storeId);

        return $collection;
    }

    public function getStoreScopeStructureReferencesForHandleAndWebsite($handle, $websiteId, array $filters = array())
    {
        $collection = $this->_getStructureCollectionForHandle($handle, $filters);

        $collection->addFieldToSelect('block_reference');

        $select = $collection->getSelect();

        $sql = 's_store.store_id=main_table.scope_entity_id AND main_table.scope=? AND s_store.website_id=?';

        $select->join(
            array('s_store' => $this->getFactory()->getSingleton('core/resource')->getTableName('core/store')),
            Mage::getResourceHelper('vaimo_cms')->quoteIntoMultiple($sql, array(
                Vaimo_Cms_Model_Fallback_Scope::STORE,
                $websiteId
            )),
            array('store_id')
        );

        /** @var Magento_Db_Adapter_Pdo_Mysql $read */
        $read = $this->_getReadAdapter();

        return $read->fetchAll($select);
    }

    public function getStoreScopeStructureReferencesForHandle($handle, array $filters = array())
    {
        $collection = $this->_getStructureCollectionForHandle($handle, $filters);

        $collection->addFieldToSelect('block_reference')
            ->addFieldToSelect('scope_entity_id', 'store_id')
            ->addFieldToFilter('scope', Vaimo_Cms_Model_Fallback_Scope::STORE);

        /** @var Magento_Db_Adapter_Pdo_Mysql $read */
        $read = $this->_getReadAdapter();

        return $read->fetchAll($collection->getSelect());
    }

    public function getWebsiteScopeStructureReferencesForHandle($handle, array $filters = array())
    {
        $collection = $this->_getStructureCollectionForHandle($handle, $filters);

        $collection->addFieldToSelect('block_reference')
            ->addFieldToSelect('scope_entity_id', 'website_id')
            ->addFieldToFilter('scope', Vaimo_Cms_Model_Fallback_Scope::WEBSITE)
            ->addFieldToFilter('scope_entity_id', array('neq' => 0));

        /** @var Magento_Db_Adapter_Pdo_Mysql $read */
        $read = $this->_getReadAdapter();

        return $read->fetchAll($collection->getSelect());
    }

    public function getAllStoreIds()
    {
        $collection = $this->getFactory()->getModel('core/store')->getCollection()
            ->addFieldToSelect('store_id', 'website_id')
            ->addFieldToFilter('store_id', array('neq' => 0));

        /** @var Magento_Db_Adapter_Pdo_Mysql $read */
        $read = $this->_getReadAdapter();

        return $read->fetchCol($collection->getSelect(), array('store_id'));
    }

    public function getAllStoreWebsiteIds()
    {
        $collection = $this->getFactory()->getModel('core/store')->getCollection()
            ->addFieldToSelect('store_id')
            ->addFieldToSelect('website_id')
            ->addFieldToFilter('store_id', array('neq' => 0));

        /** @var Magento_Db_Adapter_Pdo_Mysql $read */
        $read = $this->_getReadAdapter();

        $items = array_map(function($item) {
            return $item['website_id'];
        }, $read->fetchAssoc($collection->getSelect()));

        return $items;
    }

    public function getAllStoreIdsForWebsite($websiteId)
    {
        $collection = $this->getFactory()->getModel('core/store')->getCollection()
            ->addFieldToSelect('store_id');

        $collection->addFieldToFilter('website_id', $websiteId);

        $select = $collection->getSelect();

        /** @var Magento_Db_Adapter_Pdo_Mysql $read */
        $read = $this->_getReadAdapter();

        return $read->fetchCol($select, array('store_id'));
    }

    public function save($object)
    {
        return $this;
    }
}