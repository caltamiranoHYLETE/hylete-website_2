<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_Atp_Result
{
    const STATUS_ACCEPT = 'ACCEPT';
    const STATUS_CHALLENGE = 'CHALLENGE';
    const STATUS_REJECT = 'REJECT';

    private $result;

    protected $understoodReturns = array(
        self::STATUS_REJECT,
        self::STATUS_ACCEPT,
        self::STATUS_CHALLENGE,
    );

    public function __construct(
        $result
    )
    {
        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function isValid()
    {
        return isset($this->result->decision) && in_array($this->result->decision, $this->understoodReturns);
    }

    public function getDecision()
    {
        if ($this->isValid()) {
            return $this->result->decision;
        }
        return Mage::getStoreConfig(Cybersource_Cybersource_Helper_Atp_Api::CONFIG_ACTION_ON_ERROR);
    }

    public function accept()
    {
        return $this->getDecision() == self::STATUS_ACCEPT;
    }

    public function challenge()
    {
        return $this->getDecision() == self::STATUS_CHALLENGE;
    }

    public function reject()
    {
        return $this->getDecision() == self::STATUS_REJECT;
    }

}
