<?php 
class Vaimo_Gotoadmin_Helper_Data extends Mage_Core_Helper_Abstract {
 
    public function isIpAllowed()
    {
        $allowed = true;
        if ($allowIps = Mage::getStoreConfig('gotoadmin/general/allow_ips')){
            $allowed = (strstr($allowIps . ',127.0.0.1', Mage::helper('core/http')->getRemoteAddr())) ? true : false;
        }
        return $allowed;
    }
    

    public function isAllowed()
    {
        return (Mage::getStoreConfig('gotoadmin/general/active') && $this->isIpAllowed()) ? true : false;
    } 
}