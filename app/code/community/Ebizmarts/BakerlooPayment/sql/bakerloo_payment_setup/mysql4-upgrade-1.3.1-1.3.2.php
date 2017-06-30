<?php

$installer = $this;

$installer->startSetup();

$installer->run(
    "CREATE TABLE IF NOT EXISTS `{$this->getTable('bakerloo_payment_installments')}` (
    `id` int(11) unsigned NOT NULL auto_increment,
    `parent_id` int(11) unsigned NOT NULL,
    `order_id` int(11) unsigned NOT NULL,
    `order_increment_id` int(11) unsigned NOT NULL,
    `pos_order_id` int (11) unsigned NOT NULL,
    `payment_id` int(11) unsigned NOT NULL,
    `amount_paid` decimal(12,4) NOT NULL,
    `amount_refunded` decimal(12,4) NOT NULL,
    `currency` char(3) NOT NULL,
    `payment_method` VARCHAR(255) NOT NULL,
    `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY(`id`),
    CONSTRAINT `FK_ORDER_ID` FOREIGN KEY (`order_id`) REFERENCES `{$this->getTable('sales_flat_order')}` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `FK_PARENT_ID` FOREIGN KEY (`parent_id`) REFERENCES `{$this->getTable('sales_flat_order_payment')}` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `FK_POS_ORDER_ID` FOREIGN KEY (`pos_order_id`) REFERENCES `{$this->getTable('bakerloo_restful_orders')}` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8
");

$installer->endSetup();