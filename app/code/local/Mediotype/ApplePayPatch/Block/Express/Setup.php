<?php

/**
 * Express setup configuration block for Apple Pay.
 * @category  Class
 * @package   Mediotype_ApplePayPatch
 * @author    Rick Buczynski <rick@mediotype.com>
 * @copyright 2018 Mediotype
 */

class Mediotype_ApplePayPatch_Block_Express_Setup extends Gene_ApplePay_Block_Express_Setup
{
    /**
     * Fetch the quote grand total.
     * @return float
     */
    public function getQuoteGrandTotal()
    {
        return $this->helper('core')->currency(
            $this->getQuote()->getGrandTotal(),
            false,
            false
        );
    }
}
