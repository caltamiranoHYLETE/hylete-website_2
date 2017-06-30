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
 * @package     Vaimo_Hylete
 * @author      Scott Kennerly <skennerly@hylete.com>
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Vaimo_Hylete_Block_TrackingScripts extends Vaimo_GoogleAddons_Block_TrackingScripts
{
    public function getDynamicRemarketingCustomerData() {
        $accumulatedCustomerData = array();

        $accumulatedCustomerData['customer_group_id'] = 0;
        $accumulatedCustomerData['customer_logged_in'] = 0;
        $accumulatedCustomerData['customer_gender'] = "";
        $accumulatedCustomerData['customer_id'] = "0";
        $accumulatedCustomerData['customer_email'] = "";

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $accumulatedCustomerData['customer_group_id'] = $customer->getGroupId();
            $accumulatedCustomerData['customer_logged_in'] = 1;
            $accumulatedCustomerData['customer_gender'] = $customer->getGender();
            $accumulatedCustomerData['customer_id'] = $customer->getId();
            $accumulatedCustomerData['customer_email'] = $customer->getEmail();
        }

        return $accumulatedCustomerData;
    }
}