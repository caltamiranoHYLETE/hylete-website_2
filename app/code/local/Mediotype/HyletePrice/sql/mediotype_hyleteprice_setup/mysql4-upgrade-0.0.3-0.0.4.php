<?php

$installer = $this;
$installer->startSetup();

$installer
	->getConnection()
	->addColumn($installer->getTable('customer/customer_group'),
		'hylete_price_cms_block_identifier', array(
			'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
			'length' => 64,
			'comment' => 'Identifier of the CMS block to be rendered into the tooltip modal',
		));

$installer->endSetup();
