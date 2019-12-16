<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_BankTransfer_Payment_Ideal extends Cybersource_Cybersource_Model_BankTransfer_Payment_Abstract
{
    const CODE = 'csbtideal';

    const CYBERSOURCE_CODE = 'IDL';

    protected $_code = self::CODE;
    
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
			$data = new Varien_Object($data);
		}

        if ($bankId = $data->getCybersourceBtBankId()) {
            $this->getInfoInstance()->setAdditionalInformation('btBankId', $bankId);
        }

		return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function validate()
    {
        parent::validate();

        if (! $this->getInfoInstance()->getAdditionalInformation('btBankId')){
            throw new Exception('Bank id is undefined.');
        }

        return $this;
    }
}
