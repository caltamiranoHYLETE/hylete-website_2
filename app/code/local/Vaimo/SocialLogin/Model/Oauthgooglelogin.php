<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
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
 * @file        OAuthGoogleLogin.php
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 * @author      Vitali Rassolov <vitali.rassolov@vaimo.com>
 */

require_once('Google/google.php');

class Vaimo_SocialLogin_Model_Oauthgooglelogin extends Mage_Core_Model_Abstract
{
    protected $_helper;
    protected $_redirectURI;
    protected $_scope;
    protected $_isLoggedIn     = false;
    protected $_isAccessDenied = false;

    protected $_userProfile = array();

    /**
     * @param boolean $isAccessDenied
     */
    public function setAccessDenied($isAccessDenied)
    {
        $this->_isAccessDenied = $isAccessDenied;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isAccessDenied()
    {
        return $this->_isAccessDenied;
    }

    /**
     * @param array $userProfile
     */
    public function setUserProfile($userProfile)
    {
        $this->_userProfile = $userProfile;

        return $this;
    }

    /**
     * @return array
     */
    public function getUserProfile()
    {
        return $this->_userProfile;
    }


    /**
     * @param bool $isLoggedIn
     *
     * @return $this
     */
    public function setLoggedIn($isLoggedIn)
    {
        $this->_isLoggedIn = $isLoggedIn;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->_isLoggedIn;
    }

    /**
     * @param mixed $scope
     */
    public function setScope($scope)
    {
        $this->_scope = $scope;

        return $this;
    }

    /**
     * @return array
     */
    public function getScope()
    {
        if (!$this->_scope) {
            $this->_scope = array(
                'https://www.googleapis.com/auth/userinfo.profile',
                'https://www.googleapis.com/auth/userinfo.email'
            );
        }

        return $this->_scope;
    }

    /**
     * @param mixed $helper
     */
    public function setHelper($helper)
    {
        $this->_helper = $helper;

        return $this;
    }

    /**
     * @return Vaimo_SocialLogin_Helper_Data
     */
    public function getHelper()
    {
        if (!$this->_helper) {
            $this->_helper = Mage::helper('sociallogin');
        }

        return $this->_helper;
    }

    /**
     * @param mixed $redirectURI
     */
    public function setRedirectURI($redirectURI)
    {
        $this->_redirectURI = $redirectURI;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRedirectURI()
    {
        return $this->_redirectURI;
    }

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Controller_Response_Http $response
     * @param $session
     */
    public function handleLogin($request, $response)
    {
        $google = new Google(
            array(
                'id'     => $this->getHelper()->googleClientId(),
                'secret' => $this->getHelper()->googleClientSecret()
            )
        );

        $code  = $request->getParam('code', null);
        $error = $request->getParam('error', null);

        if ($code) {
            $token = $google->getToken($code, $this->getRedirectURI());

            $this->setUserProfile(
                $this->_formatUserProfile($google->getInfo($token))
            );

            $this->setLoggedIn(true);
        } else if ($error) {
            $this->setLoggedIn(false);
            $this->setAccessDenied(true);
        } else {
            $lastVisited = Mage::app()->getRequest()->getServer('HTTP_REFERER');
            Mage::getSingleton('core/session')->setLastVisited($lastVisited);
            $response->setRedirect(
                $google->getLoginUrl(
                    array(
                        'redirect_uri' => $this->getRedirectURI(),
                        'scope' => $this->getScope()
                    )
                )
            );
        }
    }

    /**
     * @param array $userProfile
     *
     * @return array|bool
     */
    protected function _formatUserProfile(array $userProfile)
    {
        if (!isset($userProfile['emails'][0]['value']) ||
            !isset($userProfile['name']['familyName']) ||
            !isset($userProfile['name']['givenName']) ||
            !isset($userProfile['id'])) {

            return false;
        }

        return array(
            'id'         => $userProfile['id'],
            'first_name' => $userProfile['name']['givenName'],
            'last_name'  => $userProfile['name']['familyName'],
            'email'      => $userProfile['emails'][0]['value'],
            'locale'     => $userProfile['language']
        );
    }
}