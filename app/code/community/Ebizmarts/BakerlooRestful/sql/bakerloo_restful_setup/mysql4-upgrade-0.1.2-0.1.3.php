<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/order'), 'device_order_id', 'varchar(255)');

$installer->endSetup();