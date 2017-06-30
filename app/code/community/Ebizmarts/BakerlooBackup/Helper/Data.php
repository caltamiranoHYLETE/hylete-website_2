<?php

class Ebizmarts_BakerlooBackup_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getConfigPath(){
        return 'bakerloo_backup/';
    }

    public function getDestinationPath(){
        return $this->getConfigPath() . 'destination';
    }

    public function getDropboxAccessCodePath(){
        return 'bakerloo_backup/dropbox_backup/access_code';
    }

    public function getDriveAccessCodePath(){
        return 'bakerloo_backup/drive_backup/access_code';
    }

    public function getAccessCodePath(){
        return 'bakerloo_backup/storage/access_code';
    }

    public function getAccessCredentialsPath(){
        return $this->getConfigPath() . 'access_credentials';
    }

    public function getAccessCredentials(){
        return Mage::getModel('core/config_data')
            ->getCollection()
            ->addFieldToFilter('path', array('eq' => $this->getAccessCredentialsPath()))
            ->getFirstItem()
            ->getValue();
    }

    public function getLocalDir(){
        return 'var/pos_orders_backup';
    }

    public function getBackupHeader(){
        return 'pos_orders_backup';
    }

    public function getCurrentStorage(){
        //backup config path
        $destinationPath = $this->getDestinationPath();

        //identify backup destination
        $destinationItem = Mage::getModel('core/config_data')->getCollection()->addFieldToFilter('path', array('eq' => $destinationPath))->getFirstItem();

        if(is_null($destinationItem) or is_null($destinationItem->getValue()))
            $destination = 'magento';
        else
            $destination = $destinationItem->getValue();

        return $destination;
    }
}