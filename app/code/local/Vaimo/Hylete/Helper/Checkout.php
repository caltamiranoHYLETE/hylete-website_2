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
 * @package     Vaimo_Hylete
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 */

/**
 * Class Vaimo_Hylete_Helper_Checkout
 *
 * @category  Vaimo
 * @package   Vaimo_Hylete
 * @author    Vaimo
 */
class Vaimo_Hylete_Helper_Checkout extends Mage_Core_Helper_Abstract
{
    const DEFAULT_CUSTOMER_GROUP_ID = 1;
    const CONFIG_CUSTOMER_VIP_GROUPS_PATH = 'vaimo_hylete/customer_groups/vip_groups';

    /**
     * @var array
     */
    protected $_groupIds;

    /**
     * Transform the discount title by removing coupon code and placing the percentage instead
     *
     * @param \Mage_Sales_Model_Quote_Address_Total $total
     */
    public function transformDiscountTitle(Mage_Sales_Model_Quote_Address_Total $total)
    {
        /** @var Mage_Sales_Model_Quote_Address $address */
        $address = $total->getAddress();

        if ($address) {
            $discountTitle = $total->getTitle();
            $discountAmount = $total->getValue();
            $subtotal = $address->getSubtotal();

            $percentage = abs($discountAmount / $subtotal * 100);
            $discountTitle = $this->__('Discount total (%d%%)', $percentage);

            $total->setTitle($discountTitle);
        }
    }

    /**
     * Transform the shipping carrier/method title by using the part that in outer parentheses
     *
     * @param \Mage_Sales_Model_Quote_Address_Total $total
     */
    public function transformShippingTitle(Mage_Sales_Model_Quote_Address_Total $total)
    {
        $title = $total->getTitle();

        if (preg_match('/^[^(]+\((.*)\)$/', $title, $matches)) {
            $title = $matches[1];
            $total->setTitle($title);
        }
    }

    /**
     * Calculate the overall difference between original price amount and the final price amount
     *
     * @return float
     */
    public function getCartDifferenceAmount()
    {
        /** @var Mage_Sales_Model_Quote $cart */
        $cart = Mage::helper('checkout/cart')->getQuote();

        $amount = 0;

        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach($cart->getAllVisibleItems() as $item) {
            $product = $item->getProduct();

            $originalPrice = $product->getPrice();
            $finalPrice = $product->getFinalPrice();
            $amount += ($originalPrice - $finalPrice) * $item->getQty();
        }

        return $amount;
    }

    /**
     * Calculate the custom shipping label for the totals
     *
     * @param Mage_Sales_Model_Quote_Address_Total $total
     * @return string
     */
    public function getCustomSubtotalLabel($total)
    {
        $groupId = $this->_getCustomer()->getGroupId();

        /** @var Mage_Tax_Helper_Data $taxHelper */
        $taxHelper = Mage::helper('tax');

        if (in_array($groupId, $this->_getGroupIds())) {
            return $taxHelper->__('VIP Team Pricing');
        } elseif ($groupId == $this->getDefaultCustomerGroupId()) {
            return $total->getTitle();
        }

        return $taxHelper->__('Exclusive Pricing');
    }

    /**
     * Get a default customer group ID for totals
     *
     * @return int
     */
    public function getDefaultCustomerGroupId()
    {
        return self::DEFAULT_CUSTOMER_GROUP_ID;
    }

    public function getAddressesHtmlSelect(Mage_Checkout_Block_Onepage_Abstract $block, $type)
    {
        if ($block->isCustomerLoggedIn()) {
            $options = array();
            foreach ($block->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline'),
                    'params' => array(
                        'data-country-id' => $block->escapeHtml($address->getCountryId()),
                    )
                );
            }

            $addressId = $block->getAddress()->getCustomerAddressId();
            if (empty($addressId)) {
                if ($type=='billing') {
                    $address = $block->getCustomer()->getPrimaryBillingAddress();
                } else {
                    $address = $block->getCustomer()->getPrimaryShippingAddress();
                }
                if ($address) {
                    $addressId = $address->getId();
                }
            }

            $select = $block->getLayout()->createBlock('core/html_select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
                ->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', Mage::helper('checkout')->__('New Address'));

            return $select->getHtml();
        }
        return '';
    }

    /**
     * Fetch current customer instance
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer()
    {
        return Mage::helper('customer')->getCustomer();
    }

    /**
     * Retrieve a list of group IDs eligible for custom label
     *
     * @return array
     */
    protected function _getGroupIds()
    {
        if (!$this->_groupIds) {
            $this->_groupIds = explode(',', (string)Mage::getStoreConfig(self::CONFIG_CUSTOMER_VIP_GROUPS_PATH));
        }

        return $this->_groupIds;
    }
}
