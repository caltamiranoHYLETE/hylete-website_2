<?php

class Hylete_ApplePay_Helper_Data extends Cryozonic_StripeExpress_Helper_Data
{
    /**
     * Add discount to Cart Items
     *
     * @param Mage_Sales_Model_Quote|null $quote
     * @return array|void
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getCartItems($quote = null)
    {
        $result = parent::getCartItems($quote);
        $quote = $quote ?: $this->getSessionQuote();

        // Early exit
        $totals = $quote->getTotals();
        if (!isset($totals['discount']) || $totals['discount']->getValue() > -0.01) {
            return $result;
        }

        $discountAmount = (float)$totals['discount']->getValue();

        // Get Currency and Amount
        $currency = Mage::getStoreConfig('payment/cryozonic_stripe/use_store_currency')
            ? $quote->getQuoteCurrencyCode()
            : $quote->getBaseCurrencyCode();

        $discountLineItem = null;
        foreach ($result['displayItems'] as $displayItem) {
            if ($displayItem['label'] == $this->__('Discount')) {
                $discountLineItem = &$displayItem;
                break;
            }
        }

        if ($discountLineItem === null) {
            $discountLineItem = [];
            $result['displayItems'][] = &$discountLineItem;
        }

        $discountLineItem['label'] = $this->__('Discount');
        $discountLineItem['amount'] = $this->getAmountCents($discountAmount, $currency);

        return $result;
    }
}
