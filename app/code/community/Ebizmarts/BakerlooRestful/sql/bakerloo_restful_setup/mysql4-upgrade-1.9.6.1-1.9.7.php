<?php

$installer = $this;
$installer->startSetup();

$debugTable = $installer->getTable('bakerloo_restful/debug');

$installer->getConnection()->modifyColumn($debugTable, 'debug_at', "TIMESTAMP NOT NULL DEFAULT 0");
$installer->getConnection()->modifyColumn($debugTable, 'debug_at', "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

$installer->endSetup();