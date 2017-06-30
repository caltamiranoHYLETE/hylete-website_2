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
 * @package     Vaimo_CustomCss
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */
class Vaimo_CustomCss_Model_Resource_Customcss extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('customcss/customcss', 'customcss_id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId()) {
            $object->setCreationTime(Mage::getSingleton('core/date')->gmtDate());
        }
        $object->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());

        if (!$object->getFilename()) {
            $object->setFilename(md5('customcss' . time()) . '.css');
        }

        $object->setVersionHash(substr(md5(time()),0,10));

        $this->cleanCache();

        parent::_beforeSave($object);
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $this->_bindStores($object);
        $this->_writeCssFile($object);

        return parent::_afterSave($object);
    }

    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        $this->cleanCache();

        return parent::_beforeDelete($object);
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
            $object->setData('stores', $stores);
        }

        return parent::_afterLoad($object);
    }

    public function lookupStoreIds($id)
    {
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
                ->from($this->getTable('customcss/customcss_store'), 'store_id')
                ->where('customcss_id = :customcss_id');

        $binds = array(
                ':customcss_id' => (int) $id
        );

        return $adapter->fetchCol($select, $binds);
    }

    protected function _bindStores(Mage_Core_Model_Abstract $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();

        $table = $this->getTable('customcss/customcss_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = array('customcss_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete);

            $this->_getWriteAdapter()->delete($table, $where);
        }

        if ($insert) {
            $data = array();

            foreach ($insert as $storeId) {
                $data[] = array('customcss_id' => (int)$object->getId(), 'store_id' => (int)$storeId);
            }

            $this->_getWriteAdapter()->insertMultiple($table, $data);
        }
    }

    protected function _writeCssFile(Mage_Core_Model_Abstract $object)
    {
        $ioAdapter = new Varien_Io_File();

        try {
            $path = Mage::getBaseDir('media') . DS . Mage::helper('customcss')->getCssDir();
            $filePath = $path . DS . $object->getFilename();
            $ioAdapter->setAllowCreateFolders(true);
            $ioAdapter->open(array('path' => $path));

            $ioAdapter->write($filePath, $object->getCode());
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    public function cleanCache()
    {
        Mage::app()->cleanCache(Vaimo_CustomCss_Model_Customcss::CACHE_TAG);
        return $this;
    }
}