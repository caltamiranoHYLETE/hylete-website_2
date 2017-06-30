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

require_once('Google/openid.php');

class Vaimo_SocialLogin_GoogleController extends Mage_Core_Controller_Front_Action
{
    const URL_GOOGLE_LOGIN = 'https://www.google.com/accounts/o8/id';

    /**
     * Login to google and get the status, the user email and full name.
     * Register the user in the Magento database module table.
     * Create a Magento account if needed.
     * http://server-dd.vaimo.com/x1402/sociallogin/google/login/
     * Login the user in Magento.
     * Redirect to the startpage.
     */
    public function loginAction()
    {
        if (!Mage::helper('sociallogin')->isGoogleEnabled()) {
            return $this;
        }

        try {
            $server = Mage::getBaseURL(Mage_Core_Model_Store::URL_TYPE_LINK, Mage::app()->getFrontController()->getRequest()->isSecure());
            $userProfile = array();
            if (Mage::helper('sociallogin')->googleUseOAuth()) {
                $oauthLogin = Mage::getModel('sociallogin/oauthgooglelogin')
                    ->setRedirectURI($server . 'sociallogin/google/login');

                try {
                    $oauthLogin->handleLogin($this->getRequest(), $this->getResponse());
                } catch (RuntimeException $e) {
                    $this->_addError($this->__('Failed to handle login'));
                    $this->_log($e->getMessage());
                    return $this->_redirectIfNeeded();
                }

                if ($oauthLogin->isLoggedIn()) {
                    $userProfile = $oauthLogin->getUserProfile();
                } else if ($oauthLogin->isAccessDenied()) {
                    $this->_addError($this->__('Login canceled by user'));
                    return $this->_redirectIfNeeded();
                } else {
                    return $this;
                }
            } else {
                $openid = new LightOpenID($server);
                if (!$openid->mode) {
                    $openid->identity = self::URL_GOOGLE_LOGIN;

                    // Set the parameters we want back from Google
                    // http://code.google.com/p/lightopenid/wiki/GettingMoreInformation
                    $openid->required = array(
                        'namePerson/first',
                        'namePerson/last',
                        'contact/country/home',
                        'contact/email',
                        'pref/language',
                    );

                    // Redirect to Google login page.
                    $lastVisited = Mage::app()->getRequest()->getServer('HTTP_REFERER');
                    Mage::getSingleton('core/session')->setLastVisited($lastVisited);
                    $this->_redirectUrl($openid->authUrl());
                    return $this;
                }

                // Check if user canceled the login
                if ($openid->mode == 'cancel') {
                    $this->_addError($this->__('Login canceled by user'));
                    return $this->_redirectIfNeeded();
                }

                // We can only check the validation once, next time it will be zero.
                $validate = $openid->validate();

                if ($validate == 0) {
                    // The user did not login with Google, we leave teh login process.
                    $this->_addError($this->__('Not logged in'));
                    return $this->_redirectIfNeeded();
                }

                // We are logged in to Google. Now we have some data to handle
                $google_id = $openid->identity;
                $attr = $openid->getAttributes();

                $userProfile = $this->_getUserProfile($google_id, $attr);
            }

            if (!$userProfile) {
                $this->_addError($this->__('Did not get all data needed from Google'));
                return $this->_redirectIfNeeded();
            }

            $session    = Mage::getSingleton('customer/session');
            $customerId = $session->getCustomerId();
            $customer   = Mage::getSingleton('customer/session')->getCustomer();
            $emailMatch = (Mage::getStoreConfig('sociallogin/google/emailmatch') == '1' ? true : false);

            // Do not use email match
            if ($customerId > 0 && !$emailMatch) {
                // Do not check if Magento<>Google emails match, just associate the google user with the existing Magento user
                $this->_updateOrAddGoogleId($customerId, $userProfile);
                Mage::getModel('sociallogin/login')->loginUser($customerId);
                return $this->_redirectIfNeeded();
            }

            // Use email match
            if ($customerId && $emailMatch) {
                // Check that existing Magento user email = Logged in Google user email
                if ($customer->getEmail() == $userProfile['email']) {
                    // Emails match, the magento user can be associated with the google user
                    $this->_updateOrAddGoogleId($customerId, $userProfile);
                    Mage::getModel('sociallogin/login')->loginUser($customerId);
                } else {
                    // Emails do not match, give error
                    $this->_addError($this->__('Google and Magento email doesn\'t match'));
                }
                return $this->_redirectIfNeeded();
            }

            // There are no existing Magento user logged in.
            // Look in table vaimo_sociallogin if there are a Magento user associated with this Google_id
            $result = Mage::getModel('sociallogin/login')->load($userProfile['id'], 'google_id');
            // FIXME is $result an array? If object then following is incorrect
            if ($result['customer_id']) {
                // Yes we found a magento user id, logging in the user.
                Mage::getModel('sociallogin/login')->loginUser($result['customer_id']);
                return $this->_redirectIfNeeded();
            }

            // We have no logged in Magento user and we have not found any magento_user_id in the table

            // Check if we find the user by email in Magento
            $customer = Mage::getModel("customer/customer");
            $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
            $customer->loadByEmail($userProfile["email"]); //load customer by email id
            $customerId = $customer->getId();
            if ($customerId > 0) {
                // We found a magento user with the same Google email.
                $this->_updateOrAddGoogleId($customerId, $userProfile);
                Mage::getModel('sociallogin/login')->loginUser($customerId);
                return $this->_redirectIfNeeded();
            }

            // Create a new Magento user. Set as logged in. Register the new user with this Google_id.
            $this->_registerUser($userProfile, $session);
        } catch (ErrorException $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
        }

        return $this->_redirectIfNeeded();
    }

    protected function _redirectIfNeeded()
    {
        $this->_redirectAfterLogin(Mage::helper('customer')->isLoggedIn());

        return $this;
    }

    private function _redirectAfterLogin($successful = true)
    {
        // Get the setting that tells us if we should redirect to last visited page or not
        $redirectToLastPage = Mage::getStoreConfigFlag('sociallogin/facebook/redirecttolastpage');

        $redirectUrl = null;
        if ($redirectToLastPage) {
            $refererUrl = Mage::getSingleton('core/session')->getLastVisited();
            $redirectUrl = $refererUrl;
        } else {
            $redirectUrl = Mage::getUrl();
        }

        $customRedirectUrl = Mage::helper('sociallogin')->getRedirectUrl('', $successful);
        if ($_url = $customRedirectUrl->getUrl()) {
            if (strpos($_url, 'http') === false) {
                $redirectUrl = Mage::getUrl($_url);
            } else {
                $redirectUrl = $_url;
            }
        }

        Mage::getSingleton('core/session')->unsLastVisited();
        $this->_redirectUrl($redirectUrl);
    }

    /**
     * Set the user_profile array, validate the data.
     * @param bool $google_id
     * @param bool $attr
     * @return bool
     */
    private function _getUserProfile($googleId = false, $attr = false)
    {
        if (!$googleId  || !$attr) {
            return false;
        }

        if (!isset($attr["namePerson/first"])) {
            return false;
        }
        if (!isset($attr["namePerson/last"])) {
            return false;
        }
        if (!isset($attr["contact/email"])) {
            return false;
        }

        $tmp = explode('=', $googleId);
        $googleId = $tmp[1];

        $userProfile = array(
            "id" => $googleId,
            "first_name" => $attr["namePerson/first"],
            "last_name"  => $attr["namePerson/last"],
            "email"  => $attr["contact/email"],
            "locale" => "",
        );

        return $userProfile;
    }

    /**
     * Register a new user, login the new user, associate the google_id with this new user.
     * @param $user_profile
     * @param $session
     */
    private function _registerUser($user_profile, &$session)
    {
        $customer = Mage::getModel('customer/customer')
                ->setId(null);
        $customer->setData('firstname', $user_profile['first_name']);
        $customer->setData('lastname', $user_profile['last_name']);
        $customer->setData('email', $user_profile['email']);
        $customer->setData('password', md5(time() . $user_profile['id'] . $user_profile['locale']));
        $customer->setData('is_active', 1);
        $customer->setData('confirmation', null);
        $customer->setConfirmation(null);
        $customer->getGroupId();
        $customer->save();

        Mage::getModel('customer/customer')
                ->load($customer->getId())
                ->setConfirmation(null)
                ->save();
        $customer->setConfirmation(null);
        $session->setCustomerAsLoggedIn($customer);
        $customerId = $session->getCustomerId();
        Mage::getModel('sociallogin/login')
                ->load($customerId)
                ->addGoogleId($customerId, $user_profile['id']);
    }

    /**
     * Write data to the table. Check if Magento_user_id,
     * if exist then update the post
     * If not exist then create a new post
     * @param $customer_id
     * @param $user_profile
     */
    private function _updateOrAddGoogleId($customer_id, $user_profile)
    {
        $result = Mage::getModel('sociallogin/login')
                ->load($customer_id);

        if ($result->getData('customer_id')) {
            $row = Mage::getModel('sociallogin/login')->load($customer_id);
            $row->updateGoogleId($user_profile['id']);
        } else {
            $row = Mage::getModel('sociallogin/login')->load($customer_id);
            $row->addGoogleId($customer_id, $user_profile['id']);
        }
    }

    /**
     * This function make sure that you don't get inte problems when using store code in the url.
     * @param null $coreRoute
     */
    public function norouteAction($coreRoute = null)
    {
        // FIXME ER> this looks porn! Don't use echo, use layout/templates
        echo "Add the storecode to your URL (www.blabla.com/storecode/modulename/controllername/actionname , or turn off store codes in admin (System / Configuration / General / Web / Url Options / Add Store Code to Urls)";
        parent::norouteAction($coreRoute);
    }

    /**
     * Adds an error to the error message stack.
     * Note that this method does not translate messages.
     *
     * @param $message string Error message that is to be displayed
     */
    protected function _addError($message)
    {
        $this->_log($message);
        Mage::helper('sociallogin')->addErrorMessage($message);
    }

    protected function _log($message)
    {
        return Mage::helper('sociallogin')->log($message);
    }
}
