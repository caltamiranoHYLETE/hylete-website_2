<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_Core_Avs_Validator extends Mage_Core_Model_Abstract
{
    const CLIENT_LIBRARY_VERSION = "CyberSource PHP 1.0.0";
    
    public function execute()
    {
		$addressdata        = $this->getData();
		$cbavs_street1      = isset($addressdata['street1'])?$addressdata['street1']:'';
		$cbavs_street2      = isset($addressdata['street2'])?$addressdata['street2']:'';
		$cbavs_city         = isset($addressdata['city'])?$addressdata['city']:'';
		$cbavs_state        = isset($addressdata['state'])?$addressdata['state']:'';
		$cbavs_postalcode   = isset($addressdata['postcode'])?$addressdata['postcode']:'';
		$cbavs_country      = isset($addressdata['country_id'])?$addressdata['country_id']:'';
        $merchantReferenceCode  = Mage::helper('core')->uniqHash();
        //@TODO:: Populate the request xml with address fields.
        $request_xml = '<requestMessage xmlns="urn:schemas-cybersource-com:transaction-data-1.23">
		   <merchantID>'.Mage::helper('cybersource_core')->getMerchantId().'</merchantID>
		   <merchantReferenceCode>'.$merchantReferenceCode.'</merchantReferenceCode>
		   <billTo>
			  <street1>'.$cbavs_street1.'</street1>';
        if(isset($cbavs_street2) && trim($cbavs_street2)!='') {
            $request_xml .= '<street2>'.$cbavs_street2.'</street2>';
        }
		$request_xml .=	'<city>'.$cbavs_city.'</city>
			  <state>'.$cbavs_state.'</state>
			  <postalCode>'.$cbavs_postalcode.'</postalCode>
			  <country>'.$cbavs_country.'</country>
		   </billTo>
		   <davService run="true"/>
		</requestMessage>';				
        $request =  $this->createRequest($merchantReferenceCode);
        $simpleXml = simplexml_load_string($request_xml);
        $xmlRequest = $this->simpleXmlToCybsRequest($simpleXml);
        $mergedRequest = (object) array_merge((array) $request, (array) $xmlRequest);

        $soapClient = $this->getSoapClient();
        $response = $soapClient->runTransaction($mergedRequest); 
        return $response;
    }
	
	 /**
     * Returns an object initialized with basic client information.
     *
     * @param string $merchantReferenceCode Desired reference code for the request
     * @return stdClass An object initialized with the basic client info.
     */
    public function createRequest($merchantReferenceCode)
    {
        $request = new stdClass();
        $request->merchantID = Mage::helper('cybersource_core')->getMerchantId();
        $request->merchantReferenceCode = $merchantReferenceCode;
        $request->clientLibrary = self::CLIENT_LIBRARY_VERSION;
        $request->clientLibraryVersion = phpversion();
        $request->clientEnvironment = php_uname();
        return $request;
    }
    
    /**
     * Returns a properly formatted request object from a SimpleXMLElement. 
     *
     * @param SimpleXMLElement $simpleXml Representation of an XML structure
     * @return stdClass A request with the data from the SimpleXMLElement.
     */
    public function simpleXmlToCybsRequest($simpleXml)
    {
        $vars = get_object_vars($simpleXml);
        $request = new stdClass();
        foreach(array_keys($vars) as $key)
        {
            $element = $vars[$key];
            if ($key == 'comment') {
                continue;
            }
            if (is_string($element)) {
                $request->$key = $element;
            } else if (is_array($element)) {
                $array = $element;
                if ($key == "@attributes") {
                    // Each attribute in the '@attributes' array should
                    // instead be a property of the parent element.
                    foreach($array as $k => $value) {
                        $request->$k = $value;
                    }
                } else {
                    $newArray = array();
                    foreach($array as $k => $value) {
                        $newArray[$k] = $this->simpleXmlToCybsRequest($value);
                    }
                    $request->$key = $newArray; 
                }
            } else if ($element instanceof SimpleXMLElement) {
                $request->$key = $this->simpleXmlToCybsRequest($element);
            }
        }
        return $request;
    }

    /**
     * @return Cybersource_Cybersource_Model_Core_Soap_Client
     */
    public function getSoapClient()
    {
        $wsdlPath = Mage::helper('cybersource_core')->getWsdlUrl();
        $client = new Cybersource_Cybersource_Model_Core_Soap_Client($wsdlPath);

        $client->setContext(self::class);
        $client->setLogFilename(Cybersource_Cybersource_Helper_Core_Avs::LOGFILE);
        $client->setPreventLogFlag(!Mage::helper('cybersource_core/avs')->isDebugMode());

        return $client;
    }
}
