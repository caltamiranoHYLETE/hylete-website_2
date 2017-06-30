<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful_orders'), 'salesperson', 'varchar(255) NULL default NULL');

$installer->endSetup();