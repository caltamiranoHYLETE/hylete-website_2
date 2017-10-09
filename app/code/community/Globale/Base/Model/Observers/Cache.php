<?php

use GlobalE\SDK\Core;

class Globale_Base_Model_Observers_Cache
{
    public function clearCache(Varien_Event_Observer $observer){

        $cache_type = $observer->getData('type');
        if($cache_type === 'globale'){
            Core\Cache::flush();
        }
    }

}