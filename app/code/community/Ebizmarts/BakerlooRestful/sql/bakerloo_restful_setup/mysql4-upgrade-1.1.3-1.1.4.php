<?php

$installer  = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$installer->run("
	ALTER TABLE `{$installer->getTable('bakerloo_restful/order')}` ENGINE='InnoDB';
	ALTER TABLE `{$installer->getTable('bakerloo_restful/debug')}` ENGINE='InnoDB';
	ALTER TABLE `{$installer->getTable('bakerloo_restful/catalogtrash')}` ENGINE='InnoDB';
	ALTER TABLE `{$installer->getTable('bakerloo_restful/inventorydelta')}` ENGINE='InnoDB';
");

$installer->endSetup();