<?php
class Globale_Base_Model_Adminhtml_Environment extends Mage_Core_Model_Config_Data
{
    
    const GLOBALE_QA         = 0;
    const GLOBALE_STAGING    = 1;
    const GLOBALE_PRODUCTION = 2;

    const QA_LABEL    = 'QA';
    const STAGE_LABEL = 'Staging';
    const PROD_LABEL  = 'Production';

    const QA_API_BASE_URL            = 'https://connect-qa.bglobale.com/';
    const QA_CLIENT_BASE_URL         = 'https://qa.bglobale.com/';
    const QA_GEM_BASE_URL            = '//gepi.bglobale.com/';

    const STAGE_API_BASE_URL         = 'https://connect2.bglobale.com/';
    const STAGE_CLIENT_BASE_URL      = 'https://www2.bglobale.com/';
    const STAGE_GEM_BASE_URL         = '//stgepi.bglobale.com/';

    const PROD_API_BASE_URL          = 'https://api.global-e.com/';
    const PROD_CLIENT_BASE_URL       = 'https://web.global-e.com/';
    const PROD_GEM_BASE_URL          = '//gepi.global-e.com/';


    /**
     * Processing object before save data. Saving Global-e Urls as Settings.
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        switch ($this->getValue()){
            case self::GLOBALE_QA:
                Mage::getConfig()->saveConfig(Globale_Base_Model_Settings::API_BASE_URL, self::QA_API_BASE_URL);
                Mage::getConfig()->saveConfig(Globale_Base_Model_Settings::CLIENT_BASE_URL, self::QA_CLIENT_BASE_URL);
                Mage::getConfig()->saveConfig(Globale_Base_Model_Settings::GEM_BASE_URL, self::QA_GEM_BASE_URL);
                break;
            case self::GLOBALE_STAGING:
                Mage::getConfig()->saveConfig(Globale_Base_Model_Settings::API_BASE_URL, self::STAGE_API_BASE_URL);
                Mage::getConfig()->saveConfig(Globale_Base_Model_Settings::CLIENT_BASE_URL, self::STAGE_CLIENT_BASE_URL);
                Mage::getConfig()->saveConfig(Globale_Base_Model_Settings::GEM_BASE_URL, self::STAGE_GEM_BASE_URL);
                break;
            case self::GLOBALE_PRODUCTION:
                Mage::getConfig()->saveConfig(Globale_Base_Model_Settings::API_BASE_URL, self::PROD_API_BASE_URL);
                Mage::getConfig()->saveConfig(Globale_Base_Model_Settings::CLIENT_BASE_URL, self::PROD_CLIENT_BASE_URL);
                Mage::getConfig()->saveConfig(Globale_Base_Model_Settings::GEM_BASE_URL, self::PROD_GEM_BASE_URL);
                break;
        }
        return $this;
    }
}