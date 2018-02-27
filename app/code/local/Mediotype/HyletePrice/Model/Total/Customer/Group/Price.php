<?php

class Mediotype_HyletePrice_Model_Total_Customer_Group_Price extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function __construct()
    {
        $this->setCode('hyleteprice_customergroupprice');
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        $hyletePriceHelper = Mage::helper("mediotype_hyleteprice");
        return $hyletePriceHelper->__($hyletePriceHelper->getPriceLabelByCustomerGroup());
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
            $hyletePriceHelper = Mage::helper("mediotype_hyleteprice");
            $quote = $address->getQuote();
            $amount = Mage::helper('checkout')->formatPrice($hyletePriceHelper->quoteSalesRulesForMsrpCalculation($quote));
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
}