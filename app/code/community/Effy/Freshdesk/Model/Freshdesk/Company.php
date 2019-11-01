<?php
/**
 * Effy Freshdesk extension
 *
 * @category    Effy_Freshdesk
 * @package     Effy_Freshdesk
 * @copyright   Copyright (c) 2014 Effy. (http://www.effy.com)
 * @license     http://www.effy.com/disclaimer.html
 */

/**
 * Class Effy_Freshdesk_Model_Freshdesk_Company
 *
 */
class Effy_Freshdesk_Model_Freshdesk_Company extends Effy_Freshdesk_Model_Freshdesk_Abstract
{
    const URL_COMPANY_OPTIONS = 'api/v2/companies';

    protected function _construct()
    {
        parent::_construct();

        $this->_init('effy_freshdesk/freshdesk_company');
    }

    public function getCompanies() {
        $response = $this->setUrlSuffix(self::URL_COMPANY_OPTIONS)
            ->request(Zend_Http_Client::GET);
        if (is_null($response) || !($response instanceof Zend_Http_Response)) {
            throw new Effy_Freshdesk_Exception('Wrong response object');
        }

        if ($response->getStatus() != 200) {
            Mage::log($response);
            throw new Effy_Freshdesk_Exception($response->getMessage());
        }
        $companiesJson = trim($response->getRawBody());
        $response = array();
        try {
            $response = Zend_Json::decode($companiesJson);
        }
        catch(Exception $e) {
            Mage::log('Error occured while generating companies');
            Mage::log($e->message());
        }
        return $response;
    }
}