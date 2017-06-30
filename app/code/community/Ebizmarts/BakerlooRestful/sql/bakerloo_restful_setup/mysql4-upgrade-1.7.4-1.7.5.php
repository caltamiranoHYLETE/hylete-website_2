<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('tax/tax_calculation_rate'), 'ebizmarts_pos_synch', 'tinyint(1) unsigned NOT NULL default \'0\'');

$installer->endSetup();