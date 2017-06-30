<?php
/** @var Icommerce_SlideshowManager_Model_Setup $installer */
$installer = $this;

$table = $installer->getTable('icommerce_slideshow_item');
$select = 'DESCRIBE ' . $table;
$existing_columns = $installer->getConnection()->fetchPairs($select);
if (!isset($existing_columns['hotspots'])) {
    $installer->startSetup();
    $installer->run("
    ALTER TABLE `{$table}`
  		ADD COLUMN `hotspots` longtext DEFAULT NULL;
");

    $installer->endSetup();
}
