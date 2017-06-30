<?php

class Ebizmarts_BakerlooPayment_Model_Banktransfer extends Ebizmarts_BakerlooPayment_Model_Method_Abstract {

    protected $_code  = "bakerloo_banktransfer";

    protected $_canUseInternal              = false;
    protected $_canUseCheckout              = false;
    protected $_canUseForMultishipping      = false;

}