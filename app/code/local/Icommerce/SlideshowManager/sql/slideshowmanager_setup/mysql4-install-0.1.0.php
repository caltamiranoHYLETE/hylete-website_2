<?php

$installer = $this;
$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS `{$this->getTable('icommerce_slideshow')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('icommerce_slideshow')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `position` int(11) NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- DROP TABLE IF EXISTS `{$this->getTable('icommerce_slideshow_item')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('icommerce_slideshow_item')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slideshow_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `image_alt` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `image_text` text NOT NULL,
  `link` varchar(255) NOT NULL,
  `link_target` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

");

$installer->endSetup();