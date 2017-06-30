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

require_once('Facebook/facebook.php');

class Vaimo_SocialLogin_FacebookController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    }

    public function loginAction()
    {
        $this->_log('Login action');
        $facebook = $this->_getFacebookCredentials();

        $user = $facebook->getUser();

        try {
            $userProfile = $facebook->api('/me', array('fields' => 'id,name,email,first_name,last_name,locale'));
            $this->_log($userProfile);
        } catch (FacebookApiException $e) {
            $this->_addError($this->__('Failed to connect to Facebook.'));
            $this->_log($e->getMessage());
            return $this->_redirectAfterLogin(false);
        }
        $this->_log($user);

        if (!$user) {
            $this->_addError($this->__('Could not find requested user on Facebook.'));
            return $this->_redirectAfterLogin(false);
        }

        // Users missing email in their profiles are not verified and thus not allowed
        if (!isset($userProfile['email'])) {
            $this->_addError($this->__('Provided Facebook account doesn\'t have a verified email address.'));
            return $this->_redirectAfterLogin(false);
        }

        /** @var Mage_Customer_Model_Session $session */
        $session = Mage::getSingleton('customer/session');
        $customerId = $session->getCustomerId();

        $customer = $session->getCustomer();
        $forceMatchingEmail = (Mage::getStoreConfig('sociallogin/facebook/emailmatch') == 1);

        if ($customerId && $session->isLoggedIn()) {
            $this->_log('User already logged in (ID:' . $customerId . ')');
            if ($forceMatchingEmail) {
                if ($customer->getEmail() !== $userProfile['email']) {
                    $this->_addError($this->__('Facebook and Magento email doesn\'t match.'));
                    return $this->_redirectAfterLogin(false);
                }
            }

            $this->_updateOrAddFbId($customerId, $userProfile);

            return $this->_redirectAfterLogin();
        }

        /** @var Vaimo_SocialLogin_Model_Login $result */
        $result = Mage::getModel('sociallogin/login')->load($userProfile['id'], 'facebook_id');
        $deletedOldCustomer = $this->_deleteCustomerAtLoginFail($result, $session);

        if (!$result['customer_id'] || $deletedOldCustomer) {
            $this->_log('User not logged in, no user in DB table');
            // We have no logged in Magento user and we have not found any magento_user_id in the table
            // Check if we find the user by email in Magento
            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
            $customer->loadByEmail($userProfile['email']);
            $customerId = $customer->getId();

            if ($customerId > 0) {
                $this->_log('Loaded user by email');
                // We found a Magento user with the same Facebook email.
                $this->_updateOrAddFbId($customerId, $userProfile);
                $session->loginById($customerId);
            } else {
                // No Magento user found, register a new one.
                $this->_registerUser($userProfile, $session);
            }
        }

        $this->_redirectAfterLogin();
    }

    public function ajaxLoginAction()
    {
        $this->_log('Ajax Login action (facebook)');
        $facebook = $this->_getFacebookCredentials();
        $user = $facebook->getUser();
        $this->_log($user);

        $response = array();
        $response['response_code'] = 0; // Starting with neutral

        if (!$user) {
            $response['response_code'] = -1;
            $response['message'] = $this->__('Could not find requested user on Facebook.');
            $this->getResponse()->setBody(json_encode($response));
            return;
        }

        try {
            $userProfile = $facebook->api('/me', array('fields' => 'id,name,email,first_name,last_name,locale'));
            $this->_log($userProfile);
        } catch (FacebookApiException $e) {
            $response['response_code'] = -1;
            $response['message'] = $this->__('Failed to connect to Facebook.');
            $this->_log($e->getMessage());
            $this->getResponse()->setBody(json_encode($response));
            return;
        }

        // Users missing email in their profiles are not verified and thus not allowed
        if (!isset($userProfile['email'])) {
            $response['response_code'] = -1;
            $response['message'] = $this->__('Provided Facebook account doesn\'t have a verified email address.');
            $this->getResponse()->setBody(json_encode($response));
            return;
        }

        /** @var Mage_Customer_Model_Session $session */
        $session = Mage::getSingleton('customer/session');
        $customerId = $session->getCustomerId();

        $customer = $session->getCustomer();
        $forceMatchingEmail = (Mage::getStoreConfig('sociallogin/facebook/emailmatch') == 1);

        if ($customerId && $session->isLoggedIn()) {
            $this->_log('User already logged in (ID:' . $customerId . ')');
            $response['response_code'] = -1;
            $response['message'] = $this->__('User already logged in (ID:%s)', $customerId);

            if ($forceMatchingEmail) {
                if ($customer->getEmail() !== $userProfile['email']) {
                    $this->_log($this->__('Facebook and Magento email doesn\'t match.'));
                }
            }

            $this->_updateOrAddFbId($customerId, $userProfile);
            $this->getResponse()->setBody(json_encode($response));
            return;
        }

        /** @var Vaimo_SocialLogin_Model_Login $result */
        $result = Mage::getModel('sociallogin/login')->load($userProfile['id'], 'facebook_id');
        $deletedOldCustomer = $this->_deleteCustomerAtLoginFail($result, $session);

        if (!$result['customer_id'] || $deletedOldCustomer) {
            $this->_log('User not logged in, no user in DB table');
            // We have no logged in Magento user and we have not found any magento_user_id in the table
            // Check if we find the user by email in Magento
            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
            $customer->loadByEmail($userProfile['email']);
            $customerId = $customer->getId();

            if ($customerId > 0) {
                $this->_log('Loaded user by email');
                // We found a Magento user with the same Facebook email.
                $this->_updateOrAddFbId($customerId, $userProfile);
                $session->loginById($customerId);
            } else {
                // No Magento user found, register a new one.
                $this->_registerUser($userProfile, $session);
            }
        }

        $blockName = $this->getRequest()->getParam('block');
        $template = $this->getRequest()->getParam('template');
        if ($response['response_code'] == 0 && (bool)$blockName && (bool)$template) {
            $block = Mage::app()->getLayout()->createBlock($blockName, str_replace('/', '_', $blockName) . '_ajax', array('template' => $template));
            $response['block_html'] = $block->toHtml();
        }
        $response['response_code'] = 1;
        $response['message'] = $this->__('Success');
        $this->getResponse()->setBody(Zend_Json::encode($response));
        return;
    }

    protected function _registerUser($user_profile, $session)
    {
        $this->_log('Register User');
        $this->_log($user_profile);
        $customer = Mage::getModel('customer/customer')->setId(null);
        $customer->setData('firstname', $user_profile['first_name']);
        $customer->setData('lastname', $user_profile['last_name']);
        $customer->setData('email', $user_profile['email']);
        $customer->setData('password', md5(time() . $user_profile['id'] . $user_profile['locale']));
        $customer->setData('is_active', 1);
        $customer->setData('confirmation', null);
        $customer->setConfirmation(null);
        $customer->getGroupId();
        $customer->save();

        Mage::getModel('customer/customer')->load($customer->getId())->setConfirmation(null)->save();
        $customer->setConfirmation(null);
        $session->setCustomerAsLoggedIn($customer);
        $customer_id = $session->getCustomerId();
        $session->loginById($customer_id);
        Mage::getModel('sociallogin/login')->load($customer_id)->addFacebookId($customer_id, $user_profile['id']);
    }

    protected function _updateOrAddFbId($customer_id, $user_profile)
    {
        $result = Mage::getModel('sociallogin/login')->load($customer_id);

        if ($result->getData('customer_id')) {
            $this->_log('Update customer facebook ID (' . $customer_id . ' => ' . $user_profile['id'] . ')');
            Mage::getModel('sociallogin/login')->load($customer_id)->updateFacebookId($user_profile['id']);
        } else {
            $this->_log('Add customer facebook ID (' . $customer_id . ' => ' . $user_profile['id'] . ')');
            Mage::getModel('sociallogin/login')->load($customer_id)->addFacebookId($customer_id, $user_profile['id']);
        }
    }

    protected function _getFacebookCredentials()
    {
        if (!Mage::helper('sociallogin')->isFacebookEnabled()) {
            throw new Exception('Logging in with Facebook is not activated.');
        }

        $appId = trim(Mage::getStoreConfig('sociallogin/facebook/appid'));
        if (empty($appId)) {
            throw new Exception('Facebook app ID is not set.');
        }

        $secret = trim(Mage::getStoreConfig('sociallogin/facebook/secretkey'));
        if (empty($secret)) {
            throw new Exception('Facebook secret is not set.');
        }

        return new Facebook(array(
                'appId' => $appId,
                'secret' => $secret,
        ));
    }

    /**
     * Calls a redirect to either home page or the page the user last visited, depending on setting.
     *
     * @return void
     */
    protected function _redirectAfterLogin($successful = true)
    {
        // Get the setting that tells us if we should redirect to last visited page or not
        $redirectToLastPage = Mage::getStoreConfigFlag('sociallogin/facebook/redirecttolastpage');

        $redirectUrl = null;
        if ($redirectToLastPage) {
            $redirectUrl = Mage::app()->getRequest()->getServer('HTTP_REFERER');
            if ($redirectUrl == null) {
                $redirectUrl = Mage::getUrl();
            }

            $url = Mage::getSingleton('customer/session')->getAfterAuthUrl();
            if (strlen(trim($url)) > 0 && $url != '://') {
                $redirectUrl = $url;
            }
        } else {
            $customRedirectUrl = Mage::helper('sociallogin')->getRedirectUrl('', $successful);
            if ($_url = $customRedirectUrl->getUrl()) {
                if (strpos($_url, 'http') === false) {
                    $redirectUrl = Mage::getUrl($_url);
                } else {
                    $redirectUrl = $_url;
                }
            }
        }

        //In case we have  empty redirect url and to prevent from showing a blank page
        //we will now just redirect them to the login page if we had a error or to
        //account page if we had a success action
        if (empty($redirectUrl)) {
            if ($successful) {
                $redirectUrl = Mage::getUrl('customer/account');
            } else {
                $redirectUrl = Mage::getUrl('customer/account/login');
            }
        }

        $this->_redirectUrl($redirectUrl);
    }

    /**
     * Tries to log in. If it fails, it deletes the customer.
     *
     * @param $customer Vaimo_SocialLogin_Model_Login
     * @param $session Mage_Customer_Model_Session
     * @return bool True if the user was deleted
     */
    protected function _deleteCustomerAtLoginFail($customer, $session)
    {
        $this->_log('Delete customer at login fail');
        $deletedOld = false;

        if ($customer['customer_id']) {
            // Attempt to log in
            if (!$session->loginById($customer['customer_id'])) {
                $customer->delete();
                $deletedOld = true;
            }
        }

        return $deletedOld;
    }

    /**
     * Adds an error to the error message stack.
     * Note that this method does not translate messages.
     *
     * @param $message The error message that is to be displayed
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

    protected function _getGraphApiVersion()
    {
        $apiVersion = Mage::getStoreConfig('sociallogin/facebook/api_version');
        return $apiVersion;
    }
}

