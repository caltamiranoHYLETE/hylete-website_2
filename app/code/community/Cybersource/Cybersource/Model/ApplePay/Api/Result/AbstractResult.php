<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

abstract class Cybersource_Cybersource_Model_ApplePay_Api_Result_AbstractResult
{
    private $result;

    /**
     * Sets the instance of the result type.  This method should not need to be called unless there is a significant
     * customization to how tax handling is managed
     *
     * @param $result
     */
    public function setApiResult($result)
    {
        /** Used for storing data in a format that the unserializer can understand.  The Cybersource API returns a result
         * of type stdClass, but the Magento unserializer does not always understand objects.  Therefore we cast to
         * an array recursively.
         */

        $result = json_decode(json_encode($result), true);
        $this->result = $result;
    }

    /**
     * Provides the raw, unprocessed, data from the result call.
     *
     * @return array
     */
    public function getRawResult()
    {
        return $this->result;
    }

    /**
     * Does the result object indicate a successful API call?
     *
     * @return bool
     */
    abstract function isSuccess();
}
