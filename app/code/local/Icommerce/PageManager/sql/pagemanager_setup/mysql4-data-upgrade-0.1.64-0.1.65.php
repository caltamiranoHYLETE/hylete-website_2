<?php

$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `{$installer->getTable('icommerce_pagemanager_item')}`
	ADD COLUMN `filename_big` varchar(255) NOT NULL;
");

$installer->endSetup();