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

require_once('Twitter/tmhOAuth.php');
require_once('Twitter/tmhUtilities.php');

class Vaimo_SocialLogin_TwitterController extends Mage_Core_Controller_Front_Action
{

    /**
     * Login to google and get the status, the user email and full name.
     * Register the user in the Magento database module table.
     * Create a Magento account if needed.
     * http://server-dd.vaimo.com/x1402/sociallogin/google/login/
     * Login the user in Magento.
     * Redirect to the startpage.
     * @return bool
     */
    public function loginAction()
    {
        if (!Mage::helper('sociallogin')->isTwitterEnabled()) {
            return $this;
        }

        $consumerKey = Mage::getStoreConfig('sociallogin/twitter/consumer_key');
        if ($consumerKey == '') {
            return;
        }

        $consumerSecret = Mage::getStoreConfig('sociallogin/twitter/consumer_secret');
        if ($consumerSecret == '') {
            return;
        }

        $tmhOAuth = new tmhOAuth(array('consumer_key' => $consumerKey, 'consumer_secret' => $consumerSecret));

        if (isset($_REQUEST['start'])) {
            $this->_requestToken($tmhOAuth);
        } elseif (isset($_REQUEST['oauth_verifier'])) {
            $this->_accessToken($tmhOAuth);
        } elseif (isset($_REQUEST['verify'])) {
            $this->_verifyCredentials($tmhOAuth);
        } elseif (isset($_REQUEST['wipe'])) {
            $this->_wipe();
        }

        // FIXME ER>What is that?
        if (isset($_SESSION['access_token'])) {
            echo "There appears to be some credentials already stored in this browser session.
          Do you want to <a href=\"?verify=1\">verify the credentials?</a> or
          <a href=\"?wipe=1\">wipe them and start again</a>.";
        } else {
            echo "<a href=\"?start=1\">Authorize with OAuth</a>.";
        }
    }

    private function _outputError($tmhOAuth)
    {
        Mage::getSingleton('customer/session')->addError($this->__($tmhOAuth->response['response']));
        echo "Error:" . $tmhOAuth->response['response'] . PHP_EOL;
    }

    private function _wipe()
    {
        session_destroy(); // FIXME ER>What is that?
        header('Location: ' . tmhUtilities::php_self());
    }

    // Step 1: Request a temporary token
    private function _requestToken($tmhOAuth)
    {
        $code = $tmhOAuth->request('POST', $tmhOAuth->url('oauth/request_token', ''), array('oauth_callback' => tmhUtilities::php_self()));

        if ($code == 200) {
            $_SESSION['oauth'] = $tmhOAuth->extract_params($tmhOAuth->response['response']);
            $this->_authorize($tmhOAuth);
        } else {
            $this->_outputError($tmhOAuth);
        }
    }

    // Step 2: Direct the user to the authorize web page
    private function _authorize($tmhOAuth)
    {
        $authurl = $tmhOAuth->url("oauth/authorize", '') . "?oauth_token={$_SESSION['oauth']['oauth_token']}";
        header("Location: {$authurl}");

        // in case the redirect doesn't fire
        echo '<p>To complete the OAuth flow please visit URL: <a href="' . $authurl . '">' . $authurl . '</a></p>';
    }

    // Step 3: This is the code that runs when Twitter redirects the user to the callback. Exchange the temporary token for a permanent access token
    private function _accessToken($tmhOAuth)
    {
        $tmhOAuth->config['user_token'] = $_SESSION['oauth']['oauth_token'];
        $tmhOAuth->config['user_secret'] = $_SESSION['oauth']['oauth_token_secret'];

        $code = $tmhOAuth->request('POST', $tmhOAuth->url('oauth/access_token', ''), array(
            'oauth_verifier' => $_REQUEST['oauth_verifier'],
        ));

        if ($code == 200) {
            $_SESSION['access_token'] = $tmhOAuth->extract_params($tmhOAuth->response['response']);
            unset($_SESSION['oauth']);
            header('Location: ' . tmhUtilities::php_self());
        } else {
            $this->_outputError($tmhOAuth);
        }
    }

    // Step 4: Now the user has authenticated, do something with the permanent token and secret we received
    private function _verifyCredentials($tmhOAuth)
    {
        $tmhOAuth->config['user_token']  = $_SESSION['access_token']['oauth_token'];
        $tmhOAuth->config['user_secret'] = $_SESSION['access_token']['oauth_token_secret'];

        $code = $tmhOAuth->request('GET', $tmhOAuth->url('1/account/verify_credentials'));

        if ($code == 200) {
            $resp = json_decode($tmhOAuth->response['response']);
            echo '<h1>Hello ' . $resp->screen_name . '</h1>';
            if (isset($tmhOAuth->response['headers']['X-Access-Level']) == true) {
                echo '<p>The access level of this token is: ' . $tmhOAuth->response['headers']['X-Access-Level'] . '</p>';
            }
            echo '<p>Twitter User ID: ' . $resp->id . '</p>';
            echo '<p>Twitter Name: ' . $resp->name . '</p>';
        } else {
            $this->_outputError($tmhOAuth);
        }
    }

    /**
     * Set the user_profile array, validate the data.
     * @param bool $google_id
     * @param bool $attr
     * @return bool
     */
    private function get_user_profile($google_id = false, $attr = false)
    {
        if ($google_id == false) {
            return false;
        }

        $tmp = explode("=", $google_id);
        $google_id = $tmp[1];

        if ($attr == false) {
            return false;
        }
        if (isset($attr["namePerson/first"]) == false) {
            return false;
        }
        if (isset($attr["namePerson/last"]) == false) {
            return false;
        }
        if (isset($attr["contact/email"]) == false) {
            return false;
        }
        $user_profile = array(
            "id"     => $google_id,
            "first_name" => $attr["namePerson/first"],
            "last_name"  => $attr["namePerson/last"],
            "email"      => $attr["contact/email"],
            "locale" => "",
        );
        return $user_profile;
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
        $customer->setData('lastname',  $user_profile['last_name']);
        $customer->setData('email',     $user_profile['email']);
        $customer->setData('password',  md5(time() . $user_profile['id'] . $user_profile['locale']));
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
        $customer_id = $session->getCustomerId();
        Mage::getModel('sociallogin/login')->load($customer_id)
                ->addTwitterId($customer_id, $user_profile['id']);
    }

    /**
     * FIXME ER> Why this method loads model same customer multiple times?
     *
     * Write data to the table. Check if Magento_user_id,
     * if exist then update the post
     * If not exist then create a new post
     * @param $customer_id
     * @param $user_profile
     */
    private function _updateOrAddTwitterId($customer_id, $user_profile)
    {
        $result = Mage::getModel('sociallogin/login')->load($customer_id);

        if ($result->getData('customer_id')) {
            $row = Mage::getModel('sociallogin/login')->load($customer_id);
            $row->updateTwitterId($user_profile['id']);
        } else {
            $row = Mage::getModel('sociallogin/login')->load($customer_id);
            $row->addTwitterId($customer_id, $user_profile['id']);
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
}
