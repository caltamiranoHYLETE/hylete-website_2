<?php

$installer = $this;

$installer->startSetup();

$table = $installer->getTable('bakerloo_restful/order');

$installer->getConnection()->addColumn($table, 'latitude', 'decimal(18,12)');
$installer->getConnection()->addColumn($table, 'longitude', 'decimal(18,12)');
$installer->getConnection()->addColumn($table, 'admin_user_auth', 'varchar(255)');
$installer->getConnection()->addColumn($table, 'user_agent', 'varchar(255)');
$installer->getConnection()->addColumn($table, 'device_id', 'varchar(100)');
$installer->getConnection()->addColumn($table, 'remote_ip', 'varchar(32)');

$installer->endSetup();