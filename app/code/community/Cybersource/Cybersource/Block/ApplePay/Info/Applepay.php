<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Block_ApplePay_Info_Applepay extends Mage_Payment_Block_Info
{
    /**
     * Prepares information to display on the admin UI screen for a particular order
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
		// If it's a quote (which means this will be rendered on the payment page), we have nothing to display
        if ($info instanceof Mage_Sales_Model_Quote_Payment) {
            return $transport;
        }

		$token = (array)$info->getAdditionalInformation('token');
		$response = (array)$info->getAdditionalInformation('response');

		// We only need this messaging if the order has been processed
        if ($info instanceof Mage_Sales_Model_Order_Payment) {
            if (!$response || !$token) {
                $transport->setData(
                    $this->getApplePayHelper()->__('WARNING'),
                    $this->getApplePayHelper()->__('Missing transactional data')
                );
                return $transport;
            }
        }

		$isSecure = $this->getIsSecureMode();
		if ($isSecure) {
            $this->buildCustomerResult($token, $response, $transport);
		} else {
            $this->buildAdminResult($token, $response, $transport);
        }

		return $transport;
	}

    /**
     * Builds the result object for
     *
     * @param array $token
     * @param array $response
     * @param Varien_Object $transport
     */
	protected function buildCustomerResult(array $token, array $response, Varien_Object $transport)
    {
        $this->addResultToTransport($response, 'requestID', 'Transaction ID', $transport);
        $this->addResultToTransport($token, 'token/paymentMethod/network', 'Credit Card Type', $transport);
        $this->addResultToTransport($token, 'token/paymentMethod/displayName', 'Cardholder Name', $transport);
        $this->addResultToTransport($response, 'token/suffix', 'Last 4 Card Digits', $transport);
        $this->addResultToTransport($response, 'token/expirationMonth', 'Card Expiration Month', $transport);
        $this->addResultToTransport($response, 'token/expirationYear', 'Card Expiration Year', $transport);

        // Allows 3rd party developers to modify what customers see via events
        Mage::dispatchEvent(
            'cybersource_apple_pay_customer_info_result',
            array(
                'transport' => $transport,
                'token' => $token,
                'response' => $response
            )
        );
    }

    /**
     * Build the result for the admin order page, which has need of more data than the customer page.
     *
     * @param array $token
     * @param array $response
     * @param Varien_Object $transport
     */
	protected function buildAdminResult(array $token, array $response, Varien_Object $transport)
    {
        $this->addResultToTransport($response, 'requestID', 'Cybersource Request ID', $transport);
        $this->addResultToTransport($token, 'token/paymentMethod/network', 'Card Network', $transport);
        $this->addResultToTransport($token, 'token/paymentMethod/displayName', 'Cardholder Name', $transport);
        $this->addResultToTransport($token, 'token/transactionIdentifier', 'Apple Pay Transaction Identifier', $transport);

        $this->addResultToTransport($response, 'token/suffix', 'Last 4 Card Digits', $transport);
        $this->addResultToTransport($response, 'token/expirationMonth', 'Card Expiration Month', $transport);
        $this->addResultToTransport($response, 'token/expirationYear', 'Card Expiration Year', $transport);

        $this->addResultToTransport($response, 'decision', 'Processor Decision', $transport);
        $this->addResultToTransport($response, 'purchaseTotals/currency', 'Purchase Currency', $transport);
        $this->addResultToTransport($response, 'ccAuthReply/authorizationCode', 'Authorization Code', $transport);
        $this->addResultToTransport($response, 'ccAuthReply/acsCode', 'AVS Code', $transport);
        $this->addResultToTransport($response, 'ccAuthReply/reconciliationID', 'Reconciliation ID', $transport);

        // Allows 3rd party developers to modify what admins see via events
        Mage::dispatchEvent(
            'cybersource_apple_pay_admin_info_result',
            array(
                'transport' => $transport,
                'token' => $token,
                'response' => $response
            )
        );
    }

    /**
     * Add a label/value to the transport
     *
     * @param $response
     * @param $key
     * @param $label
     * @param Varien_Object $transport
     */
	private function addResultToTransport($response, $key, $label, Varien_Object $transport)
    {
        $value = $this->traverseResult($response, $key);
        if (!empty($value)) {
            $transport->setData(
                $this->getApplePayHelper()->__($label),
                $this->getApplePayHelper()->escapeHtml($value)
            );
        }
    }

    /**
     * Traverses the response array to render the nested value
     *
     * @param $source
     * @param $key
     * @return string
     */
    private function traverseResult($source, $key)
    {
        $source = (array)$source;
        if (($pos = strpos($key, '/')) !==false) {
            $baseKey = substr($key, 0, $pos);
            $nextKey = substr($key, $pos + 1);
            if (empty($source[$baseKey])) {
                return '';
            }
            return $this->traverseResult($source[$baseKey], $nextKey);
        }
        if (!empty($source[$key])) {
            return $source[$key];
        }
        return '';
    }

    /**
     * Get the Apple Pay helper
     *
     * @return Cybersource_Cybersource_Helper_ApplePay_Data
     */
	private function getApplePayHelper()
    {
        return Mage::helper(Cybersource_Cybersource_Helper_ApplePay_Data::GROUP_NAME);
    }

}
