<?php

$installer = $this;

$installer->startSetUp();

$installer->run("
  ALTER TABLE `{$this->getTable('bakerloo_restful_orders')}` MODIFY COLUMN json_payload LONGTEXT
");

$installer->run("
  ALTER TABLE `{$this->getTable('bakerloo_restful_orders')}` MODIFY COLUMN json_payload_enc LONGTEXT
");

$installer->run("
  CREATE TABLE IF NOT EXISTS `{$this->getTable('bakerloo_restful_customprice')}` (
    `id` int(11) unsigned NOT NULL auto_increment,
    `order_id` int(11) unsigned NOT NULL,
    `order_increment_id` varchar(50) NOT NULL default '',
    `admin_user` varchar(255) NOT NULL DEFAULT '',
    `store_id` smallint(5) unsigned DEFAULT NULL,
    `total_discount` decimal(12,4) DEFAULT '0.0000',
    `grand_total_before_discount` decimal(12,4) DEFAULT '0.0000',
    `grand_total_after_discount` decimal(12,4) DEFAULT '0.0000',
    `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8
");

$installer->endSetUp();