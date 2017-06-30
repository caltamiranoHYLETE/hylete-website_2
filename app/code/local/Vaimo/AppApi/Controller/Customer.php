<?php
/**
 * Copyright (c) 2009-2016 Vaimo AB
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
 * @package     Vaimo_AppApi
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 * @author      Tobias Åström
 */

class Vaimo_AppApi_Controller_Customer extends Vaimo_AppApi_Controller_Action
{
    protected $_doCustomerAuthentication = true;
    protected $_tokenExpiration = '+30 days';
    protected $_customerSession = false;
    protected $_authenticatedCustomer = false;

    private $_token = false;
    private $_username = false;
    private $_password = false;
    private $_timestamp = false;
    private $_nonce = false;
    private $_signature = false;

    public function preDispatch()
    {
        $result = parent::preDispatch();

        if ($this->_doCustomerAuthentication) {
            $this->_token = $this->_request->getHeader('Vaimo-Token');
            $this->_username = $this->_request->getHeader('Vaimo-Username');
            $this->_password = $this->_request->getHeader('Vaimo-Password');
            $this->_timestamp = $this->_request->getHeader('Vaimo-Timestamp');
            $this->_nonce = $this->_request->getHeader('Vaimo-Nonce');
            $this->_signature = $this->_request->getHeader('Vaimo-Signature');

            $this->_authenticateRequest();
            $this->_authenticateCustomer();
            // TODO: Check Timestamp and Expiration
        }

        return $result;
    }

    public function _authenticateRequest()
    {
        if (!$this->_authenticateCustomerNonce() || !$this->_authenticateCustomerSignature()) {
            if (!headers_sent()) {
                header('HTTP/1.1 ' . self::HTTP_INTERNAL_ERROR);
            }

            $this->_response->setHttpResponseCode(self::HTTP_BAD_REQUEST)
                ->setBody('Invalid request.')
                ->sendResponse();
            exit;
        }
    }

    public function _authenticateCustomer()
    {
        $result = array();

        if ($this->_token && $this->_authenticateCustomerToken() && $this->_registerNonce()) {
            $this->_authenticatedCustomer = $this->_getAuthenticatedCustomer();
        } else if ($this->_authenticateCustomerCredentials()) {
            $token = $this->_generateToken();

            if ($token && $this->_registerNonce()) {
                Mage::register('isSecureArea', true);

                $apiResult = Mage::getModel('appapi/customer')->listDetails(
                    $this->_getAuthenticatedCustomerId(),
                    Vaimo_AppApi_Helper_Data::APP_API_DETAIL_LEVEL_MINMIAL
                );

                Mage::unregister('isSecureArea');

                $result['status'] = 'success';
                $result['result'] = $apiResult;
                $result['token'] = $token;

                $jsonData = Mage::helper('core')->jsonEncode($result);

                $this->_response->setHttpResponseCode(self::HTTP_OK)
                    ->setHeader('Content-type', 'application/json')
                    ->setBody($jsonData)
                    ->sendResponse();
                exit;
            } else {
                if (!headers_sent()) {
                    header('HTTP/1.1 ' . self::HTTP_INTERNAL_ERROR);
                }

                $this->_response->setHttpResponseCode(self::HTTP_INTERNAL_ERROR)
                    ->setBody('Internal error.')
                    ->sendResponse();
                exit;
            }
        } else {
            if (!headers_sent()) {
                header('HTTP/1.1 ' . self::HTTP_INTERNAL_ERROR);
            }

            $this->_response->setHttpResponseCode(self::HTTP_UNAUTHORIZED)
                ->setBody('Invalid user credentials.')
                ->sendResponse();
            exit;
        }
    }

    protected function _authenticateCustomerNonce()
    {
        $nonce = (!empty($this->_nonce) ? $this->_nonce : false);
        $doesExist =  Mage::getModel('appapi/nonce')->getCollection()->addFieldToFilter('auth_nonce', $nonce)->getFirstItem()->getId();

        if ($nonce && !$doesExist) {
            return true;
        }

        return false;
    }

    protected function _authenticateCustomerSignature()
    {
        $signature = hash('sha256', ($this->_token ? $this->_token : $this->_username . $this->_password) . $this->_timestamp . $this->_nonce);

        if ($this->_signature && $this->_signature == $signature) {
            return true;
        }

        return false;
    }

    protected function _authenticateCustomerCredentials()
    {
        $authenticated = false;
        $session = $this->_getCustomerSession();

        try {
            $authenticated = $session->login($this->_username, $this->_password);
        } catch (Exception $e) {
            Mage::log($e, null, 'appapi.log');
        }

        return $authenticated;
    }

    protected function _authenticateCustomerToken()
    {
        $authenticated = false;
        $session = $this->_getCustomerSession();
        $customerId = Mage::getModel('appapi/auth')->getCollection()->addFieldToFilter('auth_token', $this->_token)->getFirstItem()->getCustomerId();

        if ($customerId) {
            $authenticated = $session->loginById($customerId);
        }

        return $authenticated;
    }

    protected function _getCustomerSession()
    {
        if (!$this->_customerSession) {
            $this->_customerSession = Mage::getSingleton('customer/session');
        }

        return $this->_customerSession;
    }

    protected function _getAuthenticatedCustomer()
    {
        $customerId = $this->_getAuthenticatedCustomerId();

        if ($customerId) {
            $customer = Mage::getModel('customer/customer')->load($customerId);

            if ($customer->getId()) {
                return $customer;
            }
        }

        return false;
    }

    protected function _getAuthenticatedCustomerId()
    {
        $session = $this->_getCustomerSession();
        $customerId = $session->getCustomerId();

        if ($customerId) {
            return $customerId;
        }

        return false;
    }

    protected function _generateToken()
    {
        $token = md5(rand(0, 1000));

        $authModel = Mage::getModel('appapi/auth');
        $authModel->setAuthToken($token);
        $authModel->setAuthTimestamp(date('Y-m-n H:i:s'));
        $authModel->setAuthValidTo(date('Y-m-n H:i:s', strtotime($this->_tokenExpiration)));
        $authModel->setCustomerId($this->_getAuthenticatedCustomerId());

        if ($authModel->save()) {
            return $token;
        }

        return false;
    }

    protected function _registerNonce()
    {
        $nonceModel = Mage::getModel('appapi/nonce');
        $nonceModel->setAuthNonce($this->_nonce);
        $nonceModel->setAuthId($this->_getAuthId());

        if ($nonceModel->save()) {
            return true;
        }

        return false;
    }

    protected function _getAuthId()
    {
        $customerId = $this->_getAuthenticatedCustomerId();
        $authId = Mage::getModel('appapi/auth')->getCollection()->addFieldToFilter('customer_id', $customerId)->getLastItem()->getId();

        if ($authId) {
            return $authId;
        }

        return false;
    }

    protected function _skipCustomerAuthentication()
    {
        $this->_doCustomerAuthentication = false;
    }
}