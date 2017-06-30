<?php

class Ebizmarts_BakerlooRestful_Model_Pincode extends Mage_Core_Model_Abstract {

    public function _construct() {
        $this->_init('bakerloo_restful/pincode');
    }

    public function resetPincode() {

        $newPin = $this->_newPin();

        while(Mage::helper('bakerloo_restful')->existsPin($newPin, $this))
            $newPin = $this->_newPin();

        $newPin = Mage::helper('core')->encrypt($newPin);

        $this->setCode($newPin)->save();
        return $this;
    }

    public function _newPin() {
        $pinLength = (int)Mage::helper("bakerloo_restful")->config("general/pin_code_length");

        $pin = '';
        do {
            $pin .= mt_rand(0, 9);
            $pinLength--;
        }
        while($pinLength>0);

        return $pin;
    }

    /**
     * @param $pin
     * @return bool
     *
     * Checks that a user-generated pincode is valid
     * A pincode is valid if it contains only numeric values, is of configured length, and is unique
     */
    public function validatePincode($pin){
        $pinLength = (int)Mage::helper("bakerloo_restful")->config("general/pin_code_length");

        if(!is_int($pin) && !ctype_digit((string)$pin))
            return 'Pin should consist of numeric values only.';

        if($pinLength !== strlen((string) $pin))
            return 'Invalid pin length (pin should have ' . $pinLength . ' digits).';

        if (Mage::helper('bakerloo_restful')->existsPin($pin, $this))
            return 'Duplicate pin.';

        return true;
    }

    /**
     * @param $pin
     * @return $this
     * @throws Mage_Exception
     */
    public function savePincode($pin){
        $validate = $this->validatePincode($pin);

        if($validate === true){
            $pin = Mage::helper('core')->encrypt($pin);
            $this->setCode($pin)->save();
            return $this;
        }
        else
            throw new Mage_Exception($validate);
    }

    /**
     * Retrieve data
     *
     * @param   string $key
     * @param   mixed $index
     * @return unknown
     */
    public function getData($key = '', $index = null) {

        if(empty($key)) {
            if (array_key_exists('code', $this->_data) && empty($this->_data['pincode'])) {
                $this->_data['pincode'] = Mage::helper('core')->decrypt($this->getCode());
            }

        }
        else {
            if ('pincode'===$key) {
                if (empty($this->_data['pincode'])) {
                    $this->_data['pincode'] = Mage::helper('core')->decrypt($this->getCode());
                }
            }
        }

        return parent::getData($key, $index);

    }

}