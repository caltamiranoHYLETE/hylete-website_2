<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Block_SOPWebMobile_Info_Pay extends Mage_Payment_Block_Info
{
    private $possibleInfoKeys = array(
        'reasonCode' => 'Reason Code',
        'authenticationResult' => 'Authentication Result',
        'authenticationStatusMessage' => 'Authentication Status Message',
        'commerceIndicator' => 'Commerce Indicator',
        'eciRaw' => 'ECI Raw',
        'eci' => 'ECI',
        'xid' => 'XID',
        'cavv' => 'CAVV',
        'ucafAuthenticationData' =>'UCAF Authentication Data',
        'ucafCollectionIndicator' => 'UCAF Collection Indicator',
        'paresStatus' => 'paRes Status',
        'veresEnrolled' => 'veRes Enrolled',
        'cavvAlgorithm' => 'CAVV Algorithm'
    );

    /**
     * Prepares information
     * @param null $transport
     * @return null|Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
	{
		if (null !== $this->_paymentSpecificInformation) {
			return $this->_paymentSpecificInformation;
		}

		$info = $this->getInfo();
		$transport = new Varien_Object();
		$transport = parent::_prepareSpecificInformation($transport);

        if ($info->getCcLast4()) {
            $transport->addData(array(
                Mage::helper('payment')->__('Credit Card #') => $info->getCcLast4(),
                Mage::helper('payment')->__('Card Type') => Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getCCname($info->getCcType()),
            ));
        }

        if ($echeckRouting = $info->getAdditionalInformation('echeck_routing_masked')) {
            $transport->addData(array(
                Mage::helper('payment')->__('ECheck Routing #') => $echeckRouting
            ));
        }

        if ($echeckAccount = $info->getAdditionalInformation('echeck_account_masked')) {
            $transport->addData(array(
                Mage::helper('payment')->__('ECheck Account #') => $echeckAccount
            ));
        }

		if ($this->getIsSecureMode()) {
		    return $transport;
        }

        $successCodes = explode(',',str_replace(' ', '', Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('forceavs_codes')));
        $successCodes = count($successCodes) ? $successCodes : Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getAvsSuccessVals();
        if ($avs = $info->getAdditionalInformation('cc_avs_status')) {
            $avstext = in_array($avs, $successCodes) ? "MATCH / PARTIAL MATCH" : "NO MATCH";
            $transport->addData(array(
                Mage::helper('payment')->__('AVS Result') => $avs . " - " . $avstext
            ));
        }

        $successCodes = explode(',',str_replace(' ','',Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('forcecvn_codes')));
        $successCodes = count($successCodes) ? $successCodes : Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getCvnSuccessVals();
        if ($cvn = $info->getAdditionalInformation('cc_cid_status')) {
            $cvntext = in_array($cvn, $successCodes) ? "MATCH" : "NO MATCH";
            $transport->addData(array(
                Mage::helper('payment')->__('CVN Result') => $cvn . " - " . $cvntext
            ));
        }

        // 3DS related info
        if ($xid = $info->getAdditionalInformation('payer_authentication_xid')) {
            $transport->addData(array(
                Mage::helper('payment')->__('XID') => $xid
            ));
        }

        if ($eci = $info->getAdditionalInformation('cybersourcesop_eci')) {
            $transport->addData(array(
                Mage::helper('payment')->__('ECI') => $eci
            ));
        }

        if ($cavv = $info->getAdditionalInformation('cybersourcesop_cavv')) {
            $transport->addData(array(
                Mage::helper('payment')->__('CAVV') => $cavv
            ));
        }

        foreach ($this->possibleInfoKeys as $key => $title) {
            if (! $value = $info->getAdditionalInformation($key)) {
                continue;
            }

            $transport->addData(array(
                Mage::helper('payment')->__($title) => $value
            ));
        }

		return $transport;
	}
}
