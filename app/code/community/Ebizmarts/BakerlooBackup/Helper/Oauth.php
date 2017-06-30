<?php

class Ebizmarts_BakerlooBackup_Helper_Oauth extends Mage_Core_Helper_Abstract {

    public function getDbxOAuthUrl(){
        $handler = Mage::getModel('bakerloo_backup/handler_dropbox');
        return $handler->getAuthorizeUrl();
    }

    public function getDriveOAuthUrl(){
        $handler = Mage::getModel('bakerloo_backup/handler_drive');
        return $handler->getAuthorizeUrl();
    }

    public function getButtonUrl(){
        return $this->getOAuthUrl();
    }
}