<?php
/**
 * DIBS Payment Block
 *
 */
class Icommerce_Dibs_Model_Dibs extends Mage_Payment_Model_Method_Abstract
{
    const CGI_URL = 'https://payment.architrade.com/payment/start.pml';
    const CGI_URL_FLEX = 'https://payment.architrade.com/paymentweb/start.action';
    const CGI_URL_PAYMENT_WINDOW = 'https://sat1.dibspayment.com/dibspaymentwindow/entrypoint';
    const CGI_URL_CAPTURE = 'https://payment.architrade.com/cgi-bin/capture.cgi';
    const CGI_URL_CANCEL = 'https://#CRED#payment.architrade.com/cgi-adm/cancel.cgi';
    const CGI_URL_REFUND = 'https://#CRED#payment.architrade.com/cgi-adm/refund.cgi';
    //changing the payment to different from cc payment type and dibs payment type
    const PAYMENT_TYPE_AUTH = 'AUTHORIZATION';
    const PAYMENT_TYPE_SALE = 'SALE';

    protected $_code = 'dibs';
    protected $_formBlockType = 'dibs/form';
    protected $_infoBlockType = 'dibs/info';
    protected $_isInitializeNeeded      = true;
    protected $_canRefund               = true;
    protected $_canVoid                 = true;
    protected $_store_id                = NULL;

    //
    // Allowed currency types
    //
    protected $_allowCurrencyCode = array(
        'ADP', 'AED', 'AFA', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZM', 'BAM', 'BBD', 'BDT', 'BGL', 'BGN',
        'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BOV', 'BRL', 'BSD', 'BTN', 'BWP', 'BYR', 'BZD', 'CAD', 'CDF', 'CHF', 'CLF',
        'CLP', 'CNY', 'COP', 'CRC', 'CUP', 'CVE', 'CYP', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'ECS', 'ECV', 'EEK', 'EGP',
        'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GHC', 'GIP', 'GMD', 'GNF', 'GTQ', 'GWP', 'GYD', 'HKD', 'HNL',
        'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'IQD', 'IRR', 'ISK', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF',
        'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LTL', 'LVL', 'LYD', 'MAD', 'MDL', 'MGF',
        'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MTL', 'MUR', 'MVR', 'MWK', 'MXN', 'MXV', 'MYR', 'MZM', 'NAD', 'NGN', 'NIO',
        'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'ROL', 'RUB', 'RUR', 'RWF',
        'SAR', 'SBD', 'SCR', 'SDD', 'SEK', 'SGD', 'SHP', 'SIT', 'SKK', 'SLL', 'SOS', 'SRG', 'STD', 'SVC', 'SYP', 'SZL',
        'THB', 'TJS', 'TMM', 'TND', 'TOP', 'TPE', 'TRL', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS',
        'VEB', 'VND', 'VUV', 'XAF', 'XCD', 'XOF', 'XPF', 'YER', 'YUM', 'ZAR', 'ZMK', 'ZWD'
    );

    public function setStoreId($id)
    {
        $this->_store_id = $id;
    }

    public function getStoreId()
    {
        return ($this->_store_id);
    }

    /**
     * Get Dibs session namespace
     *
     * @return Mage_Dibs_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('dibs/dibs_session');
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Icommerce_Default::getCheckoutQuote();
        // Buggy on M1.4.1
        // return $this->getCheckout()->getQuote();
    }

    /**
     * Using internal pages for input payment data
     *
     * @return bool
     */
    public function canUseInternal()
    {
        return false;
    }

    /**
     * Using for multiple shipping address
     *
     * @return bool
     */
    public function canUseForMultishipping()
    {
        return false;
    }

    //
    // Perhaps this can be used to make payments before the order is created
    //
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('dibs/dibs_form', $name)
                ->setMethod('dibs')
                ->setPayment($this->getPayment())
                ->setTemplate('icommerce/dibs/form.phtml');

        return $block;
    }

    private function getQuoteBaseCurrencyCode()
    {
        $quote = null;
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo) {
            if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
                $quoteId = $paymentInfo->getOrder()->getQuoteId();
                $quote = Mage::getModel('sales/quote')->load($quoteId);
            } else {
                $quote = $paymentInfo->getQuote();
            }
        }
        if (!$quote) {
            $quote = $this->getQuote();
        }
        return $quote->getBaseCurrencyCode();
    }

    private function setCurrentOrderStoreId()
    {
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo) {
            if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
                $order = $paymentInfo->getOrder();
                if ($order) {
                    $this->setStoreId($order->getStoreId());
                }
            }
        }
    }

    /*validate the currency code is avaialable to use for dibs or not*/
    public function validate()
    {
        parent::validate();
        $currency_code = $this->getQuoteBaseCurrencyCode();
        if (!in_array($currency_code, $this->_allowCurrencyCode)) {
            Mage::throwException(Mage::helper('dibs')->__('Selected currency code is not compatabile with DIBS') . '(' . $currency_code . ')');
        }
        return $this;
    }

    public function onOrderValidate(Mage_Sales_Model_Order_Payment $payment)
    {
        return $this;
    }

    public function onInvoiceCreate(Mage_Sales_Model_Invoice_Payment $payment)
    {
        ///       return $this;
    }

    public function canCapture()
    {
        $this->setCurrentOrderStoreId();
        return $this->getConfigData('direct_capture',$this->getStoreId()) == 2 ? true : false;
    }

    public function canCapturePartial()
    {
        $this->setCurrentOrderStoreId();
        return $this->getConfigData('part_capture',$this->getStoreId()) != 0 ? true : false;
    }

    public function canRefundInvoicePartial()
    {
        $this->setCurrentOrderStoreId();
        return $this->getConfigData('part_capture',$this->getStoreId()) != 0 ? true : false;
    }

    public function canRefundPartialPerInvoice()
    {
        $this->setCurrentOrderStoreId();
        return $this->getConfigData('part_capture',$this->getStoreId()) != 0 ? true : false;
    }

    public function capture(Varien_Object $payment, $inamount)
    {
        $order = $payment->getOrder();
        $this->setStoreId($order->getStoreId());
        if ($this->getConfigData('direct_capture',$this->getStoreId()) == 0) {
            return $this;
        }
        $status = $order->getStatus();
        $status_reserved = $this->getConfigData('order_status_reserved',$this->getStoreId());

        $merchant = $this->getConfigData('account_number',$this->getStoreId());
        $increment_id = $order->getData("entity_id");
        $order_id = $order->getData("increment_id");
//        $fake_order_id = '9'.$order_id;
        $fake_order_id = $order_id;
        $amount = round($inamount * 100,0);

        $key1 = $this->getConfigData('md5_k1',$this->getStoreId());
        $key2 = $this->getConfigData('md5_k2',$this->getStoreId());

        $additionaldata = unserialize($order->getPayment()->getAdditionalData());

        /* Activate this code when you want to Fake Capture an order in limboland (not possible to Finish)
           I will make a function for this, sooooooon.
                  $transact = 0;
                  $additionaldata['transactionCaptured'] = 'yes';
                  $order->getPayment()->setAdditionalData(serialize(($additionaldata)));
                  $status_captured = $this->getConfigData('order_status_captured',$this->getStoreId());
                  $msg = Mage::helper('dibs')->__("Capture successful") . "<br/>". Mage::helper('dibs')->__("DIBS Order ID") . ": <b>" . $transact . "</b>";
                  $order->addStatusToHistory($status_captured,$msg);
                  $order->save();
                  return $this;
        */

        $transact = $additionaldata['transactionNumber'];
        if ($transact <= 0) {
            Mage::throwException(Mage::helper('dibs')->__("This order doesn't contain necessary Dibs information so it can not capture online, please change option"));
            return $this;
        }

        if ($additionaldata['transactionCaptured'] == 'yes') {
            if ($this->getConfigData('part_capture',$this->getStoreId()) == 0) {
                return $this;
            }
        }

        $md5 = "merchant=$merchant&orderid=$fake_order_id&transact=$transact&amount=$amount";
        $md5 = md5($key2 . md5($key1 . $md5));

        $fields = array(
            'amount' => $amount,
            'merchant' => $merchant,
            'orderid' => $fake_order_id,
            'md5key' => $md5,
            'force' => 'true',
            'textreply' => 'true',
            'transact' => $transact,
        );

        /*
         * Split pay only works in Denmark it seems. It also doesn't work if force is set to true (see a few lines above).
         * So if we need this function, we need to set force to false and we need to have Another setting for this particular thing
         *
        if ($this->getConfigData('part_capture',$this->getStoreId()) == 1) {
            if ($inamount < $order->getGrandTotal()) {
                $fields['splitpay'] = true;
                if ($inamount + $order->getTotalDue() == $order->getGrandTotal()) { // Base, if this code goes back in!!
                    $fields['close'] = true;
                }
            }
        }
        */

        $url = $this->getCaptureUrl();
        $ch = curl_init($url);
        $r = curl_setopt($ch, CURLOPT_POST, true);
        $r = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $r = curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $out = curl_exec($ch);
        curl_close($ch);

        Icommerce_Log::writeSeqFile(Mage::getBaseDir("var") . "/dibs", "capture", $out);

        if (!$out) {
            Mage::throwException(Mage::helper('dibs')->__(Mage::helper('dibs')->__("Unknown error in communication with Dibs")));
        }

        $this->scanStatusAndResult($out,$status,$result);

        switch ($status) {
            case 'DECLINED':
                $msg = Mage::helper('dibs')->__("Capture failed") . "<br/>" . Mage::helper('dibs')->__("DIBS Order ID") . ": <b>" . $transact . "</b><br/>" . $result;
                $order->addStatusToHistory($order->getStatus(), $msg);
                $order->save();
                Mage::throwException(Mage::helper('dibs')->__($result));
                break;
            case 'ACCEPTED':
                $additionaldata['transactionCaptured'] = 'yes';
                $order->getPayment()->setAdditionalData(serialize(($additionaldata)));
                $order->getPayment()->setLastTransId($transact);
                $status_captured = Icommerce_OrderStatus_Helper_Data::getStatus($this->getConfigData('order_status_captured',$this->getStoreId()), $order->getState());
                $msg = Mage::helper('dibs')->__("Capture successful") . "<br/>" . Mage::helper('dibs')->__("DIBS Order ID") . ": <b>" . $transact . "</b>";
                $order->addStatusToHistory($status_captured, $msg);
                $order->save();
                Mage::dispatchEvent( 'vaimo_paymentmethod_order_captured', array(
                    'store_id' => $order->getStoreId(),
                    'order_id' => $order->getIncrementId(),
                    'method' => 'dibs',
                    'amount' => $inamount
                    ));
                break;
            default:
                Mage::throwException(Mage::helper('dibs')->__("Unknown error in communication with Dibs"));
                break;
        }
        return $this;
    }

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('dibs/dibs/redirect', array('_secure' => $this->isFrontendSecure()));
    }

    //
    // Convert from Magento currency to DIBS currency
    //
    public function convertToDibsCurrency($cur)
    {
        switch ($cur->getCode())
        {
            case "ADP":
                return "020";
            case "AED":
                return "784";
            case "AFA":
                return "004";
            case "ALL":
                return "008";
            case "AMD":
                return "051";
            case "ANG":
                return "532";
            case "AOA":
                return "973";
            case "ARS":
                return "032";
            case "AUD":
                return "036";
            case "AWG":
                return "533";
            case "AZM":
                return "031";
            case "BAM":
                return "977";
            case "BBD":
                return "052";
            case "BDT":
                return "050";
            case "BGL":
                return "100";
            case "BGN":
                return "975";
            case "BHD":
                return "048";
            case "BIF":
                return "108";
            case "BMD":
                return "060";
            case "BND":
                return "096";
            case "BOB":
                return "068";
            case "BOV":
                return "984";
            case "BRL":
                return "986";
            case "BSD":
                return "044";
            case "BTN":
                return "064";
            case "BWP":
                return "072";
            case "BYR":
                return "974";
            case "BZD":
                return "084";
            case "CAD":
                return "124";
            case "CDF":
                return "976";
            case "CHF":
                return "756";
            case "CLF":
                return "990";
            case "CLP":
                return "152";
            case "CNY":
                return "156";
            case "COP":
                return "170";
            case "CRC":
                return "188";
            case "CUP":
                return "192";
            case "CVE":
                return "132";
            case "CYP":
                return "196";
            case "CZK":
                return "203";
            case "DJF":
                return "262";
            case "DKK":
                return "208";
            case "DOP":
                return "214";
            case "DZD":
                return "012";
            case "ECS":
                return "218";
            case "ECV":
                return "983";
            case "EEK":
                return "233";
            case "EGP":
                return "818";
            case "ERN":
                return "232";
            case "ETB":
                return "230";
            case "EUR":
                return "978";
            case "FJD":
                return "242";
            case "FKP":
                return "238";
            case "GBP":
                return "826";
            case "GEL":
                return "981";
            case "GHC":
                return "288";
            case "GIP":
                return "292";
            case "GMD":
                return "270";
            case "GNF":
                return "324";
            case "GTQ":
                return "320";
            case "GWP":
                return "624";
            case "GYD":
                return "328";
            case "HKD":
                return "344";
            case "HNL":
                return "340";
            case "HRK":
                return "191";
            case "HTG":
                return "332";
            case "HUF":
                return "348";
            case "IDR":
                return "360";
            case "ILS":
                return "376";
            case "INR":
                return "356";
            case "IQD":
                return "368";
            case "IRR":
                return "364";
            case "ISK":
                return "352";
            case "JMD":
                return "388";
            case "JOD":
                return "400";
            case "JPY":
                return "392";
            case "KES":
                return "404";
            case "KGS":
                return "417";
            case "KHR":
                return "116";
            case "KMF":
                return "174";
            case "KPW":
                return "408";
            case "KRW":
                return "410";
            case "KWD":
                return "414";
            case "KYD":
                return "136";
            case "KZT":
                return "398";
            case "LAK":
                return "418";
            case "LBP":
                return "422";
            case "LKR":
                return "144";
            case "LRD":
                return "430";
            case "LSL":
                return "426";
            case "LTL":
                return "440";
            case "LVL":
                return "428";
            case "LYD":
                return "434";
            case "MAD":
                return "504";
            case "MDL":
                return "498";
            case "MGF":
                return "450";
            case "MKD":
                return "807";
            case "MMK":
                return "104";
            case "MNT":
                return "496";
            case "MOP":
                return "446";
            case "MRO":
                return "478";
            case "MTL":
                return "470";
            case "MUR":
                return "480";
            case "MVR":
                return "462";
            case "MWK":
                return "454";
            case "MXN":
                return "484";
            case "MXV":
                return "979";
            case "MYR":
                return "458";
            case "MZM":
                return "508";
            case "NAD":
                return "516";
            case "NGN":
                return "566";
            case "NIO":
                return "558";
            case "NOK":
                return "578";
            case "NPR":
                return "524";
            case "NZD":
                return "554";
            case "OMR":
                return "512";
            case "PAB":
                return "590";
            case "PEN":
                return "604";
            case "PGK":
                return "598";
            case "PHP":
                return "608";
            case "PKR":
                return "586";
            case "PLN":
                return "985";
            case "PYG":
                return "600";
            case "QAR":
                return "634";
            case "ROL":
                return "642";
            case "RUB":
                return "643";
            case "RUR":
                return "810";
            case "RWF":
                return "646";
            case "SAR":
                return "682";
            case "SBD":
                return "090";
            case "SCR":
                return "690";
            case "SDD":
                return "736";
            case "SEK":
                return "752";
            case "SGD":
                return "702";
            case "SHP":
                return "654";
            case "SIT":
                return "705";
            case "SKK":
                return "703";
            case "SLL":
                return "694";
            case "SOS":
                return "706";
            case "SRG":
                return "740";
            case "STD":
                return "678";
            case "SVC":
                return "222";
            case "SYP":
                return "760";
            case "SZL":
                return "748";
            case "THB":
                return "764";
            case "TJS":
                return "972";
            case "TMM":
                return "795";
            case "TND":
                return "788";
            case "TOP":
                return "776";
            case "TPE":
                return "626";
            case "TRL":
                return "792";
            case "TRY":
                return "949";
            case "TTD":
                return "780";
            case "TWD":
                return "901";
            case "TZS":
                return "834";
            case "UAH":
                return "980";
            case "UGX":
                return "800";
            case "USD":
                return "840";
            case "UYU":
                return "858";
            case "UZS":
                return "860";
            case "VEB":
                return "862";
            case "VND":
                return "704";
            case "VUV":
                return "548";
            case "XAF":
                return "950";
            case "XCD":
                return "951";
            case "XOF":
                return "952";
            case "XPF":
                return "953";
            case "YER":
                return "886";
            case "YUM":
                return "891";
            case "ZAR":
                return "710";
            case "ZMK":
                return "894";
            case "ZWD":
                return "716";
        }

        return "";
    }

    //
    // Calculates if any of the trusted logos are to be shown - in that case return true
    //
    public function showTrustedList()
    {
        $logoArray = explode(',', $this->getConfigData('showlogos'));
        foreach ($logoArray as $item) {
            if ($item == 'DIBS' ||
                $item == 'VISA_SECURE' ||
                $item == 'MC_SECURE' ||
                $item == 'JCB_SECURE' ||
                $item == 'PCI') {
                return true;
            }
        }
        return false;
    }

    //
    // Calculates if any of the card logos are to be shown - in that case return true
    //
    public function showCardsList()
    {
        $logoArray = explode(',', $this->getConfigData('showlogos'));
        foreach ($logoArray as $item) {
            if ($item == 'AMEX' ||
                $item == 'BAX' ||
                $item == 'DIN' ||
                $item == 'DK' ||
                $item == 'FFK' ||
                $item == 'JCB' ||
                $item == 'MC' ||
                $item == 'MTRO' ||
                $item == 'MOCA' ||
                $item == 'VISA' ||
                $item == 'ELEC' ||
                $item == 'AKTIA' ||
                $item == 'DNB' ||
                $item == 'EDK' ||
                $item == 'ELV' ||
                $item == 'EW' ||
                $item == 'FSB' ||
                $item == 'GIT' ||
                $item == 'ING' ||
                $item == 'SEB' ||
                $item == 'SHB' ||
                $item == 'SOLO' ||
                $item == 'VAL') {
                return true;
            }
        }
        return false;
    }

    protected $_dibsCountryCodes = array(
        'DK' => 'da',
        'SE' => 'sv',
        'NO' => 'no',
        'GB' => 'en',
        'IR' => 'en',
        'NL' => 'nl',
        'DE' => 'de',
        'FR' => 'fr',
        'FI' => 'fi',
        'ES' => 'es',
        'IT' => 'it',
        //array('' => 'fo'),  // Färöarna?
        'PL' => 'pl'
    );

    protected function convertToDibsCountryCode($iso_code)
    {
        if (array_key_exists($iso_code, $this->_dibsCountryCodes)) {
            return $this->_dibsCountryCodes[$iso_code];
        }
        else {
            // lowercase and return
            return strtolower($iso_code);
        }
    }

    /**
     * Convert our "frontend-methods" to DIBS paytype
     * @param string $frontend_method
     * @return string
     */
    protected function _getPayTypeFromFrontendMethod($frontend_method)
    {
        switch ($frontend_method) {
            //credit cards
            case Icommerce_Dibs_Helper_Data::PAYMENT_METHOD_VISA:
                return 'VISA';
            case Icommerce_Dibs_Helper_Data::PAYMENT_METHOD_MASTERCARD:
                return 'MC';
            case Icommerce_Dibs_Helper_Data::PAYMENT_METHOD_AMEX:
                return 'AMEX';

            //swedish banks
            case Icommerce_Dibs_Helper_Data::PAYMENT_METHOD_SE_SWEDBANK:
                return 'FSB';
            case Icommerce_Dibs_Helper_Data::PAYMENT_METHOD_SE_NORDEA:
                return 'NDB';
            case Icommerce_Dibs_Helper_Data::PAYMENT_METHOD_SE_HANDELSBANKEN:
                return 'SHB';
            case Icommerce_Dibs_Helper_Data::PAYMENT_METHOD_SE_SEB:
                return 'SEB_A';

            //finnish banks
            case Icommerce_Dibs_Helper_Data::PAYMENT_METHOD_FI_NORDEA:
                return 'SOLOFI';
            case Icommerce_Dibs_Helper_Data::PAYMENT_METHOD_FI_OSUUSPANKKI:
                return 'OKO';
            case Icommerce_Dibs_Helper_Data::PAYMENT_METHOD_FI_SAMPO:
                return 'SAMPO';
            case Icommerce_Dibs_Helper_Data::PAYMENT_METHOD_FI_AKTIA:
                return 'AKTIA';
        }
        return '';
    }

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Fontis_Australia_Model_Payment_Paymate
     */
    public function assignData($data)
    {
        $details = array();
        if ($this->getUsername()) {
            $details['username'] = $this->getUsername();
        }
        if (isset($data['frontend_method'])) {
            $details['frontend_method'] = $data['frontend_method'];
        }
        if (isset($data['fees'])) {
            $details['fees'] = $data['fees'];
            if (Icommerce_Default::isModuleActive('Icommerce_DibsFee')) {
                $fee = Mage::getModel('dibsfee/fees')->getFee($data['fees']);
                $details['cardtype'] = $fee['card'];
            }
        }
        if (!empty($details)) {
            $this->getInfoInstance()->setAdditionalData(serialize($details));
        }
        return $this;
    }

    public function isTest()
    {
        return $this->getConfigData('api_test');
    }

    public function getUsername()
    {
        return $this->getConfigData('username');
    }

    protected function getFailureURL()
    {
        $url = $this->getConfigData('url_failure');

        return $url;
    }

    protected function getAcceptURL()
    {
        $url = $this->getConfigData('url_accept');

        return $url;
    }

    protected function getCallbackURL()
    {
        $url = $this->getConfigData('url_callback');

        return $url;
    }

    public function getUrl()
    {
        switch ($this->getConfigData('window_type')) {
            case 3:
                $url = $this->getConfigData('cgi_url_payment_window');
                if (!$url) {
                    $url = self::CGI_URL_PAYMENT_WINDOW;
                }
                break;
            case 1:
                $url = $this->getConfigData('cgi_url_flex');
                if (!$url) {
                    $url = self::CGI_URL_FLEX;
                }
                break;
            default:
                $url = $this->getConfigData('cgi_url');
                if (!$url) {
                    $url = self::CGI_URL;
                }
                break;
        }
        return $url;
    }

    public function getCaptureUrl()
    {
        $url = $this->getConfigData('cgi_url_capture');
        if (!$url) {
            $url = self::CGI_URL_CAPTURE;
        }
        return $url;
    }

    public function getCancelUrl()
    {
        $user = $this->getConfigData('account_number',$this->getStoreId());
        $passwd = $this->getConfigData('account_password',$this->getStoreId());
        $userrefund = $this->getConfigData('account_number_refund',$this->getStoreId());
        if ($userrefund!="") $user = $userrefund;
        $url = self::CGI_URL_CANCEL;
        return str_replace('#CRED#',$user . ':' . $passwd . '@',$url);
    }

    public function getRefundUrl()
    {
        $user = $this->getConfigData('account_number',$this->getStoreId());
        $passwd = $this->getConfigData('account_password',$this->getStoreId());
        $userrefund = $this->getConfigData('account_number_refund',$this->getStoreId());
        if ($userrefund!="") $user = $userrefund;
        $url = self::CGI_URL_REFUND;
        return str_replace('#CRED#',$user . ':' . $passwd . '@',$url);
    }

    public function getFinalSuccessUrl()
    {
        return Mage::getUrl('checkout/onepage/success');
    }

    public function getFinalFailureURL()
    {
        if ($this->getConfigData('redirect_to_cart_on_cancel')) {
            return Mage::getUrl('checkout/cart');
        }

        return Mage::getUrl('checkout/onepage/failure');
    }

    function createMessage($formKeyValues)
    {
        $string = "";
        if (is_array($formKeyValues)) {
            ksort($formKeyValues); // Sort the posted values by alphanumeric
            foreach ($formKeyValues as $key => $value) {
                if ($key != "MAC") { // Don't include the MAC in the calculation of the MAC.
                    if (strlen($string) > 0) $string .= "&";
                    $string .= "$key=$value"; // create string representation
                }
            }
        }
        return $string;
    }

    function hextostr($hex)
    {
        $string = "";
        foreach (explode("\n", trim(chunk_split($hex, 2))) as $h) {
            $string .= chr(hexdec($h));
        }
        return $string;
    }

    function calculateMac($formKeyValues, $HmacKey)
    {
        $MAC = NULL;
        if (is_array($formKeyValues)) {
            $messageToBeSigned = $this->createMessage($formKeyValues);
            if ($messageToBeSigned) {
                $MAC = hash_hmac("sha256", $messageToBeSigned, $this->hextostr($HmacKey));
            }
        }
        return $MAC;
    }

    public function cleanPhoneNumber($phonenumber)
    {
        $phonenumber = preg_replace('~  (?! ^ \+)  [^\d]  ~x', "", $phonenumber);
        return $phonenumber;
    }

    public function getDibsEscapedString($str)
    {
        $res = str_replace(";", " ", str_replace("'", " ", str_replace('"', ' ', $str)));
        return $res;
    }

    public function getCheckoutFormFields()
    {
        $shpaddr = $this->getQuote()->getShippingAddress();
        $invaddr = $this->getQuote()->getBillingAddress();
        $currency_code = $this->getQuote()->getBaseCurrencyCode();
        $cost = $shpaddr->getBaseSubtotal() - $shpaddr->getBaseDiscountAmount();
        if ($this->getConfigData('use_base_currency_order_review')) {
            $shipping = $shpaddr->getBaseShippingAmount();
        } else {
            $shipping = $shpaddr->getShippingInclTax();
        }
        $language = $invaddr->getCountry();
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($this->getCheckout()->getLastRealOrderId());
        if ($order->getQuoteId() != $this->getQuote()->getId()) {
            throw new Exception('Could not load order details for DIBS.');
        }
        $new_payment_window_format = false;
        if ($this->getConfigData('window_type')==3) {
            $new_payment_window_format = true;
        }
        $convertor = Mage::getModel('sales/convert_order');
        $invoice = $convertor->toInvoice($order);
        $check_test = $this->isTest();

        $merchant = $this->getConfigData('account_number');
        $tag = $this->getConfigData('account_tag');
        $mac_hex = $this->getConfigData('mac_hex');
        $order_id = $order->getRealOrderId();
//        $fake_order_id = '9'.$order_id;
        $fake_order_id = $order_id;
        if($this->getConfigData('use_base_currency')){
            $currency = $this->convertToDibsCurrency($order->getBaseCurrency());
            $amount = round($order->getBaseTotalDue() * 100,0);
        } else {
            $currency = $this->convertToDibsCurrency($order->getOrderCurrency());
            $amount = $order->getTotalDue() * 100;
        }

        $key1 = $this->getConfigData('md5_k1');
        $key2 = $this->getConfigData('md5_k2');

        $items = $this->getQuote()->getItemsCollection();

        $ordline = array();

        $md5 = "merchant=$merchant&orderid=$fake_order_id&currency=$currency&amount=$amount";
        $md5 = md5($key2 . md5($key1 . $md5));

        $lang = $this->getConfigData('language');
        if ($lang == "auto") {
            $lang = $this->convertToDibsCountryCode($invaddr->getCountry());
        }
        if ($new_payment_window_format) {
            $fields = array(
                'acceptReturnUrl' => Mage::getUrl($this->getAcceptURL(), array('_secure' => $this->isFrontendSecure())),
                'amount' => $amount,
                'callbackurl' => Mage::getUrl($this->getCallbackURL(), array('_secure' => $this->isFrontendSecure())),
                'cancelReturnUrl' => Mage::getUrl($this->getFailureURL(), array('_secure' => $this->isFrontendSecure())),
                'currency' => $currency,
                'merchant' => $merchant,
                'language' => $lang,
                'orderid' => $fake_order_id,
                's_magentoorderid' => $order_id,
            );
            $invaddrarr = array(
                'billingFirstName' => $invaddr->getFirstname(),
                'billingLastName' => $invaddr->getLastname(),
                'billingAddress' => $invaddr->getStreet(1),
                'billingPostalCode' => $invaddr->getPostcode(),
                'billingPostalPlace' => $invaddr->getCity(),
                'billingEmail' => $order->getCustomerEmail(),
                'billingMobile' => $this->cleanPhoneNumber($invaddr->getTelephone()),
            );
            $shpaddrarr = array(
                'shippingFirstName' => $shpaddr->getFirstname(),
                'shippingLastName' => $shpaddr->getLastname(),
                'shippingAddress' => $shpaddr->getStreet(1),
                'shippingPostalCode' => $shpaddr->getPostcode(),
                'shippingPostalPlace' => $shpaddr->getCity(),
            );
            $ordline['oiTypes'] = "QUANTITY;UNITCODE;DESCRIPTION;AMOUNT;ITEMID;VATAMOUNT";

            $ordlinecounter = 1;
            $oi_total_amount = 0;
            $oi_total_tax = 0;

            foreach ($items as $item)
            {
                if ($item->getProductType()=='simple' && $item->getParentItemId()>0) continue;
                $oi_unit = "st";
                if ($item->getUnit()) $unit = $item->getUnit();
                $itemQuantity = (int)$item->getQty();
                $oi_name = $this->getDibsEscapedString($item->getName());
                $oi_sku = $this->getDibsEscapedString($item->getSku());
                // Some installations miss the below field, but if it is there, we MUST use it as "BaseRowTotal" can be incl or excl vat, depending on settings
                if ($item->getBaseRowTotalInclTax()!=NULL) {
                    $oi_amount = round((($item->getBaseRowTotalInclTax() - $item->getBaseDiscountAmount() - $item->getBaseTaxAmount()) * 100) / $itemQuantity);
                } else {
                    $oi_amount = round((($item->getBaseRowTotal() - $item->getBaseDiscountAmount() ) * 100) / $itemQuantity);
                }
                $oi_taxamount = round(($item->getBaseTaxAmount() * 100) / $itemQuantity);
                $oi_total_amount += ($oi_amount * $itemQuantity);
                $oi_total_tax += ($oi_taxamount * $itemQuantity);
                $ordline['oiRow' . $ordlinecounter] = $itemQuantity . ";" . $oi_unit . ";" . $oi_name . ";" . $oi_amount . ";" . $oi_sku . ";" . $oi_taxamount;
                $ordlinecounter++;
            }
            $base_total_due = round($order->getBaseTotalDue() * 100);
            $base_tax_amount = round($order->getBaseTaxAmount() * 100);

            // Gift cards
            if ($order->getBaseGiftCardsAmount()) {
                $oi_name = $this->getDibsEscapedString(Mage::helper('dibs')->__("Gift Certificate Amount"));
                $oi_sku = $this->getDibsEscapedString(Mage::helper('dibs')->__("GIFTCARD"));
                $oi_amount = round($order->getBaseGiftCardsAmount() * -100);
                $oi_total_amount += $oi_amount;
                $oi_taxamount = 0;
                $ordline['oiRow' . $ordlinecounter] = "1;st;" . $oi_name . ";" .  $oi_amount . ";" . $oi_sku . ";" . $oi_taxamount;
                $ordlinecounter++;
            }

            // Freight and other costs
            if ($oi_total_amount+$oi_total_tax!=$base_total_due) {
                $oi_name = $this->getDibsEscapedString(Mage::helper('dibs')->__("Freight and other costs"));
                $oi_sku = $this->getDibsEscapedString(Mage::helper('dibs')->__("FREIGHT"));
                $oi_amount = ($base_total_due - $base_tax_amount) - $oi_total_amount;
                $oi_amount = round($oi_amount);
                $oi_taxamount = $base_tax_amount - $oi_total_tax;
                $oi_taxamount = max(0, $oi_taxamount); //This can be negative in some cases!
                $oi_amount = max(0, $oi_amount); //This can be negative in some cases! (with free shipping + usage of catalog price rules + copon code). See @Kontor-54
                $oi_taxamount = round($oi_taxamount);
                $ordline['oiRow' . $ordlinecounter] = "1;st;" . $oi_name . ";" .  $oi_amount . ";" . $oi_sku . ";" . $oi_taxamount;
                $ordlinecounter++;
            }

        } else {
            $shipping_description_arr = explode(" ", $order->getShippingDescription());
            if (sizeof($shipping_description_arr)>0) {
                $shipping_description = preg_replace('/[^a-z0-9_ ]/i', '', $shipping_description_arr[0]);
            } else {
                $shipping_description = Mage::helper('adminhtml')->__('Shipping');
            }
            $fields = array(
                'amount' => $amount,
                'currency' => $currency,
                'merchant' => $merchant,
                'orderid' => $fake_order_id,
                'md5key' => $md5,
                'lang' => $lang,
                'uniqueoid' => 'yes', // Must be set for capture and if we use the md5 keys....
                'priceinfo1.' . $this->getConfigData('vat_field_label') => $order->getTaxAmount(),
                'priceinfo2.' . $shipping_description => $shipping,
                'magentoorderid' => $order_id,
                'accepturl' => Mage::getUrl($this->getAcceptURL(), array('_secure' => $this->isFrontendSecure())),
                'cancelurl' => Mage::getUrl($this->getFailureURL(), array('_secure' => $this->isFrontendSecure())),
                'callbackurl' => Mage::getUrl($this->getCallbackURL(), array('_secure' => $this->isFrontendSecure())),
            );
            $invaddrarr = array(
                'delivery01.' . Mage::helper('adminhtml')->__('Billing Address') => ' ',
                'delivery02.Namn' => $invaddr->getFirstname() . " " . $invaddr->getLastname(),
                'delivery03.Adress' => $invaddr->getStreet(1),
                'delivery04.Postadress' => $invaddr->getPostcode() . ' ' . $invaddr->getCity(),
                'delivery05.Land' => Mage::app()->getLocale()->getCountryTranslation($invaddr->getCountry()),
                'delivery06.Telefon' => $invaddr->getTelephone(),
                'delivery07.E-mail' => $order->getCustomerEmail(),
            );
            $shpaddrarr = array(
                'delivery08.' . Mage::helper('adminhtml')->__('Shipping Address') => ' ',
                'delivery09.Namn' => $shpaddr->getFirstname() . " " . $shpaddr->getLastname(),
                'delivery10.Adress' => $shpaddr->getStreet(1),
                'delivery11.Postadress' => $shpaddr->getPostcode() . ' ' . $shpaddr->getCity(),
                'delivery12.Land' => Mage::app()->getLocale()->getCountryTranslation($shpaddr->getCountry()),
                'delivery13.Telefon' => $shpaddr->getTelephone(),
            );
            if ($this->getConfigData('decorator') && $this->getConfigData('decorator') != 'own') {
                $fields['decorator'] = $this->getConfigData('decorator');
                if ($this->getConfigData('color') && $this->getConfigData('color') != '0' && $this->getConfigData('color') != 'default' && $this->getConfigData('color') != 'blank') {
                    $fields['color'] = $this->getConfigData('color');
                }
            }
            if (isset($additionalData['cardtype'])) {
                if ($additionalData['cardtype']!="") {
                    $fields['cardtype'] = $additionalData['cardtype'];
                }
            }
            $fields['cardholder_address1'] = $invaddr->getStreet(1);
            $fields['cardholder_zipcode'] = $invaddr->getPostcode();

            $ordline['ordline0-1'] = Mage::helper('adminhtml')->__('SKU');
            $ordline['ordline0-2'] = Mage::helper('adminhtml')->__('Product Name');
            $ordline['ordline0-3'] = Mage::helper('adminhtml')->__('Qty');
            $ordline['ordline0-4'] = Mage::helper('adminhtml')->__('Price');
            $ordline['ordline0-5'] = Mage::helper('adminhtml')->__('Total');

            $ordlinecounter = 1;

            foreach ($items as $item)
            {
                if ($item->getProductType()=='simple' && $item->getParentItemId()>0) continue;
                if ($this->getConfigData('use_base_currency_order_review')) {
                    $price = $item->getPrice();
                } else {
                    $price = Mage::app()->getStore()->convertPrice($item->getPrice());
                }
                $itemQuantity = (int)$item->getQty();
                $ordline['ordline' . $ordlinecounter . '-1'] = $item->getSku();
                $ordline['ordline' . $ordlinecounter . '-2'] = $item->getName();
                $ordline['ordline' . $ordlinecounter . '-3'] = $itemQuantity;
                $ordline['ordline' . $ordlinecounter . '-4'] = $price;
                $ordline['ordline' . $ordlinecounter . '-5'] = $price * $itemQuantity;
                $ordlinecounter++;
            }

        }
        if ($invaddr->getFirstname() != $shpaddr->getFirstname() or
            $invaddr->getLastname() != $shpaddr->getLastname() or
            $invaddr->getStreet(1) != $shpaddr->getStreet(1) or
            $invaddr->getPostcode() != $shpaddr->getPostcode() or
            $invaddr->getCity() != $shpaddr->getCity() or
            $invaddr->getCountry() != $shpaddr->getCountry() or
            $invaddr->getTelephone() != $shpaddr->getTelephone()) {
            $address = array_merge($invaddrarr, $shpaddrarr);
        } else {
            $address = $invaddrarr;
        }
        if ($check_test == 1) {
            $fields['test'] = 1;
        }

        //preselect payment type (if available), so DIBS does not ask for this again
        $additionalData = unserialize($order->getPayment()->getAdditionalData());
        if (isset($additionalData['frontend_method'])) {
            $paytype = $this->_getPayTypeFromFrontendMethod($additionalData['frontend_method']);
            if ($paytype != '') {
                $fields['paytype'] = $paytype;
            }
        }

        if ($this->getConfigData('direct_capture') == 1) {
            $fields['capturenow'] = '1';
        }

        if ($tag!="") {
            $fields['account'] = $tag;
        }

        $result = array_merge($ordline, $fields, $address);

        if ($new_payment_window_format) {
            $mac = $this->calculateMac($result,$mac_hex);
            $result['MAC'] = $mac;
        }

        // Log this request
        Icommerce_Log::writeSeqFile(Mage::getBaseDir("var") . "/dibs", "dibs", array_merge($result, array('HTTP_COOKIE' => getenv('HTTP_COOKIE'))));

        return $result;
    }

    public function isInitializeNeeded()
    {
        return $this->_isInitializeNeeded;
    }

    public function initialize($paymentAction, $stateObject)
    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus('pay_pending');
        $stateObject->setIsNotified(false);
    }

    public function authorize(Varien_Object $payment, $amount)
    {
        return $this;
    }

    public function scanStatusAndResult($out,&$status,&$result)
    {
        // Ugly but works :)
        $reason = NULL;
        $message = NULL;
        $res = explode('&', $out);
        if (isset($res[0])) {
            $tmp = explode('=', $res[0]);
            $status = isset($tmp[1]) ? $tmp[1] : '';
        }
        if (isset($res[1])) {
            $tmp = explode('=', $res[1]);
            $result = isset($tmp[1]) ? $tmp[1] : '';
        }
        if (isset($res[2])) {
            $tmp = explode('=', $res[2]);
            $reason = isset($tmp[1]) ? $tmp[1] : '';
        }
        if (isset($res[3])) {
            $tmp = explode('=', $res[3]);
            $message = isset($tmp[1]) ? $tmp[1] : '';
        }

        switch ($status) {
            case 'DECLINED':
                switch ($result) {
                    case 1:
                        $result = "No response from acquirer";
                        break;
                    case 2:
                        $result = "Error in the parameters sent to the DIBS server. An additional parameter called 'message' is returned, with a value that may help identifying the error.";
                        break;
                    case 3:
                        $result = "Credit card expired";
                        break;
                    case 4:
                        $result = "Rejected by acquirer";
                        break;
                    case 5:
                        $result = "Authorisation older than7 days";
                        break;
                    case 6:
                        $result = "Transaction status on the DIBS server does not allow capture";
                        break;
                    case 7:
                        $result = "Amount too high";
                        break;
                    case 8:
                        $result = "Amount is zero";
                        break;
                    case 9:
                        $result = "Order number (orderid) does not correspond to the authorisation order number";
                        break;
                    case 10:
                        $result = "Re-authorisation of the transaction was rejected";
                        break;
                    case 11:
                        $result = "Not able to communicate with the acquier";
                        break;
                    case 15:
                        $result = "Capture was blocked by DIBS";
                        break;
                }
                if ($reason) {
                    $result = $result . ' - ' . $reason;
                }
                if ($message) {
                    $result = $result . ' - ' . $message;
                }
                break;
        }
    }

    public function tryCancel(Varien_Object $payment)
    {
        $ares = array(0,'');
        $order = $payment->getOrder();
        $this->setStoreId($order->getStoreId());
        if ($this->getConfigData('preform_cancel',$this->getStoreId())==0) {
            $ares[0] = 1; // Do nothing
            $ares[1] = 'Cancel';
            return $ares;
        }
        $merchant = $this->getConfigData('account_number',$this->getStoreId());
        $tag = $this->getConfigData('account_tag',$this->getStoreId());
        $increment_id = $order->getData("entity_id");
        $order_id = $order->getData("increment_id");
//        $fake_order_id = '9'.$order_id;
        $fake_order_id = $order_id;

        $key1 = $this->getConfigData('md5_k1',$this->getStoreId());
        $key2 = $this->getConfigData('md5_k2',$this->getStoreId());

        $additionaldata = unserialize($order->getPayment()->getAdditionalData());

        $transact = $additionaldata['transactionNumber'];
        if ($transact <= 0) {
            $ares[0] = -1;
            $ares[1] = Mage::helper('dibs')->__('Transaction does not contain necessary Dibs information so it was not able to cancel the amount');
            return $ares;
        }

        if ($additionaldata['transactionCaptured'] == 'yes') {
            $ares[0] = 0;
            return $ares; // New idea, when doing cancel on a part captured invoice, it should simply cancel it in Magento, don't do anything at Dibs...
//            return $this->tryRefund($payment);
        }

        $md5 = "merchant=$merchant&orderid=$fake_order_id&transact=$transact";
        $md5 = md5($key2 . md5($key1 . $md5));

        $fields = array(
            'merchant' => $merchant,
            'orderid' => $fake_order_id,
            'md5key' => $md5,
            'textreply' => 'true',
            'transact' => $transact,
        );
        if ($tag!="") {
            $fields['account'] = $tag;
        }

        $url = $this->getCancelUrl();
        $ch = curl_init($url);
        $r = curl_setopt($ch, CURLOPT_POST, true);
        $r = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $r = curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $out = curl_exec($ch);
        curl_close($ch);

        Icommerce_Log::writeSeqFile(Mage::getBaseDir("var") . "/dibs", "cancel", $out);

        if (!$out) {
            $ares[0] = -1;
            $ares[1] = Mage::helper('dibs')->__("Unknown error in communication with Dibs");
            return $ares;
        }

        $this->scanStatusAndResult($out,$status,$result);

        switch ($status) {
            case 'DECLINED':
                $ares[0] = 2;
                $ares[1] = Mage::helper('dibs')->__("Dibs card transaction can not be canceled") . " " . Mage::helper('dibs')->__("DIBS Order ID") . ": " . $transact . " : " . $result;
                break;
            case 'ACCEPTED':
                $ares[0] = 0;
                $ares[1] = Mage::helper('dibs')->__("Dibs card transaction canceled successfully");
                Mage::dispatchEvent( 'vaimo_paymentmethod_order_canceled', array(
                    'store_id' => $order->getStoreId(),
                    'order_id' => $order->getIncrementId(),
                    'method' => 'dibs',
                    'amount' => $order->getBaseTotalDue()
                    ));
                break;
            default:
                $ares[0] = -1;
                $ares[1] = Mage::helper('dibs')->__("Unknown error in communication with Dibs");
                break;
        }
        return $ares;
    }

    public function tryRefund(Varien_Object $payment, $inamount)
    {
        $ares = array(0,'');
        $order = $payment->getOrder();
        $this->setStoreId($order->getStoreId());
        if ($this->getConfigData('preform_refund',$this->getStoreId())==0) {
            $ares[0] = 1; // Do nothing
            $ares[1] = 'Refund';
            return $ares;
        }
        $merchant = $this->getConfigData('account_number',$this->getStoreId());
        $tag = $this->getConfigData('account_tag',$this->getStoreId());
        $increment_id = $order->getData("entity_id");
        $order_id = $order->getData("increment_id");
//        $fake_order_id = '9'.$order_id;
        $fake_order_id = $order_id;
//        $currency = $this->convertToDibsCurrency($order->getOrderCurrency());
        $currency = $this->convertToDibsCurrency($order->getBaseCurrency());
        $amount = round($inamount * 100,0);

        $key1 = $this->getConfigData('md5_k1',$this->getStoreId());
        $key2 = $this->getConfigData('md5_k2',$this->getStoreId());

        $additionaldata = unserialize($order->getPayment()->getAdditionalData());

        $transact = $additionaldata['transactionNumber'];
        if ($transact <= 0) {
            $ares[0] = -1;
            $ares[1] = Mage::helper('dibs')->__('Transaction does not contain necessary Dibs information so it was not able to cancel the amount');
            return $ares;
        }

        $md5 = "merchant=$merchant&orderid=$fake_order_id&transact=$transact&amount=$amount";
        $md5 = md5($key2 . md5($key1 . $md5));

        $fields = array(
            'merchant' => $merchant,
            'orderid' => $fake_order_id,
            'md5key' => $md5,
            'textreply' => 'true',
            'transact' => $transact,
            'amount' => $amount,
            'currency' => $currency,
        );
        if ($tag!="") {
            $fields['account'] = $tag;
        }

        $url = $this->getRefundUrl();
        $ch = curl_init($url);
        $r = curl_setopt($ch, CURLOPT_POST, true);
        $r = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $r = curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $out = curl_exec($ch);
        curl_close($ch);

        Icommerce_Log::writeSeqFile(Mage::getBaseDir("var") . "/dibs", "refund", $out);

        if (!$out) {
            $ares[0] = -1;
            $ares[1] = Mage::helper('dibs')->__("Unknown error in communication with Dibs");
            return $ares;
        }

        $this->scanStatusAndResult($out,$status,$result);

        switch ($status) {
            case 'DECLINED':
                $ares[0] = 2;
                $ares[1] = Mage::helper('dibs')->__("Dibs card transaction can not be refunded") . " " . Mage::helper('dibs')->__("DIBS Order ID") . ": " . $transact . " : " . $result;
                break;
            case 'ACCEPTED':
                $ares[0] = 0;
                $ares[1] = Mage::helper('dibs')->__("Dibs card transaction refunded successfully");
                Mage::dispatchEvent( 'vaimo_paymentmethod_order_refunded', array(
                    'store_id' => $order->getStoreId(),
                    'order_id' => $order->getIncrementId(),
                    'method' => 'dibs',
                    'amount' => $inamount
                    ));
                break;
            default:
                $ares[0] = -1;
                $ares[1] = Mage::helper('dibs')->__("Unknown error in communication with Dibs");
                break;
        }
        return $ares;
    }
/*
    public function void(Varien_Object $payment)
    {
        $data = $this->getInfoInstance();

        throw new Mage_Core_Exception('stop stop stop');
        return $this;
    }

    public function cancel(Varien_Object $payment)
    {
        return $this->void($payment);
    }

    public function refund(Varien_Object $payment, $amount)
    {
        $data = $this->getInfoInstance();

        throw new Mage_Core_Exception('stop stop stop');
        return $this;
    }
*/

    public function getIsDummy(){
        //Enterprise_Pbridge_Model_Observer is checking if this is set for some strange reason.
        //Added code to be safe - unsure what this actually does mean.  /magnus
        return true;
    }

    public function getInvoiceFee($country)
    {
        $res = 0;
        if (Icommerce_Default::isModuleActive('Icommerce_DibsFee')) {
            $additional_data = unserialize($this->getInfoInstance()->getAdditionalData());
            $res = Mage::getModel('dibsfee/fees')->getCalcFee($this->getQuote(),$additional_data);
        }
        return $res;
    }

    public function getCanHaveCost()
    {
        $res = false;
        if (Icommerce_Default::isModuleActive('Icommerce_DibsFee')) {
            $res = true;
        }
        return $res;
    }

    public function isAvailable($quote = null)
    {
        $is_available = parent::isAvailable($quote);
        $store_id = Mage::app()->getStore()->getStoreId();
        $minimum_sum = Mage::getStoreConfig('payment/dibs/minimumsum', $store_id);
        if ($is_available && $quote && $minimum_sum){
            $grand_total = $quote->getGrandTotal();
            if ($grand_total >= $minimum_sum){
                return true;
            }else{
                return false;
            }
        }else{
            return $is_available;
        }
    }

    /**
     * If this setting is turned on then account pages and checkout pages will be behind https.
     * @return bool
     */
    public function isFrontendSecure()
    {
        return (bool)Mage::getStoreConfig('web/secure/use_in_frontend');
    }
}