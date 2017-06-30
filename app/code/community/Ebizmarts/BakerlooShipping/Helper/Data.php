<?php

class Ebizmarts_BakerlooShipping_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getBakerlooShippingMethods($store = null) {

        $methods = array();

        $carriers = Mage::getStoreConfig('carriers', $store);

        if(is_array($carriers)) {
            foreach($carriers as $_carrierCode => $_carrier) {

                if( 1 !== preg_match('/^bakerloo_/i', $_carrierCode) ) {
                    continue;
                }

                $carrierConfig = Mage::getStoreConfig('carriers/' . $_carrierCode, $store);
                if (!$carrierConfig) {
                    continue;
                }

                if(((int)$carrierConfig['active']) === 1 && isset($carrierConfig['title'])) {
                    $methods []= array(
                                       'code'       => (string)$_carrierCode,
                                       'label'      => (string)$carrierConfig['title'],
                                       'sort_order' => (int)$carrierConfig['sort_order'],
                                      );
                }
            }
        }

        return $methods;
    }

    public function disableShippingMethods($store = null) {
        $carriers = Mage::getStoreConfig('carriers', $store);

        if(is_array($carriers)) {
            $store = Mage::app()->getStore($store);

            foreach($carriers as $code => $carrier) {
                if( 1 === preg_match('/^bakerloo_/i', $code))
                    continue;

                if($carrier['active'] == 0)
                    continue;
                
                $store->setConfig('carriers/' . $code . '/active', 0);
            }
        }
    }

}