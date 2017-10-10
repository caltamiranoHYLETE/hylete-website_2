<?php
require_once("config.php");
require_once("wufoo_api/WufooApiWrapper.php");

function GetWufooEntry($wufooId) {
	$wrapper = new WufooApiWrapper(WUFOO_API, "hylete");
	$args = "Filter1=EntryId+Is_equal_to+".$wufooId;
	return $wrapper->getEntries("k1tviw2p17gle16", 'forms', $args);	
}

function GetWufooRegistrationEntry($wufooId) {
	$wrapper = new WufooApiWrapper(WUFOO_API, "hylete");
	$args = "Filter1=EntryId+Is_equal_to+".$wufooId;
	return $wrapper->getEntries("z7p7z3", 'forms', $args);	
}

function GetWufooRegistrationEntryByFormID($wufooId, $formID) {
	$wrapper = new WufooApiWrapper(WUFOO_API, "hylete");
	$args = "Filter1=EntryId+Is_equal_to+".$wufooId;
	return $wrapper->getEntries($formID, 'forms', $args);	
}

/*
  This prints the Wufoo Array so you can see all the fields easily
*/
function print_a($subject){
	echo str_replace("=>","&#8658;",str_replace("Array","<font color=\"red\"><b>Array</b></font>",nl2br(str_replace(" "," &nbsp; ",print_r($subject,true)))) . '<br />');
}

function getClean($strVar) {
	if(empty($_GET)) {
		return "";
	} else {
		if(isset($_GET[$strVar])) {
			return $_GET[$strVar];   
		} else {
			return "";
		}
	}	
}

function getPost($strVar) {
	if(empty($_POST)) {
		return "";
	} else {
		if(isset($_POST[$strVar])) {
			return $_POST[$strVar];   
		} else {
			return "";
		}
	}	
}

function cBoolString($bln) {
	if (is_null($bln) || $bln == "") {
		return "Not Yet";
	} else {
		if($bln==1) {
			return "Yes";
		}elseif($bln==0) {
			return "No";
		}
	}
}

//Will format phone numbers if you want to - Not in use
function formatPhone($num)
{
    $num = ereg_replace('[^0-9]', '', $num);

    $len = strlen($num);
    if($len == 7)
        $num = preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $num);
    elseif($len == 10)
        $num = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $num);

    return $num;
}

//Writes out the Page 2 Return information
function createPage2($entry) {

	$shipfrom['Name'] = $entry->Field1. " " . $entry->Field2;
    $shipfrom['AttentionName'] = $entry->Field1. " " . $entry->Field2;;
    $addressFrom['AddressLine'] = $entry->Field4;
    $addressFrom['City'] = $entry->Field6;
    
	if(strlen($entry->Field7) > 2) {
		$strState = convertState($entry->Field7);
	} else {
		$strState = strtoupper($entry->Field7);
	}
	$addressFrom['StateProvinceCode'] = $strState;
    $addressFrom['PostalCode'] = $entry->Field8;
	
	$Page2 = "<p><b>Please place this page inside your shipment</b></p>".
		"<p><b>Name:</b> ".$entry->Field1." ".$entry->Field2."</p>".
		"<p><b>Email:</b> ".$entry->Field856."</p>".
		"<p><b>Phone Number:</b> ".$entry->Field20."</p>".
		"<p><b>Address:</b><br>".$entry->Field4."<br>".addBR($entry->Field5).$entry->Field6.", ".$entry->Field7." ".$entry->Field8."<br>".$entry->Field9."</p>".
		"<p><b>Order Number:</b> ".$entry->Field12."</p>".
		"<p><b>Name Associated With Order:</b> ".$entry->Field30."</p>".
		"<p><b>Number of Items Returning:</b> ".$entry->Field10."</p>".
		writeItemReturn("1",$entry->Field14, $entry->Field255, $entry->Field256, $entry->Field873).
		writeItemReturn("2",$entry->Field26, $entry->Field755, $entry->Field756, $entry->Field919).
		writeItemReturn("3",$entry->Field32, $entry->Field655, $entry->Field656, $entry->Field876).
		writeItemReturn("4",$entry->Field37, $entry->Field555, $entry->Field556, $entry->Field914).
		writeItemReturn("5",$entry->Field36, $entry->Field455, $entry->Field456, $entry->Field915).
		writeItemReturn("6",$entry->Field35, $entry->Field355, $entry->Field356, $entry->Field916).
		writeItemReturn("7",$entry->Field45, $entry->Field55, $entry->Field56, $entry->Field917).
		writeItemReturn("8",$entry->Field44, $entry->Field155, $entry->Field156, $entry->Field918).
		"<p><b>Comments:</b> ".$entry->Field53."</p>";
	
	return $Page2;
}

function writeItemReturn($strNum, $strItem, $strEx, $strRet, $strReason) {
	
	if($strItem != "") {
		$return = "<p><b>Item #".$strNum.":</b> ".$strItem."<br>".
		"<b>".strtoupper($strEx).strtoupper($strRet)."</b>: ".$strReason."</p>";
	} else {
		$return = "";
	}
	
	return $return;

}

function addBR($strToCheck) {
	
	if($strToCheck != "") {
		$strRet = $strToCheck."<br>";	
	} else { 
		$strRet = "";
	}
	
	return $strRet;
}

//Create the mailing label image
function createLabel($entry) {
	
	//print_a($entry);

	$wsdl = "../lib/ups_api/Ship.wsdl";
	$operation = "ProcessShipment";
	$endpointurl = 'https://onlinetools.ups.com/webservices/Ship';
	//TESTING URL
	//$endpointurl = 'https://wwwcie.ups.com/webservices/Ship';
	$outputFileName = "XOLTResult.xml";
	
  try
  {
	$mode = array
	(
		 'soap_version' => 'SOAP_1_1',  // use soap 1.1 client
		 'trace' => 1
	);

	// initialize soap client
	$client = new SoapClient($wsdl , $mode);

	//set endpoint url
	$client->__setLocation($endpointurl);
	
	//create soap header
	$usernameToken['Username'] = UPS_USER;
	$usernameToken['Password'] = UPS_PWD;
	$serviceAccessLicense['AccessLicenseNumber'] = UPS_ACCESSKEY;
	$upss['UsernameToken'] = $usernameToken;
	$upss['ServiceAccessToken'] = $serviceAccessLicense;

	$header = new SoapHeader('http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0','UPSSecurity',$upss);
	$client->__setSoapHeaders($header);

	if(strcmp($operation,"ProcessShipment") == 0 )
	{
	
	//get response
	$resp = $client->__soapCall('ProcessShipment',array(processShipment($entry)));

	//print_a($resp);
	
	//Label Image
	$label = $resp->ShipmentResults->PackageResults->ShippingLabel->GraphicImage;
	$shipmentID = $resp->ShipmentResults->ShipmentIdentificationNumber;
	
	$fh = fopen("labels/label".$shipmentID.".gif", "w");
	fwrite($fh, base64_decode($label));
	fclose($fh);
	
	//HTML Image
	$HTMLlabel = $resp->ShipmentResults->PackageResults->ShippingLabel->HTMLImage;
	echo('<img height="392" width="651" alt="UPS Label" src="labels/label'.$shipmentID.'.gif">');
	
	//We decode the label and replace the end body tag with our Page 2 data.
	//$strHtml = base64_decode($HTMLlabel)
		//echo(str_replace($strHtml, "</body>", $strPage2."</body>");
		//get status
		//echo "Response Status: " . $resp->Response->ResponseStatus->Description ."\n";

		//save soap request and response to file
		$fw = fopen($outputFileName , 'w');
		fwrite($fw , "Request: \n" . $client->__getLastRequest() . "\n");
		fwrite($fw , "Response: \n" . $client->__getLastResponse() . "\n");
		fclose($fw);

	}
	else if (strcmp($operation , "ProcessShipConfirm") == 0)
	{
		//get response
		$resp = $client->__soapCall('ProcessShipConfirm',array(processShipConfirm()));

		//get status
		echo "Response Status: " . $resp->Response->ResponseStatus->Description ."\n";

		//save soap request and response to file
		$fw = fopen($outputFileName , 'w');
		fwrite($fw , "Request: \n" . $client->__getLastRequest() . "\n");
		fwrite($fw , "Response: \n" . $client->__getLastResponse() . "\n");
		fclose($fw);

	}
	else
	{
		$resp = $client->__soapCall('ProcessShipeAccept',array(processShipAccept()));

		//get status
		echo "Response Status: " . $resp->Response->ResponseStatus->Description ."\n";

		//save soap request and response to file
		$fw = fopen($outputFileName ,'w');
		fwrite($fw , "Request: \n" . $client->__getLastRequest() . "\n");
			fwrite($fw , "Response: \n" . $client->__getLastResponse() . "\n");
			fclose($fw);
	}

  }
  catch(Exception $ex)
  {
  		
		echo("There was an error creating your shipping label.<br/><br/>");
		//print_a($ex);
		echo("UPS says: ".$ex->detail->Errors->ErrorDetail->PrimaryErrorCode->Description."<br/><br/>");
		
		echo("If you continue to have problems please contact HYLETE customer support.");
  }
}

function convertState($name, $to='abbrev') {
	$states = array(
	array('name'=>'Alabama', 'abbrev'=>'AL'),
	array('name'=>'Alaska', 'abbrev'=>'AK'),
	array('name'=>'Arizona', 'abbrev'=>'AZ'),
	array('name'=>'Arkansas', 'abbrev'=>'AR'),
	array('name'=>'California', 'abbrev'=>'CA'),
	array('name'=>'Colorado', 'abbrev'=>'CO'),
	array('name'=>'Connecticut', 'abbrev'=>'CT'),
	array('name'=>'Delaware', 'abbrev'=>'DE'),
	array('name'=>'Florida', 'abbrev'=>'FL'),
	array('name'=>'Georgia', 'abbrev'=>'GA'),
	array('name'=>'Hawaii', 'abbrev'=>'HI'),
	array('name'=>'Idaho', 'abbrev'=>'ID'),
	array('name'=>'Illinois', 'abbrev'=>'IL'),
	array('name'=>'Indiana', 'abbrev'=>'IN'),
	array('name'=>'Iowa', 'abbrev'=>'IA'),
	array('name'=>'Kansas', 'abbrev'=>'KS'),
	array('name'=>'Kentucky', 'abbrev'=>'KY'),
	array('name'=>'Louisiana', 'abbrev'=>'LA'),
	array('name'=>'Maine', 'abbrev'=>'ME'),
	array('name'=>'Maryland', 'abbrev'=>'MD'),
	array('name'=>'Massachusetts', 'abbrev'=>'MA'),
	array('name'=>'Michigan', 'abbrev'=>'MI'),
	array('name'=>'Minnesota', 'abbrev'=>'MN'),
	array('name'=>'Mississippi', 'abbrev'=>'MS'),
	array('name'=>'Missouri', 'abbrev'=>'MO'),
	array('name'=>'Montana', 'abbrev'=>'MT'),
	array('name'=>'Nebraska', 'abbrev'=>'NE'),
	array('name'=>'Nevada', 'abbrev'=>'NV'),
	array('name'=>'New Hampshire', 'abbrev'=>'NH'),
	array('name'=>'New Jersey', 'abbrev'=>'NJ'),
	array('name'=>'New Mexico', 'abbrev'=>'NM'),
	array('name'=>'New York', 'abbrev'=>'NY'),
	array('name'=>'North Carolina', 'abbrev'=>'NC'),
	array('name'=>'North Dakota', 'abbrev'=>'ND'),
	array('name'=>'Ohio', 'abbrev'=>'OH'),
	array('name'=>'Oklahoma', 'abbrev'=>'OK'),
	array('name'=>'Oregon', 'abbrev'=>'OR'),
	array('name'=>'Pennsylvania', 'abbrev'=>'PA'),
	array('name'=>'Rhode Island', 'abbrev'=>'RI'),
	array('name'=>'South Carolina', 'abbrev'=>'SC'),
	array('name'=>'South Dakota', 'abbrev'=>'SD'),
	array('name'=>'Tennessee', 'abbrev'=>'TN'),
	array('name'=>'Texas', 'abbrev'=>'TX'),
	array('name'=>'Utah', 'abbrev'=>'UT'),
	array('name'=>'Vermont', 'abbrev'=>'VT'),
	array('name'=>'Virginia', 'abbrev'=>'VA'),
	array('name'=>'Washington', 'abbrev'=>'WA'),
	array('name'=>'West Virginia', 'abbrev'=>'WV'),
	array('name'=>'Wisconsin', 'abbrev'=>'WI'),
	array('name'=>'Wyoming', 'abbrev'=>'WY')
	);

	$return = false;
	foreach ($states as $state) {
		if ($to == 'name') {
			if (strtolower($state['abbrev']) == strtolower($name)){
				$return = $state['name'];
				break;
			}
		} else if ($to == 'abbrev') {
			if (strtolower($state['name']) == strtolower($name)){
				$return = strtoupper($state['abbrev']);
				break;
			}
		}
	}
	return $return;
}

function countryCodeLookup($code) {

	$strRet = "US";

	switch ($code) {
		case "Canada":
			$strRet = "CA";
			break;
		case "Mexico":
			$strRet = "MX";
			break;
		default:
			$strRet = "US";
			break;
	}

	return $strRet;
}

//Takes the Wufoo entry and turns it into the UPS SOAP Request
function processShipment($entry) {
	
	//create soap request
	$requestoption['RequestOption'] = 'nonvalidate';
	$request['Request'] = $requestoption;

	$return['Code'] = "9";
	
	$shipper['Name'] = 'HYLETE, LLC';
	$shipper['AttentionName'] = 'HYLETE, LLC';
	$shipper['ShipperNumber'] = UPS_ACCOUNT;
	$shipper['EMailAddress'] = 'returns@hylete.com';
	$address['AddressLine'] = '564 Stevens Ave';
	$address['City'] = 'Solana Beach';
	$address['StateProvinceCode'] = 'CA';
	$address['PostalCode'] = '92075';
	$address['CountryCode'] = 'US';
	$shipper['Address'] = $address;
	$phone['Number'] = '8009916532';
	$shipper['Phone'] = $phone;
	
	$shipfrom['Name'] = $entry->Field1. " " . $entry->Field2;
    $shipfrom['AttentionName'] = $entry->Field1. " " . $entry->Field2;;
    $addressFrom['AddressLine'] = $entry->Field4;
    $addressFrom['City'] = $entry->Field6;
    
	if(strlen($entry->Field7) > 2) {
		$strState = convertState($entry->Field7);
	} else {
		$strState = strtoupper($entry->Field7);
	}
	$addressFrom['StateProvinceCode'] = $strState;
    $addressFrom['PostalCode'] = $entry->Field8;
    $addressFrom['CountryCode'] = countryCodeLookup($entry->Field9);
    
	if($entry->Field20 == "") {
		$phone3['Number'] = "8009916532";
	} else {
		$phone3['Number'] = $entry->Field20;
	}
	
    
	$shipfrom['Address'] = $addressFrom;
    $shipfrom['Phone'] = $phone3;
    $shipment['ShipFrom'] = $shipfrom;
	
	$shipto['Name'] = 'HYLETE RETURNS';
	//$shipto['AttentionName'] = 'HYLETE, LLC';
	$shipto['EMailAddress'] = 'returns@hylete.com';
	$addressTo['AddressLine'] = '5959 Randolph Street';
	$addressTo['City'] = 'Commerce';
	$addressTo['StateProvinceCode'] = 'CA';
	$addressTo['PostalCode'] = '90040';
	$addressTo['CountryCode'] = 'US';
	$phone2['Number'] = '8009916532';
	$shipto['Address'] = $addressTo;
	$shipto['Phone'] = $phone2;

	$strOrder = "";
	if($entry->Field12 != "") {
		$strOrder = " ORDER: ".$entry->Field12;
	}

	$package['Description'] = $strOrder.' RA: ' . $entry->EntryId;
	$packaging['Code'] = '02';
	$package['Packaging'] = $packaging;
	$unit['Code'] = 'IN';
	$unit['Description'] = 'Inches';
	$dimensions['UnitOfMeasurement'] = $unit;
	$dimensions['Length'] = '12';
	$dimensions['Width'] = '9';
	$dimensions['Height'] = '4';
	$package['Dimensions'] = $dimensions;
	$unit2['Code'] = 'LBS';
	$unit2['Description'] = 'Pounds';
	$packageweight['UnitOfMeasurement'] = $unit2;
	
	$intW = .75 * (int)$entry->Field10;
	
	$packageweight['Weight'] = (string)$intW; //NEED TO UPDATE PER ITEM;
	$package['PackageWeight'] = $packageweight;
	
	$labelimageformat['Code'] = 'GIF';
	$labelimageformat['Description'] = 'GIF';
	$labelspecification['LabelImageFormat'] = $labelimageformat;
	$labelspecification['HTTPUserAgent'] = 'Mozilla/4.5';
	
	$service['Code'] = '03';
    $service['Description'] = 'Ground';
    $shipment['Service'] = $service;
	
	$shipmentcharge['Type'] = '01';
	$billshipper['AccountNumber'] = UPS_ACCOUNT;
	$shipmentcharge['BillShipper'] = $billshipper;
    $paymentinformation['ShipmentCharge'] = $shipmentcharge;
    
	$emailNotification['EMailAddress'] = 'returns@hylete.com';
	$emailNotification['SubjectCode'] = '08';
	$emailNotification['Subject'] = 'Return Notice For RA: '.$entry->EntryId." ".$entry->Field1." ".$entry->Field2;
	
	$notifications["EMail"] = $emailNotification;
	$notifications["NotificationCode"] = '2';
	
	$serviceoptions["Notification"] = $notifications;
	
	$shipment['ShipmentServiceOptions'] = $serviceoptions;
	$shipment['PaymentInformation'] = $paymentinformation;
	$shipment['Description'] = 'HYLETE Order: '.$entry->Field12;
	$shipment['ReturnService'] = $return;
	$shipment['Service'] = $service;
	$shipment['Shipper'] = $shipper;
	$shipment['ShipTo'] = $shipto;
	$shipment['Package'] = $package;
	$shipment['LabelSpecification'] = $labelspecification;
	
	//ShipmentRequest
	$request['Shipment'] = $shipment;
	
	//echo "Request.......\n";
	//print_r($request);
	return $request;
}

?>