<?php

/**
 * Created by PhpStorm.
 * User: Mykhaylo.Kozlovskyy
 * Date: 6/9/2017
 * Time: 5:42 PM
 */

use GlobalE\SDK\Core\Settings;

trait Globale_EFPC_Trait_AddCustomerInfoToPageId
{
    /**
     * Rewrite of FPC method that generates cache key, in a way that he will include Globale vars into it:
     * Country, currency, culture.
     * @param Enterprise_PageCache_Model_Processor $processor
     * @return string
     */
    public function getPageIdWithoutApp(Enterprise_PageCache_Model_Processor $processor)
    {
        $pageId = parent::getPageIdWithoutApp($processor);
        return $this->_appendCustomerInfoToPageId($pageId);
    }

    protected function _appendCustomerInfoToPageId($pageId)
    {
        $this->_initConfig();
        /** @var \GlobalE\SDK\Models\Customer $customer */
        $customer = \GlobalE\SDK\Models\Customer::getSingleton();
        if (!$customer->hasCustomerCookie()) {
            $customer->setIp();
            $customerInfo = $customer->getInfo();
        }else{
            $customerInfo = $customer->fetchCustomerCookie();
        }

        $pageId = $pageId . '_' . md5(serialize($customerInfo));
        return $pageId;
    }

    protected function _initConfig(){
        $resource = new Mage_Core_Model_Resource();
        $conn = $resource->getConnection('core_read');
        $config = $conn->fetchAssoc('select path, value from '
            .$resource->getTableName('core/config_data').
            ' where path LIKE "globale_%"');

        Settings::set("MerchantID", $config['globale_settings/api_settings/merchant_id']['value']);
        Settings::set("MerchantGUID", $config['globale_settings/api_settings/merchant_guid']['value']);
        Settings::set("API.BaseUrl", $config['globale_settings/api_settings/api_base_url']['value']);
        Settings::set("Base.Currency", $config['globale_settings/browsing_settings/base_currency']['value']);

    }

    //'API.BaseUrl'
}