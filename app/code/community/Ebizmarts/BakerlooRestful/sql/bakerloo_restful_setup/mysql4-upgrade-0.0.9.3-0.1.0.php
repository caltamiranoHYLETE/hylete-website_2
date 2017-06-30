<?php

$installer = $this;

$installer->startSetup();

$table = $installer->getTable('bakerloo_restful/order');

$installer->getConnection()->addColumn($table, 'store_id', 'smallint(5) unsigned NULL');
$installer->getConnection()->addColumn($table, 'grand_total', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($table, 'base_grand_total', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($table, 'base_shipping_amount', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($table, 'base_tax_amount', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($table, 'base_to_global_rate', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($table, 'base_to_order_rate', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($table, 'base_currency_code', 'varchar(3)');
$installer->getConnection()->addColumn($table, 'tax_amount', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($table, 'store_to_base_rate', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($table, 'store_to_order_rate', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($table, 'global_currency_code', 'varchar(3)');
$installer->getConnection()->addColumn($table, 'order_currency_code', 'varchar(3)');
$installer->getConnection()->addColumn($table, 'store_currency_code', 'varchar(3)');

$installer->endSetup();