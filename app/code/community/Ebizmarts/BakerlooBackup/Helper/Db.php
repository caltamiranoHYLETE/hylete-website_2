<?php

class Ebizmarts_BakerlooBackup_Helper_Db extends Mage_Core_Helper_Abstract {

    public function saveFile($row){
        $fileRow = Mage::getModel('bakerloo_backup/files');
        $fileRow->setDeviceKey($row['deviceKey'])
            ->setDeviceName($row['deviceName'])
            ->setBackupFileName($row['fileName'])
            ->setStoreId($row['storeId'])
            ->setStorage($row['storage'])
            ->setUploadDate($row['uploadDate'])
            ->setBackupFileSize ($row['fileSize']);
        $fileRow->save();
    }

    public function getFilesWithDeviceName($fileNames){
        $files = array();
        $model = Mage::getModel('bakerloo_backup/files');

        foreach($fileNames as $name){
            $row = $model->getCollection()
                ->addFieldToFilter('backup_file_name', array('eq' => $name))
                ->getFirstItem();
            $rowData = $row->getData();

            if(!empty($rowData)){
                $files[] = array(
                    'deviceKey' => $row->getDeviceKey(),
                    'deviceName' => $row->getDeviceName(),
                    'storeId' => $row->getStoreId(),
                    'fileName' => $row->getBackupFileName(),
                    'uploadDate' => $row->getUploadDate(),
                    'fileSize' => $row->getFileSize()
                );
            }
        }

        return $files;
    }
}