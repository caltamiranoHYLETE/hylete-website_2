<?php
/**
 * Subscribe Pro - Subscriptions Management Extension
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of SUBSCRIBE PRO INC.
 *
 * @category  SubscribePro
 * @package   SubscribePro_Autoship
 * @author    Subscribe Pro Support <support@subscribepro.com>
 * @copyright 2009-2020 SUBSCRIBE PRO INC. All Rights Reserved.
 * @license   http://www.subscribepro.com/terms-of-service/ Subscribe Pro Terms of Service
 * @link      http://www.subscribepro.com/
 *
 */

class SubscribePro_Autoship_Model_Shipping_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'subscribepro';


    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            'recurring' =>  'Recurring Order Shipping',
        );
    }

    /**
     * Collect and get rates
     *
     * @abstract
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result|bool|null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        /** @var Mage_Shipping_Model_Rate_Result $result */
        $result = Mage::getModel('shipping/rate_result');

        // Make sure SP is enabled for this store
        // Check config to see if extension functionality is enabled
        if (Mage::getStoreConfig('autoship_general/general/enabled', $this->getStore()) == '1') {

            // Only make Subscribe Pro shipping methods available on recurring orders
            // Assume recurring orders can't have multiple shipping addresses

            // Get quote items from request
            $items = $request->getAllItems();
            // Make sure we have at least 1 item
            if (count($items)) {
                // Get quote from first item
                /** @var Mage_Sales_Model_Quote_Item $quoteItem1 */
                $quoteItem1 = $items[0];
                $quote = $quoteItem1->getQuote();

                /** @var SubscribePro_Autoship_Helper_Quote $quoteHelper */
                $quoteHelper = Mage::helper('autoship/quote');

                // Check if quote has recurring subscription items
                if ($quoteHelper->hasSubscriptionReorderProduct($quote)) {
                    // Calculate rate
                    $rate = $this->getRate($request, $quote);
                    try {
                        // Fire event
                        Mage::dispatchEvent('subscribepro_autoship_after_calc_recurring_shipping_rate',
                            array('request' => $request, 'quote' => $quote, 'rate' => $rate));
                    }
                    catch(\Exception $e) {
                        SubscribePro_Autoship::log('SubscribePro_Autoship_after_calc_recurring_shipping_rate event dispatching failed!', Zend_Log::ERR);
                        SubscribePro_Autoship::log('Error message: ' . $e->getMessage(), Zend_Log::ERR);
                        // Re-calculate rate
                        $rate = $this->getRate($request, $quote);
                    }
                    // Make Subscribe Pro shipping method available
                    $result->append($rate);
                }
            }
        }

        return $result;
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function getRate(Mage_Shipping_Model_Rate_Request $request, Mage_Sales_Model_Quote $quote)
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        // Set carrier and method codes
        $rate->setCarrier($this->_code);
        $rate->setMethod('recurring');

        // Set cost to 0 for all Subscribe Pro shipping rate quotes
        $rate->setCost(0.0);

        // Implement logic to select free, standard or international shipping
        // Check if this order qualifies as international
        $isInternationalShipment = false;
        if ($this->getConfigFlag('enable_international_shipping')) {
            // Now compare request country with Magento origin country
            if ($request->getDestCountryId() != Mage::getStoreConfig('shipping/origin/country_id', $this->getStore())) {
                // Request country is different, this is an international shipment
                $isInternationalShipment = true;
            }
        }

        // Check if order qualifies for free shipping
        $freeShippingApplies = false;
        if ($this->getConfigFlag('enable_free_shipping')) {
            if ($isInternationalShipment) {
                $freeShippingThreshold = $this->getConfigData('free_shipping_minimum_order_total_international');
            }
            else {
                $freeShippingThreshold = $this->getConfigData('free_shipping_minimum_order_total');
            }
            if ($request->getBaseSubtotalInclTax() >= $freeShippingThreshold) {
                $freeShippingApplies = true;
            }
        }

        //
        // Set price on the rate quote
        //
        // Lookup custom price from quote and override price we have set otherwise
        if ($quote->getData('subscribe_pro_custom_shipping_price') > 0.0) {
            // Custom rate passed via API into quote
            $shippingPrice = $quote->getData('subscribe_pro_custom_shipping_price');
        }
        else {
            if ($freeShippingApplies) {
                $shippingPrice = 0.0;
            }
            else {
                if ($isInternationalShipment) {
                    $shippingPrice = $this->getConfigData('default_international_shipping_price');
                }
                else {
                    $shippingPrice = $this->getConfigData('default_standard_shipping_price');
                }
            }
        }
        $rate->setPrice($shippingPrice);

        //
        // Set title on rate quote
        //
        if ($freeShippingApplies) {
            $title = $this->getConfigData('free_title');
        }
        else {
            if ($isInternationalShipment) {
                $title = $this->getConfigData('international_title');
            }
            else {
                $title = $this->getConfigData('standard_title');
            }
        }
        $rate->setMethodTitle($title);

        // Now return the rate we built
        return $rate;
    }

}
