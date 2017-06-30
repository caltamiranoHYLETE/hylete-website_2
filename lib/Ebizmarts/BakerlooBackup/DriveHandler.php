<?php

namespace Ebizmarts\BakerlooBackup;

require_once(realpath('.') . "/lib/Ebizmarts/lib/google-sdk/Google/autoload.php");

require_once "AbstractHandler.php";
require_once "BackupHandler.php";
require_once "Backup.php";


class DriveHandler extends AbstractHandler {

    const APPLICATION_NAME = "POS Backups";
    private $_scopes = array(
        \Google_Service_Drive::DRIVE,
        \Google_Service_Drive::DRIVE_METADATA,
        \Google_Service_Drive::DRIVE_FILE,
        \Google_Service_Drive::DRIVE_APPDATA
    );

    protected $_credentials = '{"installed":{"client_id":"1048865549058-od1s7b7q46hj9h3id031551hle6fn1dq.apps.googleusercontent.com","auth_uri":"https://accounts.google.com/o/oauth2/auth","token_uri":"https://accounts.google.com/o/oauth2/token","auth_provider_x509_cert_url":"https://www.googleapis.com/oauth2/v1/certs","client_secret":"XsTjedx62-dmjD5ugECMeFFp","redirect_uris":["urn:ietf:wg:oauth:2.0:oob","http://localhost"]}}';

    private $_driveClient;
    private $_folderName = "POS Backups";

    public function getAllFiles($dir = null){
        if(!isset($this->_driveClient))
            throw new \Exception("No drive client configured.");

        $files = array();

        $service = new \Google_Service_Drive($this->_driveClient);
        $storeFolder = $this->_getStoreFolder($service, $dir);
        if(!is_null($storeFolder))
            $files = $this->_getFileNames($storeFolder->getId(), $service);

        return $files;
    }

    public function _getFileNames($folderId, \Google_Service_Drive $service){
        $files = array();

        $posFiles = $service->children->listChildren($folderId)->getItems();
        foreach($posFiles as $fileReference) {
            $file = $service->files->get($fileReference->getId());
            $files[] = $file->getOriginalFilename();
        }

        return $files;
    }

    public function getFile($fileName, $storeId, $dir = null){
        if(!isset($this->_driveClient))
            throw new \Exception("No drive client configured.");

        $file = null;

        $service = new \Google_Service_Drive($this->_driveClient);

        $posFolderId = $this->_getStoreFolder($service, $storeId)->getId();
        if (!is_null($posFolderId)){
            $query = sprintf("title = '%s' and '%s' in parents", $fileName, $posFolderId);
            $foundFiles = $service->files->listFiles(array('q' => $query))->getItems();
            if(is_array($foundFiles) and !empty($foundFiles))
                $file = $this->downloadFile($foundFiles[0], $service);
        }

        return $file;
    }

    public function downloadFile(\Google_Service_Drive_DriveFile $file, \Google_Service_Drive $service){

        $downloadUrl = $file->getDownloadUrl();
        if(!$downloadUrl)
            $downloadUrl = $file->getWebContentLink();

        $request = new \Google_Http_Request($downloadUrl, 'GET', null, null);
        $httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
        if($httpRequest->getResponseHttpCode() == 200)
            return $httpRequest->getResponseBody();
        else
            return null;
    }

    public function uploadFile($file, $storeId, $dir = null){
        if(!isset($this->_driveClient))
            throw new \Exception("No drive client configured.");
        $service = new \Google_Service_Drive($this->_driveClient);

        if(!file_exists($file->getFilePath()))
            throw new \Exception("Invalid file path.");
        $fileData = file_get_contents($file->getFilePath());

        $driveFile = new \Google_Service_Drive_DriveFile();
        $driveFile->setTitle($file->getName());
        $driveFile->setMimeType($file->getMimeType());
        $driveFile->setParents(array($this->_getStoreFolder($service, $storeId)));

        $result = $service->files->insert($driveFile, array('uploadType' => 'multipart', 'data' => $fileData, 'mimeType' => 'application/zip'));
        return $result;
    }

    private function _getPosFolder(\Google_Service_Drive $service){
        $q = sprintf("title = '%s'", $this->_folderName);
        $folders = $service->files->listFiles(array('q' => $q));

        if(empty($folders))
            $posFolder = $this->_createPosFolder($service);
        else
            $posFolder = $folders[0];
        return $posFolder;
    }
    private function _createPosFolder(\Google_Service_Drive $service){
        $folder = new \Google_Service_Drive_DriveFile();
        $folder->setTitle($this->_folderName);
        $folder->setMimeType('application/vnd.google-apps.folder');
        $folder->setParents($service->files->get("root"));
        return $service->files->insert($folder);
    }
    private function _getStoreFolder(\Google_Service_Drive $service, $storeId){
        $posFolder = $this->_getPosFolder($service);
        $q = sprintf("title = '%d' and '%s' in parents", $storeId, $posFolder->getId());
        $folders = $service->files->listFiles(array('q' => $q))->getItems();

        if(empty($folders)){
            $newFolder = new \Google_Service_Drive_DriveFile();
            $newFolder->setTitle($storeId);
            $newFolder->setMimeType('application/vnd.google-apps.folder');
            $newFolder->setParents(array($posFolder));
            $storeFolder = $service->files->insert($newFolder);
        }
        else
            $storeFolder = $folders[0];
        return $storeFolder;
    }

    public function validateFile($fileName){
        return $this;
    }

    public function getAuthorizeUrl(){
        return $this->getClient()->createAuthUrl();
    }
    public function authorize($code){
        if(!isset($this->_driveClient))
            $this->_getDriveClient();

        try{
            $accessData = $this->_driveClient->authenticate($code);
            $this->_driveClient->setAccessToken($accessData);
            return $accessData;
        }
        catch(\Exception $e) {
            //@TODO: log exception
            throw new \Exception("Can't validate authorization code");
        }
    }

    public function getClient(){
        if(!isset($this->_driveClient))
            $this->_getDriveClient();
        return $this->_driveClient;
    }
    private function _getDriveClient(){
        $client = new \Google_Client();
        $client->setApplicationName(self::APPLICATION_NAME);
        $client->setScopes($this->_scopes);
        $client->setAccessType('offline');
        $client->setAuthConfig($this->_credentials);

        $this->_driveClient = $client;
    }
    public function resetDriveClient($accessToken){
        if(!isset($this->_driveClient))
            $this->_getDriveClient();
        $this->_driveClient->setAccessToken($accessToken);
    }

    public function setClient($client){
        $this->_driveClient = $client;
    }
}