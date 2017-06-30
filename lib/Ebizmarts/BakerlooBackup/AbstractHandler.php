<?php

namespace Ebizmarts\BakerlooBackup;

require_once(realpath('.') . "/lib/Ebizmarts/lib/google-sdk/Google/autoload.php");


require_once "BackupHandler.php";
require_once "Backup.php";

abstract class AbstractHandler implements BackupHandler {

    protected $_params = array();
    protected $_validFileTypes = array('application/zip');
    protected $_validFileExtensions = array('zip');

    public function validateBackup(Backup $file) {
        //validate type
        $type = $file->getMimeType();
        $extension = $file->getExtension();

        if(!in_array($type, $this->_validFileTypes) or !in_array($extension, $this->_validFileExtensions))
            throw new \Exception("File format not supported.");

        return true;
    }

    public function getPostedFile($key) {
        if(!isset($_FILES) or !isset($_FILES[$key]))
            throw new \Exception("Please provide a backup file.");

        $fileData = $_FILES[$key];
        $fileName = $this->fileStamp($fileData['name']);

        if(!file_exists($fileData['tmp_name']))
            throw new \Exception("Could not find uploaded file.");

        $backup = new Backup($fileData['tmp_name'], $fileName);
        $backup->setDeviceId($this->_params['device_id']);
        $backup->setDeviceName($this->_params['device_name']);

        $this->validateBackup($backup);

        return $backup;
    }

    public function fileStamp($fileName){
        $fileNameParts = explode('.', $fileName);
        $numParts = sizeof($fileNameParts);

        if($numParts > 1){
            $ext = $fileNameParts[$numParts - 1];
            unset($fileNameParts[$numParts - 1]);
            $fileName = implode('', $fileNameParts);
            return $fileName . '_' . time() . '.' . $ext;
        }
        else
            return $fileName . '_' . time();
    }
}