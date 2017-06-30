<?php
/** @var Icommerce_SlideshowManager_Model_Setup $installer */
$installer = $this;

$table = $installer->getTable('icommerce_slideshow_item');
$select = 'DESCRIBE ' . $table;
$existing_columns = $installer->getConnection()->fetchPairs($select);
if (!isset($existing_columns['product_id'])) {
    $installer->startSetup();
    $installer->run("ALTER TABLE `{$table}` ADD COLUMN `product_id` INT(11) NOT NULL DEFAULT 0;");

    $installer->endSetup();
}
