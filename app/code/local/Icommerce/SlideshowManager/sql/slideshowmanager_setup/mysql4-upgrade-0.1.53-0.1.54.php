<?php

$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `{$installer->getTable('icommerce_slideshow_item')}`
  ADD COLUMN `title_link` text,
  ADD COLUMN `button_1_title` text,
  ADD COLUMN `button_2_title` text,
  ADD COLUMN `button_3_title` text,
  ADD COLUMN `border` varchar(255) DEFAULT NULL,
  ADD COLUMN `button_1_title_link` text,
  ADD COLUMN `button_2_title_link` text,
  ADD COLUMN `button_3_title_link` text,
  ADD COLUMN `subtitle` text,
  ADD COLUMN `title_position` varchar(255) DEFAULT NULL,
  ADD COLUMN `button_color` varchar(255) DEFAULT NULL,
  ADD COLUMN `text_color` varchar(255) DEFAULT NULL;
");

$installer->endSetup();
