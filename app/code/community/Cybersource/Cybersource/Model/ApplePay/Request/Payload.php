<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_ApplePay_Request_Payload
{
    /**
     * The actual payload
     *
     * @var array
     */
    private $params;

    /**
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * Is the request data valid
     *
     * @return bool
     */
    public function isValid()
    {
        $hasPaymentData = (boolean)$this->getPaymentData();
        return $hasPaymentData;
    }

    /**
     * Retrieve the request payload
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get the data
     *
     * @return string
     */
    public function getPaymentData()
    {
        $node = $this->getPaymentDataNode();
        if (!empty($node['data'])) {
            return $node['data'];
        }
        return '';
    }

    /**
     * Get the payment data from the token
     *
     * @return string
     */
    public function getPaymentDataNode()
    {
        if (!empty($this->params['token']['paymentData'])) {
            return $this->params['token']['paymentData'];
        }
        return '';
    }

    /**
     * Retrieve the version of request from the token
     *
     * @return string
     */
    public function getPaymentVersion()
    {
        if (!empty($this->params['token']['paymentData']['version'])) {
            return $this->params['token']['paymentData']['version'];
        }
        return '';
    }

    /**
     * Retrieve the payment method
     *
     * @return array
     */
    public function getPaymentMethod()
    {
        if (!empty($this->params['token']['paymentMethod'])) {
            return $this->params['token']['paymentMethod'];
        }
        return array();
    }

    /**
     * Get the name of the person from the payment request
     *
     * @return string
     */
    public function getDisplayName()
    {
        if (($paymentMethod = $this->getPaymentMethod()) && !empty($paymentMethod['displayName'])) {
            return $paymentMethod['displayName'];
        }
        return '';
    }

    /**
     * Get the card network from the payment request
     *
     * @return string
     */
    public function getCardNetwork()
    {
        if (($paymentMethod = $this->getPaymentMethod()) && !empty($paymentMethod['network'])) {
            return $paymentMethod['network'];
        }
        return '';
    }

    /**
     * Get the transaction identifier from the token
     *
     * @return string
     */
    public function getTransactionIdentifier()
    {
        if (!empty($this->params['token']['transactionIdentifier'])) {
            return $this->params['token']['transactionIdentifier'];
        }
        return '';
    }

    /**
     * Is the response from the device coming from the simulator?
     *
     * @return bool
     */
    public function isSimulator()
    {
        $hasPaymentData = (boolean)$this->getPaymentData();
        $hasPaymentMethod = (boolean)$this->getPaymentMethod();
        if (
            !$hasPaymentData
            && $hasPaymentMethod
            && $this->getDisplayName() == 'Simulated Instrument'
            && $this->getTransactionIdentifier() == 'Simulated Identifier') {
            return true;
        }
        return false;
    }

}
