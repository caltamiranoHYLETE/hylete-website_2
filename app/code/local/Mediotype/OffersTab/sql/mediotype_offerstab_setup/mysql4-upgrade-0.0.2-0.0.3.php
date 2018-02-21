<?php

$installer = new Mage_Catalog_Model_Resource_Setup();
$installer->startSetup();

$attr = array(
	'group' => 'General',
	'attribute_model' => NULL,
	'backend' => '',
	'type' => 'int',
	'table' => '',
	'frontend' => '',
	'input' => 'select',
	'label' => 'Multi-Pack Offer',
	'frontend_class' => '',
	'source' => '',
	'required' => '0',
	'user_defined' => '1',
	'default' => '',
	'unique' => '0',
	'note' => '',
	'input_renderer' => NULL,
	'global' => '1',
	'visible' => '1',
	'searchable' => '0',
	'filterable' => '0',
	'comparable' => '0',
	'visible_on_front' => '1',
	'is_html_allowed_on_front' => '0',
	'is_used_for_price_rules' => '0',
	'filterable_in_search' => '0',
	'used_in_product_listing' => '1',
	'used_for_sort_by' => '0',
	'is_configurable' => '1',
	'apply_to' => 'simple,configurable',
	'visible_in_advanced_search' => '1',
	'position' => '1',
	'wysiwyg_enabled' => '0',
	'used_for_promo_rules' => '0',
	'option' =>
		array(
			'values' =>
				array(
					0 => 'any 2+ tri-blends $20ea'
				),
		),
);

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'multipack_offer', $attr);
