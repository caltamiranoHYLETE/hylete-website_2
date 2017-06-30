<?php

class Ebizmarts_BakerlooBackup_Model_Handler_Factory {

    public function getHandler($params = array()){

        $helper = Mage::helper('bakerloo_backup');
        $destination = $helper->getCurrentStorage();

        $handler = Mage::getModel('bakerloo_backup/handler_' . $destination, $params);

        $credentials = $helper->getAccessCredentials();
        $credentials = json_decode(Mage::helper('core')->decrypt($credentials));

        //if dropbox configured, regenerate dropbox client
        if(strcmp($destination, 'dropbox') === 0){
            try{
                $handler->resetDbxClient($credentials->accessToken, $credentials->userId);
            }
            catch(Exception $e){
//                Mage::throwException("Please update your Dropbox credentials.");
            }
        }
        //if google drive configured, regenerate google drive client
        elseif(strcmp($destination, 'drive') === 0){
            try{
                $handler->resetDriveClient($credentials);
            }
            catch(Exception $e){
//                Mage::throwException("Please update your Google Drive credentials.");
            }
        }

        return $handler;
    }
}