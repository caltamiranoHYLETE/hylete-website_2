<?php

class Vaimo_SocialLogin_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_SOCIALLOGIN_USE_OAUTH = 'sociallogin/google/use_oauth';
    const XML_GOOGLE_CLIEND_ID      = 'sociallogin/google/client_id';
    const XML_GOOGLE_CLIEND_SECRET  = 'sociallogin/google/client_secret';
    const XML_GOOGLE_APP_NAME       = 'sociallogin/google/app_name';

    protected $_loggingEnabled;
    protected $_errorSessionContext = 'customer/session';

    public function __construct()
    {
        $this->_loggingEnabled = Mage::getStoreConfigFlag('sociallogin/developer/log');
        if (Mage::getStoreConfigFlag('sociallogin/developer/is_core_session_error')) {
            $this->_errorSessionContext = 'core/session';
        }
    }

    public function isGoogleEnabled()
    {
        return Mage::getStoreConfigFlag('sociallogin/google/activate');
    }

    public function googleUseOAuth()
    {
        return Mage::getStoreConfigFlag(self::XML_SOCIALLOGIN_USE_OAUTH);
    }

    public function googleClientId()
    {
        return Mage::getStoreConfig(self::XML_GOOGLE_CLIEND_ID);
    }

    public function googleClientSecret()
    {
        return Mage::getStoreConfig(self::XML_GOOGLE_CLIEND_SECRET);
    }

    public function googleAppName()
    {
        return Mage::getStoreConfig(self::XML_GOOGLE_APP_NAME);
    }

    public function isFacebookEnabled()
    {
        return Mage::getStoreConfigFlag('sociallogin/facebook/activate');
    }

    public function isTwitterEnabled()
    {
        return Mage::getStoreConfigFlag('sociallogin/twitter/activate');
    }

    public function getGraphApiVersion()
    {
        return Mage::getStoreConfig('sociallogin/facebook/api_version');
    }

    public function getRedirectUrl($redirectUrl = '', $successful = true)
    {
        $holder = new Varien_Object();
        $holder->setParams(array());

        Mage::dispatchEvent('sociallogin_redirect_before', array('transport' => $holder, 'is_successful' => (bool )$successful));

        $holder->setUrl($holder->getUrl() ? $holder->getUrl() : $redirectUrl);
        if (!is_array($holder->getParams())) {
            $holder->setParams(array());
        }
        return $holder;
    }

    public function addErrorMessage($message)
    {
        Mage::getSingleton($this->_errorSessionContext)->addError($message);
    }

    public function log($message)
    {
        if ($this->_loggingEnabled) {
            Icommerce_Log::append( 'var/log/sociallogin.log', $message);
        }
    }

    /**
     * Get current quote expire date
     * @return bool|mixed
     */
    public function getQuoteExpireDate()
    {
        $result = false;
        /** @var Mage_Sales_Model_Quote $currentQuote */
        $currentQuote = Mage::getSingleton('checkout/session')->getQuote();
        if ($currentQuote) {
            $lifetime = Mage::getStoreConfig('checkout/cart/delete_quote_after');
            $lifetime *= 86400;
            $result   = date('Y-m-d', time()+$lifetime);
        }

        return $result;
    }
}
