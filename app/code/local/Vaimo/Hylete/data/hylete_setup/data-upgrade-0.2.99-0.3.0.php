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
 * @package     Vaimo_Hylete
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

/** @var Mage_Catalog_Model_Resource_Setup $installer */
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');

$installer->startSetup();

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'retail_value', array(
    'label' => 'retail value',
    'group' => 'General',
    'type' => 'int',
    'input' => 'text',
    'source' => 'eav/entity_attribute_source_table',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => true,
    'visible_in_advanced_search' => false,
    'unique' => false,
    'is_system' => false,
    'used_for_sort_by' => true,
    'used_in_product_listing' => true
));


/** @var Mage_Cms_Model_Block $block */
$block = Mage::getModel('cms/block')->load('retail_value_tooltip');
if (!$block->getId()) {
    $block->setIdentifier('retail_value_tooltip');
    $block->setStores(array(0));
    $block->setIsActive(1);
    $block->setContent('Lorem ipsum');
    $block->save();
}

/** @var Mage_Cms_Model_Block $block */
$block = Mage::getModel('cms/block')->load('fire-checkout-credit-card-title');
if (!$block->getId()) {
    $block->setIdentifier('fire-checkout-credit-card-title');
    $block->setStores(array(0));
    $block->setIsActive(1);
    $block->setContent('Lorem Ipsum');
    $block->setTitle('Fire Checkout Credit Card Title');
    $block->save();
}
$installer->endSetup();
