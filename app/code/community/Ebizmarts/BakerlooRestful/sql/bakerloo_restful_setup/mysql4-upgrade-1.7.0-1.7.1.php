<?php

$installer = $this;

$installer->startSetup();

$dbTable = $installer->getTable('bakerloo_restful/quote');

$installer->getConnection()->addColumn($dbTable, 'customer_email', 'VARCHAR(255) null');
$installer->getConnection()->addColumn($dbTable, 'customer_firstname', 'VARCHAR(255) null');
$installer->getConnection()->addColumn($dbTable, 'customer_lastname', 'VARCHAR(255) null');
$installer->getConnection()->addColumn($dbTable, 'user', 'VARCHAR(255) null');
$installer->getConnection()->addColumn($dbTable, 'auth_user', 'VARCHAR(255) null');

$installer->endSetup();