<?php

$installer = $this;
$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS `{$this->getTable('icommerce_support_tickets')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('icommerce_support_tickets')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `updated_date` int(11) NOT NULL,
  `created_date` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");

$installer->endSetup();