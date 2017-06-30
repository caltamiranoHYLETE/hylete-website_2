<?php

/**
 * Dibs Checkout Controller
 */
class Icommerce_Dibs_DibsController extends Mage_Core_Controller_Front_Action
{
    protected $_order;
    protected $_callbackAction = false;
    CONST SEMAPHORE_CACHE_KEY = 'semaphore_order_';
    CONST USE_BASE_CURRENCY = 'payment/dibs/use_base_currency';

    protected function _expireAjax()
    {
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
            exit;
        }
    }

    /**
     * Give you logos in unstyled html
     * http://www.yourdomain.com/en/dibs/dibs/showlogos/type/3
     * type 1=only selected trust logos, 2=only selected card logos, omitted or 3 =all selected logos
     * @since 2013-05-24
     */
    public function showlogosAction() {
        $type=$this->getRequest()->getParam('type');
        $give=3;
        if ($type>0 and $type<4) { $give = $type; }
        $html=Mage::helper("dibs/data")->getLogosHtml($give);
        echo $html;
    }

    public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $id = $session->getQuoteId();
        if ($id) {
            Icommerce_Default::logAppend("redirectAction - begin | ".Mage::helper('core/http')->getRemoteAddr(), "var/dibs/dibs-controller.log");
            $session->setDibsQuoteId($id);
            $this->getResponse()->setBody($this->getLayout()->createBlock('dibs/redirect')->toHtml());
            $session->unsQuoteId();
            Icommerce_Default::logAppend("redirectAction - end", "var/dibs/dibs-controller.log");
        } else {
            $this->_redirect('*/*/');
        }
    }

    public function getOrder($order_id = 0)
    {
        if ($this->_order == null) {
            if((int)$order_id == 0) {
               $session = Mage::getSingleton('checkout/session');
               $this->_order = Mage::getModel('dibs/sales_order')->
                       loadByIncrementId($session->getLastRealOrderId());
            } else {
               $this->_order = Mage::getModel('dibs/sales_order')->
                       loadByIncrementId($order_id);
            }
        }

        return $this->_order;
    }

    //Removed cancel randomness by not using sessions to get orderId.
    //Removed possibility to cancel orders with other state than new.
    public function cancelAction()
    {
        Icommerce_Default::logAppend("cancelAction - begin | ".Mage::helper('core/http')->getRemoteAddr(), "var/dibs/dibs-controller.log");
		$dibs = Mage::getModel('dibs/dibs');
        //$session = Mage::getSingleton('checkout/session');
        //$session->setQuoteId($session->getDibsQuoteId(true));

        $order_id = 0;

        if (isset($_REQUEST['magentoorderid'])) {
            $order_id = $_REQUEST['magentoorderid'];
        }
        if (!$order_id) {
            if (isset($_REQUEST['orderid'])) {
                $order_id = $_REQUEST['orderid'];
            }
        }

        if ($order_id !== 0) {

            $order = $this->getOrder($order_id);

            //only cancel if state is new
            if(isset($order) && $order->getState() == "pending_payment"){


                $order->cancel();
                $status_pay_canceled = Icommerce_OrderStatus_Helper_Data::getStatus($dibs->getConfigData('order_status_pay_failed'), $order->getState());
                $order->setStatus($status_pay_canceled);
                if ( method_exists( $order, 'addStatusHistoryComment' ) ) { // Doesn't exist in 1.3...
                    $order->addStatusHistoryComment('Order cancelled by DIBS');
                }
                $order->save();
                if ($dibs->getConfigData('reactivate_quote',$dibs->getStoreId())) {
                    $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
                    if ($quote && $quote->getId()) {
                        if (!$quote->getIsActive()) {
                            $quote->setIsActive(1);
                            $quote->save();
                        }
                    }
                }

                Icommerce_Default::logAppend("Cancel succesful, orderid:" . $order_id, "var/dibs/dibs-controller.log");

                Mage::getModel("icorder/observer")->dispatchCancelled($order);

            } else {

                Icommerce_Default::logAppend("Cancel unsuccesful, order is not new - orderId:" . $order_id, "var/dibs/dibs-controller.log");

            }

        } else {

            Icommerce_Default::logAppend("No orderid provided, NOT cancelling order", "var/dibs/dibs-controller.log");

        }
        if ( isset($_REQUEST['validationErrors']) ) {
            $errorarr = json_decode($_REQUEST['validationErrors']);
            foreach ($errorarr as $heading => $err) {
                foreach ($err as $details) {
                    Icommerce_Default::logAppend("validationErrors: " . $heading . " " . $details, "var/dibs/dibs-controller.log");
                }
            }
        }

        if ($dibs->getConfigData('redirect_to_cart_on_cancel', $dibs->getStoreId())) {
            $cart = Mage::getSingleton('checkout/cart');
            $cart->save();

            Icommerce_Default::logAppend("cancelAction - redirect to cart", "var/dibs/dibs-controller.log");
            $this->_redirect('checkout/cart/');
            return;
        }

        $this->getResponse()->setBody($this->getLayout()->createBlock('dibs/cancel')->toHtml());
        Icommerce_Default::logAppend("cancelAction - end", "var/dibs/dibs-controller.log");
    }

    private function _findFromTransact($transact, $new_payment_window_format)
    {
        $read = Icommerce_Db::getDbRead();
        $rows = $read->query( "SELECT ord.entity_id, ord.increment_id, pay.additional_data FROM sales_flat_order AS ord
                               INNER JOIN sales_flat_order_payment AS pay ON ord.entity_id = pay.parent_id
                               WHERE ord.state NOT IN ('canceled', 'complete', 'closed') AND pay.method = 'dibs' AND pay.additional_data like ?", "%" . $transact . "%");
        foreach ($rows as $row) {
            $data = unserialize($row['additional_data']);
            if ($new_payment_window_format) {
                if (isset($data['transactionNumber'])) {
                    if ($data['transactionNumber']==$transact) {
                        return $row['increment_id'];
                    }
                }
            } else {
                if (isset($data['transact'])) {
                    if ($data['transact']==$transact) {
                        return $row['increment_id'];
                    }
                }
            }
        }
        return NULL;
    }

    public function successAction()
    {
        $semaphoreLock = false;
        $semaphoreLockApplied = false;
    	//Debbugging Purposes
        Icommerce_Default::logAppend(print_r($_REQUEST, true), "var/dibs/dibs-request.log");
    	Icommerce_Default::logAppend(print_r($_POST, true), "var/dibs/dibs-request.log");
    	Icommerce_Default::logAppend(print_r($_GET, true), "var/dibs/dibs-request.log");

        Icommerce_Default::logAppend("successAction - begin | ".Mage::helper('core/http')->getRemoteAddr(), "var/dibs/dibs-controller.log");
        if (!isset($_REQUEST['transact']) && isset($_REQUEST['transaction'])) {
            $new_payment_window_format = true;
        } else {
            $new_payment_window_format = false;
            if (!isset($_REQUEST['authkey'])) {
                $msg = "successaction: authkey missing, exiting!";
                Icommerce_Default::logAppend($msg, "var/dibs/dibs-controller.log");
                return;
            }
            $authkey = $_REQUEST['authkey'];
        }
        if (!isset($_REQUEST['transact']) && !isset($_REQUEST['transaction'])) {
            $msg = "successaction: transact/transaction missing, exiting!";
            Icommerce_Default::logAppend($msg, "var/dibs/dibs-controller.log");
            return;
        }
        if ($new_payment_window_format) {
            $transact = $_REQUEST['transaction'];
        } else {
            $transact = $_REQUEST['transact'];
        }

        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getDibsQuoteId(true));
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
        $order = Mage::getModel('sales/order');
        $dibs = Mage::getModel('dibs/dibs');
		$session_order_id = Mage::getSingleton('checkout/session')->getLastOrderId();
		$order_id = 0;

        if (isset($_REQUEST['s_magentoorderid'])) {
            $order_id = $_REQUEST['s_magentoorderid'];
            $msg = "successaction: s_magentoorderid: " . $order_id;
            Icommerce_Default::logAppend($msg, "var/dibs/dibs-controller.log");
        }
        if (isset($_REQUEST['magentoorderid'])) {
            $order_id = $_REQUEST['magentoorderid'];
            $msg = "successaction: magentoorderid: " . $order_id;
            Icommerce_Default::logAppend($msg, "var/dibs/dibs-controller.log");
        }
        if ($order_id) {
            $msg = "successaction: magentoorderid: " . $order_id;
            Icommerce_Default::logAppend($msg, "var/dibs/dibs-controller.log");
            $order->loadByIncrementId($order_id);
        }
        if (!$order->getId()) {
            if (isset($_REQUEST['orderid'])) {
                $order_id = $_REQUEST['orderid'];
                $msg = "successaction: magentoorderid wrong, trying orderid: " . $order_id;
                Icommerce_Default::logAppend($msg, "var/dibs/dibs-controller.log");
                $order->loadByIncrementId($order_id);
            }
        }
        if (!$order->getId()) {
            if ($transact) {
                $msg = "successaction: orderid wrong, trying searching for transact: " . $transact;
                $order_id = $this->_findFromTransact($transact, $new_payment_window_format);
                if ($order_id) {
                    $order->loadByIncrementId($order_id);
                }
            }
        }
        if (!$order->getId()) {
            $msg = "successaction: probably fake id, trying: " . $session_order_id;
            Icommerce_Default::logAppend($msg, "var/dibs/dibs-controller.log");
            $order->load($session_order_id);
        }
        if (!$order->getId()) {
            $msg = "successaction: INVALID magentoorderid: " . $order_id . "\nlastorderid: " . $session_order_id;
            Icommerce_Default::logAppend($msg, "var/dibs/dibs-controller.log");
            return;
        }
        $dibs->setStoreId($order->getStoreId());
        /**
         * @link http://jira.vaimo.com/browse/LSOUND-160
         * This fix is place due duplicate requests from dibs (one request from customer browser and second request server side request) and they are coming in at the same time.
         * Resulting two order confirmation emails and two ic_order_success events and that might result double order export to ERP.
         */
        $cache = Mage::app()->getCache();
        if ($cache instanceof Varien_Cache_Core) {
            if (!$cache->load(self::SEMAPHORE_CACHE_KEY . $order->getId())) {
                $cache->save("1", self::SEMAPHORE_CACHE_KEY . $order->getId(), array('icommerce_dibs'), 20);
                $semaphoreLockApplied = true;
                Icommerce_Default::logAppend('Applying semaphore lock to order: '. $order->getId(), "var/dibs/dibs-controller.log");
            } else {
                $semaphoreLock = true;
                Icommerce_Default::logAppend('Found semaphore lock on order: '. $order->getId() . ' skipping proccesing.', "var/dibs/dibs-controller.log");
            }
        }

        if (!$semaphoreLock) {
            $merchant = $dibs->getConfigData('account_number',$dibs->getStoreId());

            if(Mage::getStoreConfig(self::USE_BASE_CURRENCY)){
                $currency = $dibs->convertToDibsCurrency($order->getBaseCurrency());
                $amount = $order->getBaseTotalDue() * 100;
            } else {
                $currency = $dibs->convertToDibsCurrency($order->getOrderCurrency());
                $amount = $order->getTotalDue() * 100;
            }

            if ($new_payment_window_format==false) {
                $key1 = $dibs->getConfigData('md5_k1',$dibs->getStoreId());
                $key2 = $dibs->getConfigData('md5_k2',$dibs->getStoreId());

                $md5 = "transact=$transact&amount=$amount&currency=$currency";
                $md5 = md5($key2 . md5($key1 . $md5));

                if ($md5 != $authkey) {
                    $this->getResponse()->setBody($this->getLayout()->createBlock('dibs/success')->toHtml());
                    Icommerce_Default::logAppend("successaction - error md5!=authkey. aborting..", "var/dibs/dibs-controller.log");
                    return;
                }
            }

            $additionaldata = unserialize($order->getPayment()->getAdditionalData());
            if (is_array($additionaldata)) {
                // This is a test if we've been here through callback action
                if (isset($additionaldata['dibs_success_received'])) {
                    if ($additionaldata['dibs_success_received'] == '1') {
                        $this->getResponse()->setBody($this->getLayout()->createBlock('dibs/success')->toHtml());
                        Icommerce_Default::logAppend("successaction - revisited", "var/dibs/dibs-controller.log");
                        if ($semaphoreLockApplied) {
                            $cache->remove(self::SEMAPHORE_CACHE_KEY . $order->getId());
                            Icommerce_Default::logAppend('Removing semaphore lock for order: '. $order->getId(), "var/dibs/dibs-controller.log");
                        }
                        return;
                    }
                }
            } else {
                $additionaldata = array();
            }

            $additionaldata['transactionNumber'] = $transact;
            if ($new_payment_window_format) {
                if (isset($_REQUEST["billingFirstName"]) && isset($_REQUEST["billingLastName"])) {
                    $additionaldata['transactionName'] = $_REQUEST["billingFirstName"] . " " . $_REQUEST["billingLastName"];
                }
                if (isset($_REQUEST["billingAddress"])) {
                    $additionaldata['transactionAddress'] = $_REQUEST["billingAddress"];
                }
                if (isset($_REQUEST["billingPostalCode"])) {
                    $additionaldata['transactionZip'] = $_REQUEST["billingPostalCode"];
                }
                if (isset($_REQUEST["billingPostalPlace"])) {
                    $additionaldata['transactionCountry'] = $_REQUEST["billingPostalPlace"];
                }
                if (isset($_REQUEST["billingMobile"])) {
                    $additionaldata['transactionPhone'] = $_REQUEST["billingMobile"];
                }
                if (isset($_REQUEST["billingEmail"])) {
                    $additionaldata['transactionEmail'] = $_REQUEST["billingEmail"];
                }
                if (isset($_REQUEST["cardNumberMasked"])) {
                    $additionaldata['transactionCardNumber'] = $_REQUEST["cardNumberMasked"];
                }
                if (isset($_REQUEST["cardTypeName"])) {
                    $additionaldata['transactionCardType'] = $_REQUEST["cardTypeName"];
                }
                if (isset($_REQUEST["status"])) {
                    $additionaldata['transactionStatus'] = $_REQUEST["status"];
                }
                $additionaldata['transactionInterface'] = 'dibs_payment_window';
            } else {
                if (isset($_REQUEST["delivery02_Namn"])) {
                    $additionaldata['transactionName'] = iconv("ISO-8859-1", "UTF-8", $_REQUEST["delivery02_Namn"]);
                }
                if (isset($_REQUEST["delivery03_Adress"])) {
                    $additionaldata['transactionAddress'] = iconv("ISO-8859-1", "UTF-8", $_REQUEST["delivery03_Adress"]);
                }
                if (isset($_REQUEST["delivery04_Postadress"])) {
                    $additionaldata['transactionZip'] = iconv("ISO-8859-1", "UTF-8", $_REQUEST["delivery04_Postadress"]);
                }
                if (isset($_REQUEST["delivery05_Land"])) {
                    $additionaldata['transactionCountry'] = iconv("ISO-8859-1", "UTF-8", $_REQUEST["delivery05_Land"]);
                }
                if (isset($_REQUEST["delivery06_Telefon"])) {
                    $additionaldata['transactionPhone'] = $_REQUEST["delivery06_Telefon"];
                }
                if (isset($_REQUEST["delivery07_E-mail"])) {
                    $additionaldata['transactionEmail'] = $_REQUEST["delivery07_E-mail"];
                }
                $additionaldata['transactionInterface'] = 'dibs_standard';
            }
    // Fields below should probably go inside the correct sections of the above if statements...
            if (isset($_REQUEST["paytype"])) {
                $additionaldata['transactionType'] = $_REQUEST["paytype"];
            }
            if (isset($_REQUEST["cardTypeName"])) {
                $additionaldata['cardTypeName'] = $_REQUEST["cardTypeName"];
            }
            if (isset($_REQUEST["acquirer"])) {
                $additionaldata['acquirer'] = $_REQUEST["acquirer"];
            }
            if (isset($_REQUEST["actionCode"])) {
                $additionaldata['actionCode'] = $_REQUEST["actionCode"];
            }
            if (isset($_REQUEST["message"])) {
                $additionaldata['message'] = $_REQUEST["message"];
            }
            if (isset($_REQUEST["expMonth"])) {
                $additionaldata['expMonth'] = $_REQUEST["expMonth"];
            }
            if (isset($_REQUEST["expYear"])) {
                $additionaldata['expYear'] = $_REQUEST["expYear"];
            }

            if (isset($additionaldata['transactionStatus'])) {
                if ($additionaldata['transactionStatus']=='PENDING') {
                    $order->getPayment()->setAdditionalData(serialize(($additionaldata)));
                    $order->getPayment()->setLastTransId($transact);
                    $msg = $this->__("Pending received") . "<br/>" . $this->__("DIBS Order ID") . ": <b>" . $transact . "</b>";
                    $order->addStatusHistoryComment($msg);
                    $order->save();
                    $this->getResponse()->setBody($this->getLayout()->createBlock('dibs/success')->toHtml());
                    Icommerce_Default::logAppend("successaction - end", "var/dibs/dibs-controller.log");
                    return;
                }
            }

            $status_old = $status = $order->getStatus();
            switch ($dibs->getConfigData('direct_capture')) {
                case 0:
                    $state = Mage_Sales_Model_Order::STATE_NEW;
                    $status = Icommerce_OrderStatus_Helper_Data::getStatus($dibs->getConfigData('order_status_reserved'), $state);
                    $additionaldata['transactionCaptured'] = 'no';
                    break;
                case 1:
                    $state = Mage_Sales_Model_Order::STATE_PROCESSING;
                    $status = Icommerce_OrderStatus_Helper_Data::getStatus($dibs->getConfigData('order_status_captured'), $state);
                    $additionaldata['transactionCaptured'] = 'yes';
                    break;
                case 2:
                    $state = Mage_Sales_Model_Order::STATE_NEW;
                    $status = Icommerce_OrderStatus_Helper_Data::getStatus($dibs->getConfigData('order_status_reserved'), $state);
                    $additionaldata['transactionCaptured'] = 'no';
                    break;
            }
            Icommerce_Default::logAppend("successaction  -  status:$status  status_old:$status_old", "var/dibs/dibs-controller.log");

            $additionaldata['dibs_success_received'] = 1;
            $order->getPayment()->setAdditionalData(serialize(($additionaldata)));
            $order->getPayment()->setLastTransId($transact);
            $order->sendNewOrderEmail();
            $order->setEmailSent(true);

            $msg = $this->__("Order created") . "<br/>" . $this->__("DIBS Order ID") . ": <b>" . $transact . "</b>";
            $order->setState($state, $status, $msg, true);
            $order->save();

            // Dispatch event
            Mage::getModel("icorder/observer")->dispatchSuccess($order);
            Mage::dispatchEvent( 'vaimo_paymentmethod_order_reserved', array(
                'store_id' => $order->getStoreId(),
                'order_id' => $order->getIncrementId(),
                'method' => 'dibs',
                'amount' => $order->getBaseTotalDue()
                ));

            if ($additionaldata['transactionCaptured'] == 'yes') {
                Mage::getModel("icorder/observer")->dispatchCaptured($order);
                Mage::dispatchEvent( 'vaimo_paymentmethod_order_captured', array(
                    'store_id' => $order->getStoreId(),
                    'order_id' => $order->getIncrementId(),
                    'method' => 'dibs',
                    'amount' => $order->getBaseTotalDue()
                    ));
            }
        }
        if ($semaphoreLockApplied) {
            $cache->remove(self::SEMAPHORE_CACHE_KEY . $order->getId());
            Icommerce_Default::logAppend('Removing semaphore lock for order: '. $order->getId(), "var/dibs/dibs-controller.log");
        }
        $this->getResponse()->setBody($this->getLayout()->createBlock('dibs/success')->toHtml());
        Icommerce_Default::logAppend("successaction - end", "var/dibs/dibs-controller.log");
    }

    public function callbackAction()
    {
        Icommerce_Default::logAppend("callbackAction - begin | ".Mage::helper('core/http')->getRemoteAddr(), "var/dibs/dibs-controller.log");
        /**
         * @link http://jira.vaimo.com/browse/GEGGAMOJA-65
         *
         * This sleep operation is implemented due change on Dibs side, they added action that will redirect customer back to the store right after successful payment.
         * This resulted quite high number of double order emails due race-condition (two different requests are trying to complete order at exact same time)
         */
        Icommerce_Default::logAppend("callbackAction - begin sleep(1) | ".Mage::helper('core/http')->getRemoteAddr(), "var/dibs/dibs-controller.log");
        sleep(1);
        Icommerce_Default::logAppend("callbackAction - end sleep(1)| ".Mage::helper('core/http')->getRemoteAddr(), "var/dibs/dibs-controller.log");
        $this->successAction();
        // No reason for HTML body
        $this->getResponse()->setBody("");
        Icommerce_Default::logAppend("callbackAction - end", "var/dibs/dibs-controller.log");
    }
}
