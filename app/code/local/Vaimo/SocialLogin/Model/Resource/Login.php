<?php

class Vaimo_SocialLogin_Model_Resource_Login extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('sociallogin/login', 'customer_id');
        $this->_isPkAutoIncrement = false;
    }
}
