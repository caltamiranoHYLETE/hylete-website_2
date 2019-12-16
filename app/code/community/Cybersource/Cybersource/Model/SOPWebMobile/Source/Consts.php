<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

abstract class Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts extends Varien_Object
{
	const LOCALE = 'en-us';

	const PAY_METHOD_CARD = 'card';
	const PAY_METHOD_ECHECK = 'echeck';

	const TESTURL = 'https://testsecureacceptance.cybersource.com/silent/pay';
	const LIVEURL = 'https://secureacceptance.cybersource.com/silent/pay';
    const MOBILE_TESTURL = 'https://testsecureacceptance.cybersource.com/pay';
    const MOBILE_LIVEURL = 'https://secureacceptance.cybersource.com/pay';

    const CREATE_TOKEN_TESTURL = 'https://testsecureacceptance.cybersource.com/silent/token/create';
    const CREATE_TOKEN_LIVEURL = 'https://secureacceptance.cybersource.com/silent/token/create';
    const CREATE_TOKEN_MOBILE_TESTURL = 'https://testsecureacceptance.cybersource.com/token/create';
    const CREATE_TOKEN_MOBILE_LIVEURL = 'https://secureacceptance.cybersource.com/token/create';

    const CS_ACTION_CAPTURE = 'sale';
    const CS_ACTION_AUTHORIZE = 'authorization';
    const CS_ACTION_CREATE_TOKEN = 'create_payment_token';

	const STATUS_ACCEPT = 100;
	const STATUS_SMART_REVIEW = 520;
	const STATUS_DM_REVIEW = 480;
	const STATUS_PA_ENROLLED = 475;

	const CONFIG_CARDCHECK_ACCEPT = 1;
	const CONFIG_CARDCHECK_ACCEPT_HOLD = 2;
	const CONFIG_CARDCHECK_DECLINE = 3;

    const IGNORE_RULE_TRUE = 1;
    const IGNORE_RULE_FALSE = 0;

    /**
     * @param mixed $valin System config path of the field to be pulled
     * @param string $store storeId
     * @return mixed
     */
    static function getSystemConfig($valin = false, $store = null)
	{
        if (! $store) {
            $store = Mage::app()->getStore()->getId();
        }

		if ($valin) {
            return Mage::getStoreConfig('payment/cybersourcesop/' . $valin, $store);
		}

        return Mage::getStoreConfig('payment/cybersourcesop', $store);
	}

    /**
     * Returns success reasoning codes array
     * @return array
     */

    static function getSuccessCodes()
	{
		return array(self::STATUS_ACCEPT, self::STATUS_SMART_REVIEW, self::STATUS_DM_REVIEW);
	}

    /**
     * Returns array containing AVS successful codes
     * @return array
     */
    static function getAvsSuccessVals()
	{
		return array('B','D','M','P','U','A','F','H','J','K','L','O','Q','T','V','W','X','Y','Z','3','1');
	}


    /**
     * Returns array containing CVN successful codes
     * @return array
     */
	static function getCvnSuccessVals()
	{
		return array('M','U','X','1','2','N');
	}

    /**
     * Returns array containing successful codes for 3D secure
     * @return array
     */
	static function get3dSecureSuccessVals()
	{
		return array('05','02');
	}

    /**
     * @param string $storeId
     * @return mixed
     */
    static function useCvn($storeId = null)
	{
		return Mage::getStoreConfig('payment/cybersourcesop/useccv', $storeId);
	}

    /**
     * @return string
     */

    static function getCyberTransactionType($tokenize = false)
	{
	    if (Mage::helper('cybersourcesop')->useSoapForTransactions()) {
	        return self::CS_ACTION_CREATE_TOKEN;
        }

        $paymentAction = Mage::helper('cybersourcesop')->getPaymentAction();

        $map = array(
            Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE => self::CS_ACTION_AUTHORIZE,
            Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE => self::CS_ACTION_CAPTURE
        );

        // legacy WM configuration could be 'authorization' or 'sale' which is fine for CyberSource as is
        $csPaymentAction = isset($map[$paymentAction]) ? $map[$paymentAction] : $paymentAction;

        if ($tokenize) {
            $csPaymentAction .= ',' . self::CS_ACTION_CREATE_TOKEN;
        }

        return $csPaymentAction;
	}

    /**
     * Given the response code , it returns the error message.
     * @param string $codein cybersource response code.
     * @return string error message.
     */
    static function getErrorCode($codein)
	{
		//list of error responses here
        $errorArray = array(
            '150' => Mage::helper('cybersourcesop')->__('A general error has occurred.'),
            '151' => Mage::helper('cybersourcesop')->__('The communication to your bank has failed, please try again later.'),
            '152' => Mage::helper('cybersourcesop')->__('The communication to your bank has failed, please try again later.'),
            '250' => Mage::helper('cybersourcesop')->__('The communication to your bank has failed, please try again later.'),
            '203' => Mage::helper('cybersourcesop')->__('Sorry your card has been declined by your bank, please try a different card or check with your bank.'),
            '201' => Mage::helper('cybersourcesop')->__('Your issuing bank has requested more information about the transaction, please contact them and try again.'),
            '202' => Mage::helper('cybersourcesop')->__('Your credit card has expired, please enter a valid card.'),
            '204' => Mage::helper('cybersourcesop')->__('You have insufficient funds on the account for this transaction.'),
            '205' => Mage::helper('cybersourcesop')->__('Your card has been reported stolen or lost. Please contact your issuing bank or try a new card.'),
            '207' => Mage::helper('cybersourcesop')->__('Sorry we are unable to reach your bank to verify this transaction, please try again.'),
            '208' => Mage::helper('cybersourcesop')->__('Your Card is inactive or not authorized. Please contact your issuing bank or try a new card.'),
            '210' => Mage::helper('cybersourcesop')->__('Your card has reached its credit limit and this transaction cannot be processed.'),
            '211' => Mage::helper('cybersourcesop')->__('Your CVN (3 digit code) is invalid, please amend and try again.'),
            '230' => Mage::helper('cybersourcesop')->__('Your CVN (3 digit code) is invalid, please amend and try again.'),
            '200' => Mage::helper('cybersourcesop')->__('Your Billing address does not match the one registered to that card, please amend your address and try again.'),
            '476' => Mage::helper('cybersourcesop')->__('Your card failed the authentication process, please try again.'),
            '481' => Mage::helper('cybersourcesop')->__('Your card failed the fraud screening process, please check the details or try a new card.'),
            '102' => Mage::helper('cybersourcesop')->__('Your billing and/or shipping address details are invalid or missing.')
        );

		if (isset($errorArray[$codein])) {
			return $errorArray[$codein];
		}

		return 'Unknown error occured';
	}

    /**
     * Given avs code , it returns the error message.
     * @param string $codein avs  code.
     * @return string error message.
     */
    static function getAVSErrorCode($codein)
    {
        //list of error responses here
        $errorArray = array('1' => Mage::helper('cybersourcesop')->__('AVS is not supported for this processor or card type.'),
            '2' => Mage::helper('cybersourcesop')->__('The processor returned an unrecognized value for the AVS response.'),
            'A' => Mage::helper('cybersourcesop')->__('The postal code do not match.'),
            'B' => Mage::helper('cybersourcesop')->__('The postal code not verified. Returned only for non U.S.-issued Visa cards.'),
            'C' => Mage::helper('cybersourcesop')->__('Street address and postal code do not match.'),
            'E' => Mage::helper('cybersourcesop')->__('AVS data is invalid or AVS is not allowed for this card type.'),
            'F' => Mage::helper('cybersourcesop')->__('Card member\'s name does not match, but billing postal code matches.'),
            'G' => Mage::helper('cybersourcesop')->__('The bank does not support AVS.'),
            'H' => Mage::helper('cybersourcesop')->__('Card member\'s name does not match.'),
            'I' => Mage::helper('cybersourcesop')->__('Address not verified.'),
            'K' => Mage::helper('cybersourcesop')->__('The billing address and billing postal code do not match.'),
            'L' => Mage::helper('cybersourcesop')->__('Card member\'s name and billing postal code match, but billing address does not match.'),
            'N' => Mage::helper('cybersourcesop')->__('Either Street address and postal code do not match or Card member\'s name, street address and postal code do not match.'),
            'O' => Mage::helper('cybersourcesop')->__('Card member\'s name and billing address match, but billing postal code does not match.'),
            'P' => Mage::helper('cybersourcesop')->__('The street address not verified.'),
            'R' => Mage::helper('cybersourcesop')->__('System unavailable.'),
            'S' => Mage::helper('cybersourcesop')->__('The bank does not support AVS.'),
            'T' => Mage::helper('cybersourcesop')->__('Card member\'s name does not match, but street address matches.'),
            'U' => Mage::helper('cybersourcesop')->__('Address information unavailable for this card.'),
            'W' => Mage::helper('cybersourcesop')->__('Street address does not match, but 9-digit postal code matches.'),
            'X' => Mage::helper('cybersourcesop')->__('Street address and 9-digit postal code match.'),
            'Y' => Mage::helper('cybersourcesop')->__('Street address and 5-digit postal code match.'),
            'Z' => Mage::helper('cybersourcesop')->__('Street address does not match, but 5-digit postal code matches.')
        );

        if (isset($errorArray[$codein])) {
            return $errorArray[$codein];
        }

        return 'Unknown AVS error occured.';
    }
    /**
     * Given cvn code , it returns the error message.
     * @param string $codein cvn response code.
     * @return string error message.
     */
    static function getCVNErrorCode($codein)
    {
        //list of error responses here
        $errorArray = array('D' => Mage::helper('cybersourcesop')->__('Transaction determined suspicious by issuing bank.'),
            'I' => Mage::helper('cybersourcesop')->__('Card verification number failed processor\'s data validation check.'),
            'N' => Mage::helper('cybersourcesop')->__('Card verification number not matched.'),
            'P' => Mage::helper('cybersourcesop')->__('Card verification number not processed by processor for unspecified reason.'),
            'S' => Mage::helper('cybersourcesop')->__('Card verification number is on the card but was not included in the request.'),
            'U' => Mage::helper('cybersourcesop')->__('Card verification is not supported by the issuing bank.'),
            'X' => Mage::helper('cybersourcesop')->__('Card verification is not supported by the card association.'),
            '1' => Mage::helper('cybersourcesop')->__('Card verification is not supported for this processor or card type.'),
            '2' => Mage::helper('cybersourcesop')->__('Unrecognized result code returned by processor for card verification response.'),
            '3' => Mage::helper('cybersourcesop')->__('No result code returned by processor.')
        );

        if (isset($errorArray[$codein])) {
            return $errorArray[$codein];
        }

        return 'Unknown CVN error occured.';
    }

    /**
     * lookup for card name
     * @param string $ccType
     * @param string $typein
     * @return mixed
     */
    static function getCCname($ccType)
	{
		$ccMap = self::getCCMap();

		if (isset($ccMap[$ccType])) {
			//get the object from the array
			$cardinfo = $ccMap[$ccType];
			return $cardinfo->name;
		}

        return 'N/A';
	}

    /**
     *  Return the code based on the Magento code
     * @param $ccniumin
     * @return mixed
     */

    static function getCyberCCs($ccniumin)
	{
		$cardsmap = self::getCCMap();

		if (isset($cardsmap[$ccniumin])) {
			//get the object from the array
			$cardinfo = $cardsmap[$ccniumin];
			return $cardinfo->cybercode;
		}

		return $ccniumin;
	}

    /**
     * Retrieves card number
     * @param $cyberresponsecc
     * @return mixed|string
     */
    static function retrieveCardNum($cyberresponsecc)
	{
		if ($cyberresponsecc) {
			return substr($cyberresponsecc,-4);
		}

		return '0000';
	}

	/**
	 * Retrieve the error message for the back office
	 * @return string
	 */
	static function getFraudWarningMsg()
	{
		return 'PLEASE NOTE: This order has been successfully placed but failed the AVS / CVN check, please review this order.';
	}

    /**
     * Retrieve the error message for the back office
     * @return string
     */
    static function getDmWarningMsg()
    {
        return 'PLEASE NOTE: This order has been successfully placed but is under Decision Manager Review, please review this order.';
    }

	/**
	 * Define all the credit cards for this extension, allows us to be as close to magento config as possible
	 * About as clean as i can make this, could make it a table but dont want people messing with these settings.
	 *
	 * @return array
	 */
	static function getCCMap()
	{
		$cyberhelper = Mage::helper('cybersourcesop');

		$visa = new stdClass();
		$visa->cybercode='001';
		$visa->magecode='VI';
		$visa->regex="[new RegExp('^4[0-9]{12}([0-9]{3})?$'), new RegExp('^[0-9]{3}$'), true]";
		$visa->name=$cyberhelper->__('Visa');

		$mastercard = new stdClass();
		$mastercard->cybercode='002';
		$mastercard->magecode='MC';
		$mastercard->regex="[new RegExp('^5[1-5][0-9]{14}$'), new RegExp('^[0-9]{3}$'), true]";
		$mastercard->name=$cyberhelper->__('Mastercard');

		$amex = new stdClass();
		$amex->cybercode='003';
		$amex->magecode='AE';
		$amex->regex="[new RegExp('^3[47][0-9]{13}$'), new RegExp('^[0-9]{4}$'), true]";
		$amex->name=$cyberhelper->__('American Express');

		$diners = new stdClass();
		$diners->cybercode='004';
		$diners->magecode='DI';
		$diners->regex="[new RegExp('^6011[0-9]{12}$'), new RegExp('^[0-9]{3}$'), true]";
		$diners->name=$cyberhelper->__('Discover Card');

		$jcb = new stdClass();
		$jcb->cybercode='007';
		$jcb->magecode='JCB';
		$jcb->regex="[new RegExp('^(3[0-9]{15}|(2131|1800)[0-9]{11})$'), new RegExp('^[0-9]{3,4}$'), true]";
		$jcb->name=$cyberhelper->__('JCB');

		$vid = new stdClass();
		$vid->cybercode='033';
		$vid->magecode='VID';
		$vid->regex="[new RegExp('^4[0-9]{12}([0-9]{3})?$'), new RegExp('^[0-9]{3}$'), true]";
		$vid->name=$cyberhelper->__('Visa Debit / Electron');

		$maeuk = new stdClass();
		$maeuk->cybercode='024';
		$maeuk->magecode='MAEUK';
		$maeuk->regex="[new RegExp('^[0-9]{14,18}$'), new RegExp('^[0-9]{3}$'), false]";
		$maeuk->name=$cyberhelper->__('Maestro (UK) / Switch');

        $mae = new stdClass();
        $mae->cybercode='042';
        $mae->magecode='MAE';
        $mae->regex="[new RegExp('^[0-9]{14,18}$'), new RegExp('^[0-9]{3}$'), false]";
        $mae->name=$cyberhelper->__('Maestro (International)');

        $dinersClub = new stdClass();
        $dinersClub->cybercode='005';
        $dinersClub->magecode='DC';
        $dinersClub->regex="[new RegExp('^3[0|6|8|9][0-9]{12}$|^5[0-9]{15}$'), new RegExp('^[0-9]{3}$'), true]";
        $dinersClub->name=$cyberhelper->__('Diners Club');

        return array(
            'VI' => $visa,
            'MC' => $mastercard,
            'AE' => $amex,
            'DI' => $diners,
            'JCB' => $jcb,
            'VID' => $vid,
            'MAEUK' => $maeuk,
            'MAE' => $mae,
            'DC' => $dinersClub,
            '001' => $visa,
            '002' => $mastercard,
            '003' => $amex,
            '004' => $diners,
            '007' => $jcb,
            '033' => $vid,
            '024' => $maeuk,
            '042' => $mae,
            '005' => $dinersClub
        );
	}

    /**
     * Returns array containing countries codes
     * @return array
     */
    static function getPostCodeRequiredCountries()
    {
        return array('US','CA');
    }
}
