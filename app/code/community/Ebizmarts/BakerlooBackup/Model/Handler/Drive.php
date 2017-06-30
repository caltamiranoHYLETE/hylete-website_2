<?php

use Ebizmarts\BakerlooBackup\Backup;
use Ebizmarts\BakerlooBackup\DriveHandler;
require_once "Ebizmarts/BakerlooBackup/DriveHandler.php";

class Ebizmarts_BakerlooBackup_Model_Handler_Drive extends DriveHandler {

    public function __construct($params = array()){
        $this->_params = $params;
    }

    public function getById($identifier, $storeId) {

        $dir = Mage::helper('bakerloo_backup')->getLocalDir();
        return $this->getFile($identifier, $storeId, $dir);

    }

    public function post(){
        $backup = $this->getPostedFile(Mage::helper('bakerloo_backup')->getBackupHeader());
        $result = $this->uploadFile($backup, $this->_params['store_id']);

        $row = array();
        if($result){
            $row = array(
                'deviceKey' => $this->_params['device_id'],
                'deviceName' => $this->_params['device_name'],
                'fileName' => $backup->getName(),
                'storeId' => $this->_params['store_id'],
                'storage' => 'drive',
                'uploadDate' => Mage::getModel('core/date')->gmtDate()
            );

            Mage::helper('bakerloo_backup/db')->saveFile($row);
        }

        return $row;
    }

    public function saveUserAccess($accessToken){
        $encryptedCredentials = Mage::helper('core')->encrypt(json_encode($accessToken));
        $coreConfig = Mage::getModel('core/config');
        $coreConfig->saveConfig(Mage::helper('bakerloo_backup')->getAccessCredentialsPath(), $encryptedCredentials, 'stores', 0);
    }
}