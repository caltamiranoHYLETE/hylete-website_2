<?php

$installer = $this;
$installer->startSetup();

$backupTable = $installer->getTable('bakerloo_backup/files');

$installer->getConnection()->addColumn($backupTable, 'backup_file_size', "int(11) unsigned NOT NULL default '0'");
$backupDirPath = Mage::getBaseDir() . DS . Mage::helper('bakerloo_backup')->getLocalDir();

//update backup file sizes
$select = $installer->getConnection()
    ->select()
    ->from(array('files' => $backupTable))
    ->where('storage = ?', 'magento')
    ->where('backup_file_size = ?', 0);

$rows = $installer->getConnection()->fetchAll($select);
foreach ($rows as $row) {

    $storePath = $backupDirPath . DS . $row['store_id'];
    $filePath = $storePath . DS . $row['backup_file_name'];

    $where = "id = {$row['id']}";

    if (file_exists($filePath)) {
        $fileSize = filesize($filePath);

        $bind = array('backup_file_size' => $fileSize);
        $installer->getConnection()->update($backupTable, $bind, $where);

    }
    else {
        $installer->getConnection()->delete($backupTable, $where);
    }
}

$installer->endSetup();