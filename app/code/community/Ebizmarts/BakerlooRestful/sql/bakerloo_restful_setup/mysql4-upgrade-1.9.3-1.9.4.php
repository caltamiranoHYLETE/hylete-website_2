<?php


$installer = $this;

$installer->startSetup();

$shiftsTable = $this->getTable('bakerloo_restful_shifts');
$installer->getConnection()->addColumn($shiftsTable, 'json_nextday_currencies', "text not null default '' ");

$installer->endSetup();