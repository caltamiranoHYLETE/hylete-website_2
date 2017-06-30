<?php

class Ebizmarts_BakerlooPayment_Model_Method_Iframe extends Ebizmarts_BakerlooPayment_Model_Method_Abstract {

	protected $_isIframe = true;

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Method_Purchaseorder
     */
    public function assignData($data) {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $this->getInfoInstance()->setPoNumber($data->getData('payReference'));
        return $this;
    }

    protected function _verifyRequiredData($postData){
        return true;
    }

    public function getReturnData($post = array()) {
        $returnData = array('default' => 'Default Response');
        return $returnData;
    }

}