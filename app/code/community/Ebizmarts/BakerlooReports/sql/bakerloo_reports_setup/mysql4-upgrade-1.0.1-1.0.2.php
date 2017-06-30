<?php


$installer = $this;
$installer->startSetup();

$reportsTable = $installer->getTable('bakerloo_reports/report');

$installer->getConnection()->addColumn($reportsTable, 'filters', "text NULL DEFAULT NULL");

$installer->endSetup();