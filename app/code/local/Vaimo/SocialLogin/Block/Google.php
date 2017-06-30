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

class Vaimo_SocialLogin_Block_Google extends Mage_Core_Block_Template
{

    public function getFacebookAppId()
    {
        return Mage::getStoreConfig('sociallogin/facebook/appid');
    }

    public function getFacebookXfbml()
    {
        return Mage::getStoreConfig('sociallogin/facebook/xfbml');
    }

    public function getFacebookScope()
    {
        return Mage::getStoreConfig('sociallogin/facebook/scope');
    }

    public function checkGoogleUser()
    {

        $customer_id = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $result = Mage::getModel('sociallogin/login')->load($customer_id);

        // FIXME ER>Is $result an array?
        if ($result['google_id']) {
            return $result['google_id'];
        }

        return null;
    }
}
