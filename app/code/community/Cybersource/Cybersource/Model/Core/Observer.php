<?php

class Cybersource_Cybersource_Model_Core_Observer
{
    private $unwantedInfoKeys = array(
        'authTransactionID',
        'authRequestID',
        'authRequestToken',
        'captureTransactionID',
        'captureRequestID',
        'captureRequestToken',
        'refundTransactionID',
        'refundRequestID',
        'reversalRequestID',
        'reversalRequestToken'
    );

    public function observeSalesOrderConvertReorder(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        if (!$quote->getId() || !$order->getReordered()) {
            return $this;
        }

        foreach ($this->unwantedInfoKeys as $key) {
            $quote->getPayment()->unsAdditionalInformation($key);
        }
    }
}
