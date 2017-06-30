<?php
use Ebizmarts\BakerlooBackup\Backup;
use Ebizmarts\BakerlooBackup\DropboxHandler;
require_once "Ebizmarts/BakerlooBackup/DropboxHandler.php";

class Ebizmarts_BakerlooBackup_Model_Handler_Dropbox extends DropboxHandler {

    public function __construct($params = array()){
        $this->_params = $params;
    }

    public function saveUserAccess($accessData){
        list($accessToken, $userId) = $accessData;
        $this->_saveUserAccess($accessToken, $userId);
    }

    private function _saveUserAccess($accessToken, $userId){
        $credentials = array(
            'accessToken' => $accessToken,
            'userId' => $userId
        );
        $encryptedCredentials = Mage::helper('core')->encrypt(json_encode($credentials));
        $coreConfig = Mage::getModel('core/config');
        $coreConfig ->saveConfig(Mage::helper('bakerloo_backup')->getAccessCredentialsPath(), $encryptedCredentials, 'stores', 0);
    }

    public function getById($identifier, $storeId) {

        $dir = Mage::helper('bakerloo_backup')->getLocalDir();
        return $this->getFile($identifier, $storeId, $dir);

    }

    public function post(){
        $backup = $this->getPostedFile(Mage::helper('bakerloo_backup')->getBackupHeader());
        $result = $this->uploadFile($backup, $this->_params['store_id']);

        $row = array(
            'deviceKey' => $this->_params['device_id'],
            'deviceName' => $this->_params['device_name'],
            'fileName' => $backup->getName(),
            'storeId' => $this->_params['store_id'],
            'storage' => 'dropbox',
            'uploadDate' => Mage::getModel('core/date')->gmtDate()
        );

        Mage::helper('bakerloo_backup/db')->saveFile($row);

        return $row;
    }
}