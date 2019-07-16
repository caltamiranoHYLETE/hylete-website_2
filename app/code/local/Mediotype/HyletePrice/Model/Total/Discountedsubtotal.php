<?php

class Mediotype_HyletePrice_Model_Total_Discountedsubtotal extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function __construct()
    {
        $this->setCode('hyleteprice_discountedsubtotal');
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        if ($this->isDiscountApplied()) {
            return Mage::helper('mediotype_hyleteprice')->__('Discounted Subtotal');
        }
        return Mage::helper('mediotype_hyleteprice')->__('Subtotal');
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     * @return $this
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        if (($address->getAddressType() == 'billing')) {
            return $this;
        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     * @return $this
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if (($address->getAddressType() == 'billing')) {
            $this->_setAddress($address);
            $amount = $address->getQuote()->getSubtotalWithDiscount();
            if ($amount != 0) {
                $address->addTotal(array(
                    'code' => $this->getCode(),
                    'title' => $this->getLabel(),
                    'value' => $amount
                ));
            }
        }

        return $this;
    }

    /**
     * Check if any discount is applied
     *
     * @return bool
     */
    private function isDiscountApplied()
    {
        $quote = $this->_getAddress()->getQuote();
        $subtotal = $quote->getBaseSubtotal();
        $subtotalWithDiscount = $quote->getBaseSubtotalWithDiscount();

        return $subtotal > $subtotalWithDiscount;
    }
}
