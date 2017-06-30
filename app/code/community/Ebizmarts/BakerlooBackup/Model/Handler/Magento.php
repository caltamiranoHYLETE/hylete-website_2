<?php

require_once 'Ebizmarts/BakerlooBackup/Backup.php';
require_once 'Ebizmarts/BakerlooBackup/LocalHandler.php';

use Ebizmarts\BakerlooBackup;
use Ebizmarts\BakerlooBackup\Backup;


class Ebizmarts_BakerlooBackup_Model_Handler_Magento extends BakerlooBackup\LocalHandler {

    public function __construct($params = array()){
        $this->_params = $params;
    }

    public function getById($identifier, $storeId) {
        $dir = Mage::helper('bakerloo_backup')->getLocalDir();
        return $this->getFile($identifier, $storeId, $dir);
    }

    public function post(){
        $helper = Mage::helper('bakerloo_backup');
        $backup = $this->getPostedFile($helper->getBackupHeader());
        $result = $this->uploadFile($backup, $this->_params['store_id'], $helper->getLocalDir());

        $row = array(
            'deviceKey' => $backup->getDeviceId(),
            'deviceName' => $backup->getDeviceName(),
            'fileName' => $backup->getName(),
            'storeId' => $this->_params['store_id'],
            'storage' => 'magento',
            'uploadDate' => Mage::getModel('core/date')->gmtDate(),
            'fileSize' => $backup->getFileSize()
        );

        Mage::helper('bakerloo_backup/db')->saveFile($row);

        return $row;
    }

}