<?php

$installer = $this;

$installer->startSetup();

$installer->run("

    CREATE TABLE IF NOT EXISTS `{$this->getTable('bakerloo_restful/pincode')}` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `admin_user_id` int(10) unsigned NOT NULL,
      `code` varchar(100) NULL,
      `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
      `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY (`id`,`admin_user_id`),
      CONSTRAINT `FK_BAKERLOO_PIN_USER` FOREIGN KEY (`admin_user_id`) REFERENCES `{$installer->getTable('admin/user')}` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$logTable = $installer->getTable('bakerloo_restful/debug');

$installer->getConnection()->addColumn($logTable, 'remote_addr', 'bigint unsigned null');
$installer->getConnection()->addColumn($logTable, 'user_agent', 'VARCHAR(255) null');
$installer->getConnection()->addColumn($logTable, 'resource', 'VARCHAR(100) null');
$installer->getConnection()->addColumn($logTable, 'call_time', 'decimal(10,4) null');
$installer->getConnection()->addColumn($logTable, 'response_code', 'smallint(3) unsigned null');

$installer->endSetup();