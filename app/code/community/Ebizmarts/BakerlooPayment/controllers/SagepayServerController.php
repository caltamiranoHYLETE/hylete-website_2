<?php

class Ebizmarts_BakerlooPayment_SagepayServerController extends Mage_Core_Controller_Front_Action {

    public function cancelAction() {

        //@TODO: Validate request somehow.

        $methodCode = $this->getRequest()->getParam('method');
        $model = Mage::helper("payment")->getMethodInstance((string)$methodCode);

        $data = array("Status"=>$this->getRequest()->getParam('status'),
            "StatusDetail"=>$this->getRequest()->getParam('statusdetail')
        );

        $returnData = $model->getReturnData($data);
        $url  = Mage::helper("bakerloo_payment")->returnUrl($methodCode, $returnData);
        $this->getResponse()->setBody(Mage::helper("bakerloo_payment")->jsSetLocation($url));

    }

    public function successAction() {

        $methodCode = $this->getRequest()->getParam('method');
        $model = Mage::helper("payment")->getMethodInstance((string)$methodCode);

        $data = array(
                    "Status"        => $this->getRequest()->getParam('status'),
                    "StatusDetail"  => $this->getRequest()->getParam('statusdetail'),
                    "TransactionId" => $this->getRequest()->getParam('trn_id'),
        );

        $returnData = $model->getReturnData($data);
        $url        = Mage::helper("bakerloo_payment")->returnUrl($methodCode, $returnData);
        $this->getResponse()->setBody(Mage::helper("bakerloo_payment")->jsSetLocation($url));

    }

    public function notifyAction(){

        $request = $this->getRequest();

        Sage_Log::log($request->getPost(), null, 'SagePaySuite_POST_Requests.log');

        $sagePayServerSession = Mage::getSingleton('sagepaysuite/session');

        /**
         * Handle ABORT
         */
        $sageStatus = $request->getParam('Status');
        if ($sageStatus == 'ABORT') {
            $sagePayServerSession->setFailStatus($request->getParam('StatusDetail'));
            $this->_returnOkAbort();
            return;
        }
        /**
         * Handle ABORT
         */

        $strVendorName = strtolower(Mage::getModel('sagepaysuite/sagePayServer')->getConfigData('vendor'));

        $strStatus       = $request->getParam('Status', '');
        $strVendorTxCode = $request->getParam('VendorTxCode', '');
        $strVPSTxId      = $request->getParam('VPSTxId', '');

        $strSecurityKey = '';
        if ($sagePayServerSession->getVendorTxCode() == $strVendorTxCode && $sagePayServerSession->getVpsTxId() == $strVPSTxId) {
            $strSecurityKey = $sagePayServerSession->getSecurityKey();
            $sagePayServerSession->setVpsTxId($strVPSTxId);
        }

        if (strlen($strSecurityKey) == 0) {
            $this->_returnInvalid('Security Key invalid');
        }
        else {

            $strStatusDetail = $strTxAuthNo = $strAVSCV2 = $strAddressResult = $strPostCodeResult = $strCV2Result = $strGiftAid = $str3DSecureStatus = $strCAVV = $strAddressStatus = $strPayerStatus = $strCardType = $strPayerStatus = $strLast4Digits = $strMySignature = '';

            $strVPSSignature = $request->getParam('VPSSignature', '');
            $strStatusDetail = $request->getParam('StatusDetail', '');

            if (strlen($request->getParam('TxAuthNo', '')) > 0) {
                $strTxAuthNo = $request->getParam('TxAuthNo', '');

                $sagePayServerSession->setTxAuthNo($strTxAuthNo);
            }

            $strAVSCV2 = $request->getParam('AVSCV2', '');
            $strAddressResult = $request->getParam('AddressResult', '');
            $strPostCodeResult = $request->getParam('PostCodeResult', '');
            $strCV2Result = $request->getParam('CV2Result', '');
            $strGiftAid = $request->getParam('GiftAid', '');
            $str3DSecureStatus = $request->getParam('3DSecureStatus', '');
            $strCAVV = $request->getParam('CAVV', '');
            $strAddressStatus = $request->getParam('AddressStatus', '');
            $strPayerStatus = $request->getParam('PayerStatus', '');
            $strCardType = $request->getParam('CardType', '');
            $strLast4Digits = $request->getParam('Last4Digits', '');
            $strDeclineCode = $request->getParam('DeclineCode', '');
            $strExpiryDate = $request->getParam('ExpiryDate', '');
            $strFraudResponse = $request->getParam('FraudResponse', '');
            $strBankAuthCode = $request->getParam('BankAuthCode', '');

            $strMessage = $strVPSTxId . $strVendorTxCode . $strStatus . $strTxAuthNo . $strVendorName . $strAVSCV2 . $strSecurityKey
                . $strAddressResult . $strPostCodeResult . $strCV2Result . $strGiftAid . $str3DSecureStatus . $strCAVV
                . $strAddressStatus . $strPayerStatus . $strCardType . $strLast4Digits . $strDeclineCode
                . $strExpiryDate . $strFraudResponse . $strBankAuthCode;

            $strMySignature = strtoupper(md5($strMessage));

            /** We can now compare our MD5 Hash signature with that from Sage Pay Server * */
            $validSignature = ($strMySignature !== $strVPSSignature);

            if ($validSignature) {

                Sage_Log::log("Cannot match the MD5 Hash", null, 'SagePaySuite_POST_Requests.log');
                Sage_Log::log("My Message: $strMessage", null, 'SagePaySuite_POST_Requests.log');
                Sage_Log::log("My Signature: $strMySignature", null, 'SagePaySuite_POST_Requests.log');
                Sage_Log::log("VPS Signature: $strVPSSignature", null, 'SagePaySuite_POST_Requests.log');

                $this->_returnInvalid('Cannot match the MD5 Hash. Order might be tampered with. ' . $strStatusDetail);
                return;
            }
            else {

                $strDBStatus = $this->_getHRStatus($strStatus, $strStatusDetail);
                if ($strStatus == 'OK' || $strStatus == 'AUTHENTICATED' || $strStatus == 'REGISTERED') {

                    try {

                        $sagePayServerSession->setTrnhData($this->_setAdditioanlPaymentInfo($strDBStatus));

                        if ((string) $request->getParam('Status') == 'OK' && (string) $request->getParam('TxType') == 'PAYMENT') {
                            $sagePayServerSession->setInvoicePayment(true);
                        }

                        Mage::register('sageserverpost', new Varien_Object($request->getPost()));

                        $this->_returnOk($strVendorTxCode);

                        //return;
                    } catch (Exception $e) {
                        Mage::logException($e);
                        Mage::log($e->getMessage());
                    }
                }
                else {
                    /** The status indicates a failure of one state or another, so send the customer to orderFailed instead * */
                    $this->_returnInvalid();
                    return;
                }
            }
        }
    }

    protected function _getHRStatus($strStatus, $strStatusDetail) {
        if ($strStatus == 'OK')
            $strDBStatus = 'AUTHORISED - The transaction was successfully authorised with the bank.';
        elseif ($strStatus == 'NOTAUTHED')
            $strDBStatus = 'DECLINED - The transaction was not authorised by the bank.';
        elseif ($strStatus == 'ABORT')
            $strDBStatus = 'ABORTED - The customer clicked Cancel on the payment pages, or the transaction was timed out due to customer inactivity.';
        elseif ($strStatus == 'REJECTED')
            $strDBStatus = 'REJECTED - The transaction was failed by your 3D-Secure or AVS/CV2 rule-bases.';
        elseif ($strStatus == 'AUTHENTICATED')
            $strDBStatus = 'AUTHENTICATED - The transaction was successfully 3D-Secure Authenticated and can now be Authorised.';
        elseif ($strStatus == 'REGISTERED')
            $strDBStatus = 'REGISTERED - The transaction was could not be 3D-Secure Authenticated, but has been registered to be Authorised.';
        elseif ($strStatus == 'ERROR')
            $strDBStatus = 'ERROR - There was an error during the payment process.  The error details are: ' . $strStatusDetail;
        else
            $strDBStatus = 'UNKNOWN - An unknown status was returned from Sage Pay.  The Status was: ' . $strStatus . ', with StatusDetail:' . $strStatusDetail;

        return $strDBStatus;
    }

    protected function _setAdditioanlPaymentInfo($status) {
        $requestParams = $this->getRequest()->getParams();
        $sagePayServerSession = Mage::getSingleton('sagepaysuite/session');

        unset($requestParams['SID']);
        unset($requestParams['VPSProtocol']);
        unset($requestParams['TxType']);
        unset($requestParams['VPSSignature']);

        $requestParams['CustomStatusCode'] = $sagePayServerSession->getTrnDoneStatus();
        $info = serialize($requestParams);

        $sagePayServerSession->setTrnDoneStatus(null);

        return $info;
    }

    private function _returnOkAbort() {
        $strResponse = 'Status=OK' . "\r\n";
        $strResponse .= 'StatusDetail=Transaction ABORTED successfully' . "\r\n";

        $url = Mage :: getUrl('pos_payment/sagepayServer/cancel', array(
            '_secure'  => true,
            'method' => $this->getRequest()->getParam('method'),
            'status'  => 'ABORTED',
            'statusdetail'  => 'Transaction ABORTED successfully'
        ));

        $strResponse .= 'RedirectURL=' . $url . "\r\n";

        $this->getResponse()->setHeader('Content-type', 'text/plain');
        $this->getResponse()->setBody($strResponse);
        return;
    }

    private function _returnOk($transactionId) {

        $strResponse = 'Status=OK' . "\r\n";
        $strResponse .= 'StatusDetail=Transaction completed successfully' . "\r\n";

        $url = Mage :: getUrl('pos_payment/sagepayServer/success', array(
            '_secure'  => true,
            'method' => $this->getRequest()->getParam('method'),
            'status'  => 'OK',
            'statusdetail'  => 'Transaction completed successfully',
            'trn_id' => $transactionId
        ));

        $strResponse .= 'RedirectURL=' . $url . "\r\n";

        $this->getResponse()->setHeader('Content-type', 'text/plain');
        $this->getResponse()->setBody($strResponse);
    }

    private function _returnInvalid($message = 'Invalid transaction') {

        $response = 'Status=INVALID' . "\r\n";

        $url = Mage :: getUrl('pos_payment/sagepayServer/cancel', array(
            '_secure'  => true,
            'method' => $this->getRequest()->getParam('method'),
            'status'  => 'INVALID',
            'statusdetail'  => $message
        ));

        $response .= 'RedirectURL=' . $url . "\r\n";
        $response .= 'StatusDetail=' . $message . "\r\n";

        $this->getResponse()->setHeader('Content-type', 'text/plain');
        $this->getResponse()->setBody($response);
    }

}