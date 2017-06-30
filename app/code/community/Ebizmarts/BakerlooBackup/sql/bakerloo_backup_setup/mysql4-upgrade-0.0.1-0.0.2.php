<?php

$installer = $this;

$backupTable = $installer->getTable('bakerloo_backup/files');

$installer->getConnection()->addColumn($backupTable, 'store_id', "int(11) unsigned NOT NULL default '0'");
