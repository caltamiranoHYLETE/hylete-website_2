<?php

namespace Ebizmarts\BakerlooBackup;

require_once "AbstractHandler.php";

class LocalHandler extends AbstractHandler {

    public function getAllFiles($dir = null){
        $backupDirPath = $this->_getDirPath($dir);

        if(!is_dir($backupDirPath))
            mkdir($backupDirPath);

        $files = array();
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($backupDirPath));
        foreach($iterator as $dirItem){
            if(is_file($dirItem))
                $files[] = $dirItem->getFilename();
        }

        return $files;
    }

    public function getFile($fileName, $storeId = null, $dir = null){
        if(is_null($storeId))
            throw new \Exception("Please specify a store ID.");

        $backupDirPath = $this->_getDirPath($dir);
        $filePath = realpath($backupDirPath . DIRECTORY_SEPARATOR . $storeId . DIRECTORY_SEPARATOR . $fileName);

        if(!is_dir($backupDirPath) or !is_file($filePath))
            throw new \Exception("Could not find specified file.");

        return file_get_contents($filePath);
    }

    public function uploadFile($file, $storeId, $dir = null){
        $fileName = $file->getName();

        $backupDirPath = $this->_getDirPath($dir);
        $storePath = realpath($backupDirPath . DIRECTORY_SEPARATOR . $storeId);
        $filePath = $storePath . DIRECTORY_SEPARATOR . $fileName;

        if (!is_dir($backupDirPath))
            if(mkdir($backupDirPath) === false)
                throw new \Exception("Failed to create backup directory.");
        if(!is_dir($storePath))
            if(mkdir($storePath) == false)
                throw new \Exception("Failed to create store backup directory.");
        if(is_file($filePath))
            throw new \Exception("A backup file with this name already exists for this store.");

        $saved = file_put_contents($filePath, $file->getContents());

        if($saved === false)
            throw new \Exception("Backup file could not be saved.");

        return array(
            'deviceId' => $file->getDeviceId(),
            'deviceName' => $file->getDeviceName(),
            'fileName' => $fileName,
            'storeId' => $storeId,
            'fileSize' => $file->getFileSize()
        );
    }

    private function _getDirPath($dir){
        if(is_null($dir))
            $backupDirPath = getcwd();
        else
            $backupDirPath = getcwd() . DIRECTORY_SEPARATOR . $dir;

        return realpath($backupDirPath);
    }
}