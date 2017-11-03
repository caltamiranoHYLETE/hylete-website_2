<?php

class Devweb_PaypalRedirect_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getRedirectUrl()
    {
        return Mage::getUrl((version_compare(Mage::getVersion(), '1.9.1.0', '>=') ? 'payflow' : 'paypaluk') . '/express/edit');
    }
}
