<?php

class Ebizmarts_BakerlooBackup_Model_Observer_Main {

    public function setDestination(){
        $storage = Mage::getStoreConfig('bakerloo_backup/storage/enabled', Mage::app()->getStore());
        $storageOptions = Mage::getModel('bakerloo_backup/system_config_source_storage')->toArray();
        Mage::getModel('core/config')->saveConfig(Mage::helper('bakerloo_backup')->getDestinationPath(), strtolower($storageOptions[$storage]), 'stores', 0);
    }

    public function saveUserAccess(){
        $helper = Mage::helper('bakerloo_backup');
        $config = Mage::getModel('core/config_data');

        //get saved access code
        $accessCodeItem = $config->getCollection()
            ->addFieldToFilter('path', array('eq' => $helper->getAccessCodePath()))
            ->getFirstItem();

        if($accessCodeItem->getConfigId()){// and !is_null($accessCodeItem)) {
            $accessCode = $accessCodeItem->getValue();

            //update access credentials only if a code has been provided
            if (isset($accessCode) and !is_null($accessCode)) {

                //update webAuth
                $handler = Mage::getModel('bakerloo_backup/handler_factory')->getHandler();

                //try to authorize user
                $verification = $handler->authorize($accessCode);

                //save user data
                if (isset($verification))
                    $handler->saveUserAccess($verification);
                else
                    Mage::throwException("Failed to save user credentials. ");
            }

            $accessCodeItem->setValue("")
                ->save();
        }
    }
}