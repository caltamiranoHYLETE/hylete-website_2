<?php

class Ebizmarts_BakerlooRestful_Model_Shift extends Mage_Core_Model_Abstract {

    public function _construct() {
        $this->_init('bakerloo_restful/shift');
    }

    public function getOpenAmounts() {
        return json_decode($this->getJsonOpenCurrencies());
    }

    public function getCloseAmounts() {
        return json_decode($this->getJsonCloseCurrencies());
    }

    public function getVatBreakdown() {
        return json_decode($this->getJsonVatbreakdown());
    }

    public function getNextdayCurrencies() {
        return json_decode($this->getJsonNextdayCurrencies());
    }

    public function getNextdayCurrenciesByCode() {
        $nextday = $this->getNextdayCurrencies();

        if(!is_array($nextday))
            $nextday = array();

        $result = array();

        foreach($nextday as $_next)
            $result[$_next->currency_code] = $_next;

        return $result;
    }

}
