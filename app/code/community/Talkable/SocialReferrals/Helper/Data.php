<?php
/**
 * Talkable SocialReferrals for Magento
 *
 * @package     Talkable_SocialReferrals
 * @author      Talkable (http://www.talkable.com/)
 * @copyright   Copyright (c) 2015 Talkable (http://www.talkable.com/)
 * @license     MIT
 */

class Talkable_SocialReferrals_Helper_Data extends Mage_Core_Helper_Abstract
{

    //------------------------+
    // Talkable Configuration |
    //------------------------+

    public function getSiteId()
    {
        return $this->_getTextConfigValue("general/site_id");
    }

    //--------------------+
    // Talkable Campaigns |
    //--------------------+

    /**
     * @return bool Whether or not Post Purchase Integration is enabled
     */
    public function isPostPurchaseEnabled()
    {
        return $this->_getBoolConfigValue("campaigns/post_purchase");
    }

    /**
     * @return bool Whether or not Invite Integration is enabled
     */
    public function isInviteEnabled()
    {
        return $this->_getBoolConfigValue("campaigns/invite");
    }

    /**
     * @return bool Whether or not Advocate Dashboard Integration is enabled
     */
    public function isAdvocateDashboardEnabled()
    {
        return $this->_getBoolConfigValue("campaigns/advocate_dashboard");
    }

    /**
     * @return bool Whether or not Floating Widget Popup Integration is enabled
     */
    public function isFloatingWidgetPopupEnabled()
    {
        return $this->_getBoolConfigValue("campaigns/floating_widget_popup");
    }

    //-------------+
    // Origin Data |
    //-------------+

    public function getPurchaseData($order)
    {
        $shippingInfo = array();
        $shippingAddress = $order->getShippingAddress();

        if ($shippingAddress) {
            $countryName = Mage::getModel("directory/country")
                ->loadByCode($shippingAddress->getCountryId())
                ->getName();

            $shippingFields = array_filter(array(
                implode(", ", $shippingAddress->getStreet()),
                $shippingAddress->getCity(),
                $shippingAddress->getRegion(),
                $shippingAddress->getPostcode(),
                $countryName,
            ));

            $shippingInfo = array(
                "shipping_zip" => $shippingAddress->getPostcode(),
                "shipping_address" => implode(", ", $shippingFields),
            );
        }

        $subtotal = (float) $order->getSubtotal();
        if ($order->getDiscountAmount() < 0) {
            // getDiscountAmount() returns negative number formatted as string, e.g. "-10.0000"
            // That's why we add it instead of subtracting.
            $subtotal += (float) $order->getDiscountAmount();
        }

        $retval = array(
            "customer" => array(
                "email"        => $order->getCustomerEmail(),
                "first_name"   => $order->getCustomerFirstname(),
                "last_name"    => $order->getCustomerLastname(),
                "customer_id"  => $order->getCustomerId(),
            ),
            "purchase" => array_merge($shippingInfo, array(
                "order_number" => $order->getIncrementId(),
                "order_date"   => $order->getCreatedAt(),
                "subtotal"     => $this->_normalizeAmount($subtotal),
                "coupon_code"  => $order->getCouponCode(),
                "items"        => array(),
            )),
        );

        foreach ($order->getAllVisibleItems() as $product) {
            $retval["purchase"]["items"][] = array(
                "product_id" => $product->getSku(),
                "price"      => $this->_normalizeAmount($product->getPrice()),
                "quantity"   => strval(round($product->getQtyOrdered())),
                "title"      => $product->getName(),
            );
        }

        return $retval;
    }

    public function getCustomerData()
    {
        $helper = Mage::helper("customer");

        if ($helper->isLoggedIn()) {
            $customer = $helper->getCustomer();
            return array(
                "email"       => $customer->getEmail(),
                "first_name"  => $customer->getFirstname(),
                "last_name"   => $customer->getLastname(),
                "customer_id" => $customer->getId(),
            );
        } else {
            return new stdClass();
        }
    }

    //---------+
    // Private |
    //---------+

    private function _getBoolConfigValue($path)
    {
        return (bool) Mage::getStoreConfig("socialreferrals/" . $path);
    }

    private function _getTextConfigValue($path)
    {
        return trim(Mage::getStoreConfig("socialreferrals/" . $path));
    }

    private function _normalizeAmount($value)
    {
        return number_format((float) $value, 2, ".", "");
    }

}
