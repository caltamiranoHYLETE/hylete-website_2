<?php
$helper = Mage::helper('pagemanager');
$installer = $this;
$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS `{$this->getTable('icommerce_pagemanager')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('icommerce_pagemanager')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `position` int(11) NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- DROP TABLE IF EXISTS `{$this->getTable('icommerce_pagemanager_row')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('icommerce_pagemanager_row')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$this->getTable('icommerce_pagemanager_item')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('icommerce_pagemanager_item')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `row_id` int(11) NOT NULL,
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
  `page_content` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `visibility` text NOT NULL,
  `category_id` int(11) NOT NULL,
  `products_per_row` int(11) NOT NULL,
  `total_products` int(11) NOT NULL,
  `sort_by` varchar(255) NOT NULL,
  `slideshow` varchar(255) NOT NULL,
  `toplist` varchar(255) NOT NULL,
  `heading` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `icommerce_pagemanager` (`id`, `name`, `status`, `position`, `created_on`, `created_by`)
		VALUES (1, 'Page', '1', 0, '0000-00-00 00:00:00', 1);

INSERT INTO `icommerce_pagemanager_row` (`id`, `page_id`, `status`, `created_on`, `created_by`, `position`, `type`)
		VALUES (1, 1, '1', '0000-00-00 00:00:00', 0, 1, '1');

INSERT INTO `icommerce_pagemanager_item` (`id`, `page_id`, `row_id`, `filename`, `image_alt`, `title`, `image_text`, `link`, `link_target`, `status`, `created_on`, `created_by`, `position`, `page_content`, `type`, `visibility`, `category_id`, `products_per_row`, `total_products`, `sort_by`, `slideshow`, `toplist`, `heading`)
VALUES
	(1, 1, 1, '', '', '{$helper->getDefaultH1()}', '', '', '', '1', '2011-08-22 02:08:40', 1, 0, '', 'heading', '', 0, 0, 0, '', '', '', 'h1');


");

$installer->endSetup();