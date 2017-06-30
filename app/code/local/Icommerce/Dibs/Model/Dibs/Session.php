<?php

class Icommerce_Dibs_Model_Dibs_Session extends Mage_Core_Model_Session_Abstract
{
    public function __construct()
    {
        $this->init('dibs');
    }
}
