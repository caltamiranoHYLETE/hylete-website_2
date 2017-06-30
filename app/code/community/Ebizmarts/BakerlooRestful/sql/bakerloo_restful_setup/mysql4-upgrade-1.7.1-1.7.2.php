<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/order'), 'discount_amount', 'decimal(12,4) NULL');

$installer->endSetup();