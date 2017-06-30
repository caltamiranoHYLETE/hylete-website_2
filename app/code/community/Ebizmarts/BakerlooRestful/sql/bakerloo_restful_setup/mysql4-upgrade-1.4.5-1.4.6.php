<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/order'), 'subtotal', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/order'), 'base_subtotal', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/order'), 'order_guid', 'varchar(255) NULL');

$installer->endSetup();