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
 * @package     Vaimo_GoogleAddons
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

/**
 * GoogleAddons Page Block
 *
 * @category   Vaimo
 * @package    Vaimo_GoogleAddons
 */
class Vaimo_GoogleAddons_Block_Ua extends Mage_Core_Block_Template
{

    #function __construct() {}
    /**
     * Get a specific page name (may be customized via layout)
     *
     * @return string|null
     */
    public function getPageName()
    {
        return $this->_getData('page_name');
    }

    /**
     * Render regular page tracking javascript code
     * The custom "page name" may be set from layout or somewhere else. It must start from slash.
     *
     * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiBasicConfiguration.html#_gat.GA_Tracker_._trackPageview
     * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApi_gaq.html
     * @return string
     */
    public function getPageTrackingCode()
    {
        $pageName = trim($this->getPageName());
        $optPageURL = '';
        if ($pageName && preg_match('/^\/.*/i', $pageName)) {
            $optPageURL = ", '{$this->jsQuoteEscape($pageName)}'";
        }
        return "ga('send', 'pageview'{$optPageURL});";
    }

    /**
     * Render information about specified orders and their items
     *
     * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEcommerce.html#_gat.GA_Tracker_._addTrans
     * @return string
     */
    public function getOrdersTrackingCode()
    {
        $totalIncludeTaxAndShipping = Mage::helper("googleaddons")->getTotalIncludeTaxAndShipping();

        $orderIds = $this->getOrderIds();
        if (!$orderIds || !is_array($orderIds)) {
            return '';
        }

        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds));
        $result = array(
            "ga('require', 'ecommerce', 'ecommerce.js');"
        );

        foreach ($collection as $order) {
            $revenue = $order->getBaseSubtotal();
            if ($totalIncludeTaxAndShipping) {
                $revenue = $order->getBaseGrandTotal();
            }

            $transaction = array(
                'id' => $order->getIncrementId(),
                'affiliation' => $this->jsQuoteEscape(Mage::app()->getStore()->getFrontendName()),
                'revenue' => $revenue,
                'shipping' => $order->getBaseShippingAmount(),
                'tax' => $order->getBaseTaxAmount()
            );

            $result[] = "ga('ecommerce:addTransaction'," . Zend_Json::encode($transaction) . ");";

            foreach ($order->getAllVisibleItems() as $item) {

                $item = array(
                    'id' => $order->getIncrementId(),
                    'name' => $item->getName(),
                    'sku' => $item->getSku(),
                    'category' => null,
                    'price' => $item->getBasePrice(),
                    'quantity' => $item->getQtyOrdered()
                );

                $result[] = "ga('ecommerce:addItem'," . Zend_Json::encode($item) . ");";

            }
            $result[] = "ga('ecommerce:send');";
        }
        return implode("\n", $result);
    }
}