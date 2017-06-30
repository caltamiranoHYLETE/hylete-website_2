<?php

namespace Ebizmarts\BakerlooBackup;

require_once(realpath('.') . "/lib/Ebizmarts/lib/dropbox-sdk/Dropbox/autoload.php");

use Dropbox;
use Dropbox\Client;

require_once "AbstractHandler.php";
require_once "BackupHandler.php";
require_once "Backup.php";

class DropboxHandler extends AbstractHandler {
    const APP_KEY    = "5g3uk3ilozfierk";
    const APP_SECRET = "yu9v5w00na705vp";

    private $_dbxClient;

    private $_webAuth;

    public function getAllFiles($dir = null){
        if(is_null($this->_dbxClient))
            throw new \Exception("No client has been configured.");

        $storeDir = "/" . $dir;
        $fileMetadata = $this->_dbxClient->getMetadataWithChildren($storeDir);
        return $this->_getFileNames($fileMetadata);
    }

    public function getFile($fileName, $storeId, $dir = null){
        $filePath = $this->_getFilePath($fileName, $storeId);
        $tmp = tempnam($filePath, "");
        $tmpFile = fopen($tmp, "w+b");
        $fileMetadata = $this->_dbxClient->getFile($filePath, $tmpFile);
        fclose($tmpFile);
        return file_get_contents($tmp);
    }

    public function _getFileNames($metadata){
        $paths = $this->_getFilePaths($metadata);

        $fileNames = array();
        foreach($paths as $path){
            $parts = explode('/', $path);
            $fileNames[] = $parts[count($parts)-1];
        }

        return $fileNames;
    }

    public function _getFilePaths($metadata){
        $files = array();
        if(!is_null($metadata)) {
            try {
                if (array_key_exists('contents', $metadata)) {
                    $tmp = $this->_getFilePaths($metadata['contents']);
                    $files = array_merge($files, $tmp);
                } else {
                    foreach ($metadata as $fileData) {
                        if (is_array($fileData)) {
                            if (array_key_exists('contents', $fileData)) {
                                $tmp = $this->_getFilePaths($fileData['contents']);
                                $files = array_merge($files, $tmp);
                            } else {
                                $files[] = $fileData['path'];
                            }
                        } else
                            $files[] = $metadata['path'];
                    }
                }
            } catch (\Exception $e) {
                //log exception
                throw new \Exception("Metadata parsing error.");
            }
        }
        return $files;
    }
    public function _getFilePath($fileName, $storeId){
        return sprintf('/%s/%s', $storeId, $fileName);
    }

    public function uploadFile($file, $storeId, $dir = null){
        $f = fopen($file->getFilePath(), "rb");
        $fileName = DIRECTORY_SEPARATOR . $storeId . DIRECTORY_SEPARATOR . $file->getName();
        $result = $this->_dbxClient->uploadFile($fileName, Dropbox\WriteMode::add(), $f);
        fclose($f);
        return $result;
    }

    public function getAuthorizeUrl(){
        if(is_null($this->_webAuth))
            $this->_getWebAuth();

        $authorizeUrl = $this->_webAuth->start();
        return $authorizeUrl;
    }

    public function authorize($code){
        if(is_null($this->_webAuth))
            $this->_getWebAuth();

        try {
            $accessData =  $this->_webAuth->finish($code);
            $this->setDbxClient($accessData);
            return $accessData;
        }
        catch(\Exception $e){
            //@TODO: log exception
            throw new \Exception("Can't validate authorization code");
        }
    }

    public function resetDbxClient($accessToken, $userId){
        $this->_dbxClient = new Client($accessToken, $userId);
    }

    public function setDbxClient($client){
        $this->_dbxClient = $client;
    }
    public function getDbxClient(){
        return $this->_dbxClient;
    }

    public function setWebAuth(Dropbox\WebAuth $webAuth){
        $this->_webAuth = $webAuth;
    }
    public function getWebAuth(){
        return $this->_webAuth;
    }

    private function _getWebAuth(){
        $credentials = array(
            "key" => self::APP_KEY,
            "secret" => self::APP_SECRET
        );
        $appInfo = Dropbox\AppInfo::loadFromJson($credentials);

        $this->_webAuth = new Dropbox\WebAuthNoRedirect($appInfo, "TEST/1.0");
    }

    public function validateFile($fileName){
        return $this;
    }
}