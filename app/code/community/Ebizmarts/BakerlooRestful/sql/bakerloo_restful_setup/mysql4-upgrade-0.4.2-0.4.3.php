<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('sales/quote_item'), 'pos_discount_reason', 'varchar(255) NULL');
$installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'pos_discount_reason', 'varchar(255) NULL');

$installer->endSetup();