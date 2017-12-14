<?php

/**
 * @author Myles Forrest <myles@mediotype.com>
 *
 * TODO: Confirm with Dale about need for 2 attributes; isn't "HyletePrice" just a re-labeling of the actual price?
 */

// Initialize setup
$installer = $this;
$installer->startSetup();

// Add 'retail_price' product attribute
// TODO: Assert that all needed attribute properties are set accordingly
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'retail_price', array(
	'label' => 'Retail Price',
	'group' => 'General',
	'type' => 'decimal', // TODO: ?
	'input' => 'price', // TODO: ?
//	'source' => 'eav/entity_attribute_source_table',
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

// Add 'hylete_price' product attribute
// TODO: Assert that all needed attribute properties are set accordingly
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'hylete_price', array(
	'label' => 'Hylete Price',
	'group' => 'General',
	'type' => 'decimal', // TODO: ?
	'input' => 'price', // TODO: ?
//	'source' => 'eav/entity_attribute_source_table',
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

// Finish setup
$installer->endSetup();
