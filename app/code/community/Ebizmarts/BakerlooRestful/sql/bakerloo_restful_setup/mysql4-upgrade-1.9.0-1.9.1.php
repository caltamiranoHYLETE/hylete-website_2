<?php

$installer = $this;

$installer->startSetUp();

$installer->run("
ALTER TABLE {$this->getTable('bakerloo_restful/quote')} MODIFY COLUMN json_payload LONGTEXT
");

$installer->run("
ALTER TABLE {$this->getTable('bakerloo_restful/quote')} MODIFY COLUMN json_payload_enc LONGTEXT
");


$installer->run("CREATE TABLE IF NOT EXISTS `{$this->getTable('bakerloo_restful_shifts')}` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `shift_guid` VARCHAR(255) NOT NULL DEFAULT '',
  `device_shift_id` INT(11) unsigned,
  `device_id` VARCHAR(255) NOT NULL DEFAULT '',
  `user` VARCHAR(255) default '',
  `open_date` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `open_notes` TEXT DEFAULT '',
  `json_open_currencies` TEXT DEFAULT '',
  `close_date` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `close_notes` TEXT DEFAULT '',
  `json_close_currencies` TEXT DEFAULT '',
  `counted_amount` decimal(12,4) DEFAULT '0.0000',
  `state` INT(1) DEFAULT 0,
  `sales_amount` decimal(12,4) DEFAULT '0.0000',
  `sales_amount_currency` VARCHAR(3) DEFAULT '',
  `json_vatbreakdown` TEXT DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");


$installer->run("CREATE TABLE IF NOT EXISTS `{$this->getTable('bakerloo_restful_shift_activities')}` (
  `id` INT(11) unsigned NOT NULL auto_increment,
  `shift_id` INT(11) unsigned NOT NULL,
  `type` VARCHAR(255) NOT NULL DEFAULT '',
  `activity_date` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `comments` TEXT DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

$installer->run("CREATE TABLE IF NOT EXISTS `{$this->getTable('bakerloo_restful_shift_movements')}` (
  `id` INT(11) unsigned NOT NULL auto_increment,
  `activity_id` INT(11) unsigned,
  `amount` DECIMAL(12,4) NOT NULL DEFAULT '0.0000',
  `balance` DECIMAL(12,4) NOT NULL DEFAULT '0.0000',
  `refunds` DECIMAL(12,4) NOT NULL DEFAULT '0.0000',
  `currency_code` VARCHAR(3) DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

$installer->endSetUp();