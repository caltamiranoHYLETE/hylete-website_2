<?php

/**
 * Copyright (c) 2009-2013 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_SocialLogin
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 */
class Vaimo_SocialLogin_Block_Facebook extends Mage_Core_Block_Template
{
    const FACEBOOK_OAUTH_URL = 'https://www.facebook.com/dialog/oauth';
    const FACEBOOK_API_URL = 'https://connect.facebook.net/';
    const FACEBOOK_API_SCRIPT_NAME = 'sdk.js';
    const FACEBOOK_API_SCRIPT_NAME_OLD = 'all.js';

    protected $_initialConfiguration = null;
    protected $_localeCode = null;
    protected $_appId = null;
    protected $_xfbml = null;
    protected $_scope = null;
    protected $_apiVersion = null;
    protected $_lazyInit = null;

    public function getFacebookAppId()
    {
        if ($this->_appId == null) {
            $this->_appId = Mage::getStoreConfig('sociallogin/facebook/appid');
        }
        return $this->_appId;
    }

    public function getFacebookXfbml()
    {
        if ($this->_xfbml == null) {
            $this->_xfbml = Mage::getStoreConfig('sociallogin/facebook/xfbml');
        }
        return $this->_xfbml;
    }

    public function getFacebookScope()
    {
        if ($this->_scope == null) {
            $this->_scope = Mage::getStoreConfig('sociallogin/facebook/scope');
        }
        return $this->_scope;
    }

    public function getGraphApiVersion()
    {
        if ($this->_apiVersion == null) {
            $this->_apiVersion = Mage::getStoreConfig('sociallogin/facebook/api_version');
        }
        return $this->_apiVersion;
    }

    public function getFacebookLazyInit()
    {
        if ($this->_lazyInit == null) {
            $this->_lazyInit = Mage::getStoreConfig('sociallogin/facebook/lazyinit');
        }
        return $this->_lazyInit;
    }

    protected function _getLocaleCode()
    {
        if ($this->_localeCode == null) {
            $locale = Mage::app()->getLocale();
            $this->_localeCode = $locale->getLocaleCode();
            if (empty($this->_localeCode)) {
                $this->_localeCode = $locale->getDefaultLocale();
            }
        }
        return $this->_localeCode;
    }

    public function getApiUrl()
    {
        $apiVersion = $this->getGraphApiVersion();
        $locale = $this->_getLocaleCode();

        $apiUrl = self::FACEBOOK_API_URL . $locale . '/';
        $apiUrl .= $apiVersion == 1 ? self::FACEBOOK_API_SCRIPT_NAME_OLD : self::FACEBOOK_API_SCRIPT_NAME;

        return $apiUrl;
    }

    public function getFacebookLoginUrl()
    {
        return $this->getUrl('sociallogin/facebook/login');
    }

    public function getLoginUrlChromeIOS()
    {
        $queryParams = array(
            'client_id' => $this->getFacebookAppId(),
            'redirect_uri' => $this->getFacebookLoginUrl(),
            'scope' => $this->getFacebookScope()
        );
        $queryString = '?';

        foreach ($queryParams as $key => $value) {
            $queryString .= $key . '&' . urlencode($value);
        }

        return self::FACEBOOK_OAUTH_URL . $queryString;
    }

    public function getInitialConfiguration()
    {

        if ($this->_initialConfiguration == null) {
            $this->_initialConfiguration = new Varien_Object(array(
                'api_version' => $this->getGraphApiVersion(),
                'locale' => $this->_getLocaleCode(),
                'scope' => $this->getFacebookScope(),
                'app_id' => $this->getFacebookAppId(),
                'xfbml' => $this->getFacebookXfbml(),
                'lazy_init' => $this->getFacebookLazyInit(),
                'login_url' => $this->getFacebookLoginUrl(),
                'login_url_chrome_ios' => $this->getLoginUrlChromeIOS(),
                'api_url' => $this->getApiUrl()
            ));
        }

        return $this->_initialConfiguration;

    }

    public function checkFbUser()
    {

        $customer_id = Mage::getSingleton('customer/session')
            ->getCustomer()
            ->getId();
        $result = Mage::getModel('sociallogin/login')
            ->load($customer_id);

        // FIXME ER>Is $result an array?
        if ($result['facebook_id']) {
            return $result['facebook_id'];
        }

        return null;
    }
}
