<?php

$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$connection->addColumn($this->getTable('sales_flat_quote_payment'), 'pos_sagepay_info', 'TEXT');
$connection->addColumn($this->getTable('sales_flat_order_payment'), 'pos_sagepay_info', 'TEXT');

$installer->endSetup();