<?php

$installer = $this;

$installer->startSetup();

/**
 * Create table 'bakerloo_restful/quote'
 */
$installer->run("
    CREATE TABLE IF NOT EXISTS `{$this->getTable('bakerloo_restful/quote')}` (
      `id` int(11) unsigned NOT NULL auto_increment,
      `order_guid` varchar(255) NOT NULL,
      `store_id` smallint(5) unsigned NULL,
      `json_payload` TEXT NULL,
      `json_payload_enc` TEXT NULL,
      `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
      `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY (`id`),
      KEY `IDX_GUID` (`order_guid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();