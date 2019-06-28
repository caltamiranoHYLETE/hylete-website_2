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
        if ($this->_getAddress()->getBaseDiscountAmount()) {
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
}
