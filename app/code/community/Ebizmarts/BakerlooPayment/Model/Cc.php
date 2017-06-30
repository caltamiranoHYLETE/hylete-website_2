<?php

class Ebizmarts_BakerlooPayment_Model_Cc extends Ebizmarts_BakerlooPayment_Model_Method_Cc {

    protected $_code        = "bakerloo_creditcard";
    protected $_canSaveCc   = true;
    protected $_formBlockType = 'payment/form_ccsave';
    protected $_infoBlockType = 'bakerloo_payment/info_savedcc';

}