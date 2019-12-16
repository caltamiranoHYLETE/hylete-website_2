<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Block_BankTransfer_Form_Banktransfer extends Mage_Payment_Block_Form
{
    const CACHE_TAG = 'CS_DATA';
    const CACHE_KEY = 'cs_ideal_banks';
    const CACHE_LIFETIME = 14400; // 4 hours

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cybersourcebt/form/banktransfer.phtml');
    }

    public function getIdealBankOptions()
    {
        // hardcoded bank for test purposes
        if (Mage::helper('cybersource_core')->getIsTestMode()) {
            return array(
                new Varien_Object(
                    array(
                        'id' => 'ideal-FVLBNL22',
                        'name' => 'Test Bank'
                    )
                )
            );
        }

        if ($banks = Mage::app()->loadCache(self::CACHE_KEY)) {
            return json_decode($banks);
        }

        try {
            $result = Mage::getModel('cybersourcebanktransfer/soapapi_banktransfer')->requestIdealBanks();

            if (! is_array($result->apOptionsReply->option)) {
                throw new Exception('Invalid respponse: unable to pull iDEAL bank list.');
            }

            Mage::app()->saveCache(
                json_encode($result->apOptionsReply->option),
                self::CACHE_KEY,
                array(self::CACHE_TAG),
                self::CACHE_LIFETIME
            );

            return $result->apOptionsReply->option;

        } catch (Exception $e) {
            Mage::helper('cybersourcebanktransfer')->log($e->getMessage(), true);
        }

        return false;
    }
}
