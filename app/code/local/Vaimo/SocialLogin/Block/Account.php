<?php

class Vaimo_SocialLogin_Block_Account extends Mage_Core_Block_Template
{
    public function getFacebookActivate()
    {
        return Mage::getStoreConfig('sociallogin/facebook/activate');
    }

    public function getGoogleActivate()
    {
        return Mage::getStoreConfig('sociallogin/google/activate');
    }

    public function getTwitterActivate()
    {
        return Mage::getStoreConfig('sociallogin/twitter/activate');
    }
}
