<?php

$installer = $this;
$installer->startSetup();

$installer
	->getConnection()
	->addColumn($installer->getTable('customer/customer_group'),
		'customer_group_hylete_price_label', array(
			'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
			'length' => 32,
			'comment' => 'Label for displaying the final price (HYLETE price)',
		));

$installer->endSetup();
