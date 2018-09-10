<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
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
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Kjell Holmqvist
 */

class Vaimo_AppApi_Controller_Action extends Mage_Core_Controller_Front_Action
{
    protected $_doAuthentication = true;

    /**#@+
     * HTTP Response Codes
     */
    const HTTP_OK                 = 200;
    const HTTP_CREATED            = 201;
    const HTTP_MULTI_STATUS       = 207;
    const HTTP_BAD_REQUEST        = 400;
    const HTTP_UNAUTHORIZED       = 401;
    const HTTP_FORBIDDEN          = 403;
    const HTTP_NOT_FOUND          = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE     = 406;
    const HTTP_INTERNAL_ERROR     = 500;
    /**#@- */

    /**
     * Authentication flow
     *
     * @return Mage_Core_Controller_Front_Action
     */
    public function preDispatch()
    {
        $result = parent::preDispatch();

        if ($this->_doAuthentication) {
            $this->_authenticate();
        }

        return $result;
    }


    /**
     * Basic HTTP Authentication method
     *
     * Checking for proper request headers
     * and comparing them to the ones stored in configuration
     *
     * @return void
     */
    public function _authenticate()
    {
        if (Mage::getStoreConfig('appapi/appapi_settings/password') != $this->_request->getServer('PHP_AUTH_PW')
            || Mage::getStoreConfig('appapi/appapi_settings/username') != $this->_request->getServer('PHP_AUTH_USER')) {

            if (!headers_sent()) {
                header('HTTP/1.1 ' . self::HTTP_INTERNAL_ERROR);
            }

            $this->_response->setHttpResponseCode(self::HTTP_UNAUTHORIZED)
                ->setBody('Invalid user credentials.')
                ->sendResponse();

            exit;
        }
    }
    
    /**
     * Test for GET
     *
     * If request is not a GET, return error
     *
     * @return bool
     */
    protected function _isRequestGet(&$result)
    {
        if (!$this->_request->isGet()) {
            $result['status'] = 'error';
            $result['result']  = 'Request is not of type GET';
            return false;
        }
        return true;
    }
    
    /**
     * Test for POST
     *
     * If request is not a POST, return error
     *
     * @return bool
     */
    protected function _isRequestPost(&$result)
    {
        if (!$this->_request->isPost()) {
            $result['status'] = 'error';
            $result['result']  = 'Request is not of type POST';
            return false;
        }
        return true;
    }

    protected function _skipAuthentication()
    {
        $this->_doAuthentication = false;
    }
    
}
