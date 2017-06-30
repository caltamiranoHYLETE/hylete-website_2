<?php
namespace Ebizmarts\BakerlooBackup;

class Backup {
    private $_filePath;
    private $_fileName;
    private $_deviceName;
    private $_deviceId;
    private $_fileSize;

    public function __construct($filePath, $fileName = null){
        $this->_filePath = $filePath;
        $this->_fileName = $fileName;
        $this->_fileSize = filesize($this->_filePath);
    }

    public function getName(){
        if(is_null($this->_fileName)) {
            $path = explode(DIRECTORY_SEPARATOR, $this->_filePath);
            return $path[count($path) - 1];
        }
        else
            return $this->_fileName;
    }

    public function getDeviceName(){
        return $this->_deviceName;
    }

    public function getDeviceId(){
        return $this->_deviceId;
    }

    public function getFilePath(){
        return $this->_filePath;
    }

    public function setDeviceName($deviceName){
        $this->_deviceName = $deviceName;
    }

    public function setDeviceId($deviceId){
        $this->_deviceId = $deviceId;
    }

    public function getMimeType(){
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $type = $finfo->file($this->_filePath);
        return $type;
    }

    public function getContents(){
        try{
            return file_get_contents($this->_filePath);
        }
        catch(\Exception $e){
            throw new \Exception("Could not get file contents");
        }
    }

    public function getFileSize(){
        return $this->_fileSize;
    }

    public function getExtension() {
        $name = $this->getName();

        $nameParts = explode('.', $name);

        return $nameParts[count($nameParts) - 1];
    }
}