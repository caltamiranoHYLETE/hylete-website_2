<?php

class Ebizmarts_BakerlooRestful_Model_Webhook {

    public static $_events = array(
        'sales_order_invoice_save_after'    => 'invoices',
        'sales_order_creditmemo_save_after' => 'creditnotes',
        'customer_save_after'               => 'customers',
        'catalog_product_save_after'        => 'products',
        'catalog_category_save_after'       => 'categories',
    );

    public function doWebhook(Varien_Event_Observer $observer) {

        //Check if webhooks are enabled
        $webhooksEnabled = (boolean)$this->helper()->config("general/webhooks_enabled");
        if($webhooksEnabled === false) {
            return $this;
        }

        //Get event data object
        $object = $observer->getEvent()->getDataObject();

        //Get event inforamtion
        $event  = $observer->getEvent();

        //Object store_id
        $storeId = (int)$object->getStoreId();

        if(0 === $storeId) {
            $storeId = 1;
        }

        //Build postdata body
        $postData = array(
                          'resource' => $this->_entityCode($event->getName()),
                          'id'       => $object->getId()
                         );

        //Get bakerloo saas webhook end point
        $postUrl = (string)Mage::getConfig()->getNode('default/bakerloorestful/webhook_url');

        //Start a new http client and POST request, timeout is 5 seconds.
        $httpClient = new Varien_Http_Client;
        $httpClient
                    ->setUri($postUrl)
                    ->setHeaders($this->helper()->getApiKeyHeader(), $this->helper()->getApiKey($storeId))
                    ->setParameterPost($postData)
                    ->setConfig(array('timeout' => 5, 'useragent' => $this->helper()->getUserAgent()))
                    ->request(Zend_Http_Client::POST);

        //Debug
        Mage::log($httpClient);

        return $this;
    }

    private function _entityCode($eventName) {

        $code = '';

        if(isset(self::$_events[$eventName])) {
            $code = self::$_events[$eventName];
        }

        return $code;

    }

    public function helper() {
        return Mage::helper('bakerloo_restful');
    }
}