<?php

class Ebizmarts_BakerlooPayment_Model_Method_Abstract extends Mage_Payment_Model_Method_Abstract {

    protected $_canUseInternal              = false;
    protected $_canUseCheckout              = false;
    protected $_canUseForMultishipping      = false;

    protected $_isInitializeNeeded          = true;

    protected $_infoBlockType = 'bakerloo_payment/info_default';

    protected $_isIframe = true;

    public function isIframe() {
        return $this->_isIframe;
    }

    public function isActive($storeId = null) {
        return (bool)(int)$this->getConfigData('active', $storeId);
    }

    /**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function initialize($paymentAction, $stateObject) {

        $generateInvoice  = (int)$this->getConfigData('invoice');

        if($generateInvoice) {

            //If config is set to Generate Shipment, Magento will change order to COMPLETE automatically.
            $state  = Mage_Sales_Model_Order::STATE_PROCESSING;
            $status = Mage_Sales_Model_Order::STATE_PROCESSING;

        }
        else {
            $state  = Mage_Sales_Model_Order::STATE_NEW;
            $status = 'pending';
        }

        $stateObject->setState($state);
        $stateObject->setStatus($status);
        $stateObject->setIsNotified(false);

        return $this;
    }

}