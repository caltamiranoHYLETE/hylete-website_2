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
 * @package     Vaimo_Module
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 */

class Icommerce_CheckoutCommon_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function formatPrice($price, $precision=2)
    {
        $helper = Mage::helper('checkout');
        if ($helper->getQuote()->getStore()->getCurrentCurrency()) {
            return $helper->getQuote()->getStore()->getCurrentCurrency()->format($price, array('precision' => $precision));
        }
        return $helper->getQuote()->getStore()->formatPrice($price);
    }

    /**
     * @description Determine if we should skip add duplicate address on customer object
     * @return bool
     */
    public function isSkipAddDuplicateAddress()
    {
        return (bool)Mage::getStoreConfig('checkoutcommon/settings/is_skip_add_duplicate_address');
    }

    /**
     * @description Check if address is duplicate
     * @param $address Mage_Customer_Model_Address
     * @param $existingAddresses Mage_Customer_Model_Resource_Address_Collection
     * @return bool
     */
    public function isAddressDuplicate($address, $existingAddresses)
    {
        $isAddressDuplicate = false;

        foreach ($existingAddresses as $existingAddress) {
            if($existingAddress->getFirstname() == $address->getFirstname()
                && $existingAddress->getLastname() == $address->getLastname()
                && $existingAddress->getStreet() == $address->getStreet()
                && $existingAddress->getCity() == $address->getCity()
                && $existingAddress->getPostcode() == $address->getPostcode()
                && $existingAddress->getCountryId() == $address->getCountryId()
                && $existingAddress->getRegionId() == $address->getRegionId()
                && $existingAddress->getTelephone() == $address->getTelephone())
            {
                $isAddressDuplicate = true;
                break;
            }
        }

        return $isAddressDuplicate;
    }
}