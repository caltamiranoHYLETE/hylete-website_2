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

$installer = $this;
$installer->startSetup();

$table = $installer->getTable('vaimo_cms/structure');

$pages = array(
    'default' => array(),
    'non-default' => array()
);

/** @var Vaimo_Cms_Model_Resource_Structure_Collection $collection */
$collection = Mage::getModel('vaimo_cms/structure')->getCollection();

$defaultStoreStructures = array();
foreach ($collection as $structure) {
    $handle = $structure->getHandle();
    $storeId = $structure->getStoreId();

    $store = Mage::app()->getStore($storeId);

    if (!$store->getGroup()) {
        $store->load($storeId);
    }

    if ($storeId == $store->getGroup()->getDefaultStoreId()) {
        $structure->unsStoreId();
        $scopeKey = 'default';
    } else {
        $structure->setScope(Vaimo_Cms_Model_Fallback_Scope::STORE);
        $scopeKey = 'non-default';
    }

    if (!isset($pages[$scopeKey][$handle])) {
        $pages[$scopeKey][$handle] = array();
    }

    if (!isset($pages[$scopeKey][$handle][$storeId])) {
        $page = Mage::getModel('vaimo_cms/revision', array(
            'handle' => $handle,
            'store' => $storeId
        ));

        $pages[$scopeKey][$handle][$storeId] = $page;
    }

    $page = $pages[$scopeKey][$handle][$storeId];
    $page->assignStructure($structure);
}

array_walk_recursive($pages, function($page) {
    $page->save();
});

$this->getConnection()->dropColumn($table, 'store_id');

$installer->endSetup();
