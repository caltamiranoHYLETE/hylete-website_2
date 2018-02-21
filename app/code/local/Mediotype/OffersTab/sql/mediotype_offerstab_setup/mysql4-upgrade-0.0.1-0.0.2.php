<?php

$installer = $this;

$installer->startSetup();

// Remove "content" column
$installer->getConnection()
	->dropColumn($installer->getTable('mediotype_offerstab/offer'), 'content');

// Add "static_block_id" column
$installer->getConnection()
	->addColumn($installer->getTable('mediotype_offerstab/offer'), 'static_block_id', array(
		'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
		'nullable' => false,
		'comment' => 'Id of CMS static block to show for this Offer'
	));

// Add "customer_group_ids" column
$installer->getConnection()
	->addColumn($installer->getTable('mediotype_offerstab/offer'), 'customer_group_ids', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'size' => 255,
		'nullable' => true,
		'comment' => 'Ids of Customer Groups to show this Offer to'
	));

// Add "category_ids" column
$installer->getConnection()
	->addColumn($installer->getTable('mediotype_offerstab/offer'), 'category_ids', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'size' => 255,
		'nullable' => true,
		'comment' => 'Ids of Categories that this offer may show on'
	));

// Add "product_ids" column
$installer->getConnection()
	->addColumn($installer->getTable('mediotype_offerstab/offer'), 'product_ids', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'size' => 255,
		'nullable' => true,
		'comment' => 'Ids of Products that this offer may show on'
	));

// Add "priority" column
$installer->getConnection()
	->addColumn($installer->getTable('mediotype_offerstab/offer'), 'priority', array(
		'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
		'nullable' => true,
		'comment' => 'Priority used for determining order of Offers'
	));

// Add "landing page URL" column
$installer->getConnection()
	->addColumn($installer->getTable('mediotype_offerstab/offer'), 'landing_page_url', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'nullable' => true,
		'size' => 255,
		'comment' => 'If Offer is clicked through, redirect to this URL'
	));

$installer->endSetup();
