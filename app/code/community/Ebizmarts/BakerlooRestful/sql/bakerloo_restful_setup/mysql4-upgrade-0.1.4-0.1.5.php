<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/debug'), 'request_method', 'varchar(10)');

$installer->endSetup();