<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_ApplePay_Api_Result_Authorize extends Cybersource_Cybersource_Model_ApplePay_Api_Result_AbstractResult
{
    /**
     * API result for an accept decision
     */
    const RESULT_DECISION_ACCEPT = 'ACCEPT';

    /**
     * Api result for a review decision
     */
    const RESULT_DECISION_REVIEW = 'REVIEW';
    /**
     * Api result for a reject decision
     */
    const RESULT_DECISION_REJECT = 'REJECT';

    /**
     * What are all the response codes indicating a successful response?
     *
     * @var array
     */
    protected $successfulResponseCodes = array(
        100, 480
    );

    /**
     * What are the response codes for a transaction requiring a review?
     *
     * @var array
     */
    protected $reviewResponseCodes = array(
        480
    );

    /**
     * Does the result object indicate a successful API call?
     *
     * @return bool
     */
    public function isSuccess()
    {
        $responseCode = $this->getResponseCode();
        return
            !empty($responseCode)
            && in_array($responseCode, $this->successfulResponseCodes);
    }

    /**
     * Retrieve the response code from the API, if successful
     *
     * @return null|string
     */
    public function getResponseCode()
    {
        if (!empty($this->getRawResult()['reasonCode'])) {
            return $this->getRawResult()['reasonCode'];
        }
        return null;
    }

    /**
     * Does the transaction require a review in the Cybersource UI?
     *
     * @return bool
     */
    public function isReviewRequired()
    {
        if ($this->isSuccess()) {
            $responseCode = $this->getResponseCode();
            return in_array($responseCode, $this->reviewResponseCodes);
        }
        return false;
    }

    /**
     * What is the decision result?  May not match the response code
     *
     * @return string
     */
    public function getDecision()
    {
        if (!empty($this->getRawResult()['decision'])) {
            return $this->getRawResult()['decision'];
        }
        return '';
    }

    /**
     * What is the request token?
     *
     * @return string
     */
    public function getRequestToken()
    {
        if (!empty($this->getRawResult()['requestToken'])) {
            return $this->getRawResult()['requestToken'];
        }
        return '';
    }

    /**
     * Retrieve the first numbers of the credit card provided.
     *
     * @return string
     */
    public function getCardPrefix()
    {
        if ($this->isSuccess()) {
            $result = $this->getRawResult();
            if (!empty($result['token']['prefix'])) {
                return $result['token']['prefix'];
            }
        }
        return '';
    }

    /**
     * Get the request ID as a response from the API
     *
     * @return string
     */
    public function getRequestId()
    {
        if ($this->isSuccess()) {
            $result = $this->getRawResult();
            if (!empty($result['requestID'])) {
                return $result['requestID'];
            }
        }
        return '';
    }

    /**
     * Get the credit card network for the transaction
     *
     * @return string
     */
    public function getNetwork()
    {
        if ($this->isSuccess()) {
            $result = $this->getRawResult();
            if (!empty($result['token']['paymentMethod']['network'])) {
                return $result['token']['paymentMethod']['network'];
            }
        }
        return '';
    }

    /**
     * Get the expiration month for the card from the transaction
     *
     * @return string
     */
    public function getExpirationMonth()
    {
        if ($this->isSuccess()) {
            $result = $this->getRawResult();
            if (!empty($result['token']['expirationMonth'])) {
                return $result['token']['expirationMonth'];
            }
        }
        return '';
    }

    /**
     * Get the expiration year for the card from the transaction
     *
     * @return string
     */
    public function getExpirationYear()
    {
        if ($this->isSuccess()) {
            $result = $this->getRawResult();
            if (!empty($result['token']['expirationYear'])) {
                return $result['token']['expirationYear'];
            }
        }
        return '';
    }
}
