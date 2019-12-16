<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_VcController extends Mage_Core_Controller_Front_Action
{
    public function dataserviceAction()
    {
        $result = array('error' => true);

        $vcService = Mage::getModel('cybersourcevisacheckout/soapapi_visacheckout');

        try {
            if ($this->_expireAjax()) {
                throw new Exception('Session is expired.');
            }

            if (! $vcOrderId = $this->getRequest()->getParam('vcorderid')) {
                throw new Exception('VC order id is undefined.');
            }

            if (! $response = $vcService->requestDataService($vcOrderId)) {
                throw new Exception('Unable to retrieve VC init data.');
            }

            $result['error'] = false;
        } catch (Exception $e) {
            Mage::helper('cybersourcevisacheckout')->log('VC Data Service failed: ' . $e->getMessage(), true);
            $result['msg'] = $e->getMessage();
        }

        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}

    private function _expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError()
            || $this->getOnepage()->getQuote()->getIsMultiShipping()
        ) {
            return true;
        }
        return false;
    }
    
    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    private function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }
}
