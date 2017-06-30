<?php

$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `{$installer->getTable('icommerce_slideshow_item')}`
	ADD COLUMN `image_text_tablet` text NOT NULL,
	ADD COLUMN `image_text_phone` text NOT NULL;
");

$installer->endSetup();