<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Helper_SOPWebMobile_Security extends Mage_Core_Helper_Abstract
{
    /**
     * Returns signed Checkout Form Fields as a string
     * @param array $params Checkout Form Fields
     * @param string $secretKey
     * @return string
     */
    public function sign($params, $secretKey)
    {
		return base64_encode(hash_hmac('sha256', $this->buildDataToSign($params), $secretKey, true));
	}

	/**
	 * Validate the response from Cybersource
	 *
	 * @param string $secretkey
	 * @param array $params
	 * @return boolean
	 */
	public function validateResponse($secretkey, $params)
    {
        if (empty($params['req_reference_number']) || empty($params['signature'])) {
            return false;
        }

        return strcmp($this->sign($params, $secretkey), $params['signature']) == 0;
    }

    /**
     * Prepares the fields to be signed from Checkout Form Fields
     *
     * @param array $params Contains Checkout Form Fields
     * @return string
     */
    private function buildDataToSign($params)
    {
        $dataToSign = array();
		$signedFieldNames = explode(",", $params["signed_field_names"]);
		foreach ($signedFieldNames as $field) {
			$dataToSign[] = $field . "=" . $params[$field];
		}
		return implode(",", $dataToSign);
	}
}
