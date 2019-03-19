<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'is_sharable',
    [
        'label' => 'Show share button',
        'group' => 'General',
        'type' => 'int',
        'input' => 'boolean',
        'source' => 'eav/entity_attribute_source_boolean',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        'searchable' => false,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => true,
        'visible_in_advanced_search' => false,
        'unique' => false,
        'apply_to' => '',
        'is_system' => false,
        'used_for_sort_by' => false,
        'used_in_product_listing' => true
    ]
);

$installer->endSetup();
