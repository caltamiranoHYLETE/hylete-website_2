<?php

class Ebizmarts_BakerlooRestful_Model_Api_Shifts extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_model = "bakerloo_restful/shift";

    protected function _getIndexId() {
        return 'id';
    }

    public function _createDataObject($id = null, $data = null) {
        $result = array();

        if(is_null($data))
            $data = Mage::getModel($this->_model)->load($id);

        if($data->getId()){
            $result = array(
                "shift_id" => (int)$data->getId(),
                "shift_guid" => $data->getShiftGuid(),
                "device_shift_id" => (int)$data->getDeviceShiftId(),
                "device_id" => $data->getDeviceId(),
                "state" => (int)$data->getState(),
                "user" => $data->getUser(),
                "open_date" => $data->getOpenDate(),
                "open_notes" => $data->getOpenNotes(),
                "open_amounts" => $data->getOpenAmounts(),
                "close_date" => $data->getCloseDate(),
                "close_notes" => $data->getCloseNotes(),
                "close_amounts" => $data->getCloseAmounts(),
                "sales_amount" => (float)$data->getSalesAmount(),
                "sales_amount_currency" => $data->getSalesAmountCurrency(),
                "nextday_currencies" => $data->getNextdayCurrencies()
            );
        }

        return $result;
    }

    public function post() {
        parent::post();

        $h = Mage::helper('bakerloo_restful');

        if(!$this->getStoreId()) {
            Mage::throwException($h->__('Please provide a Store ID.'));
        }

        $data = $this->getJsonPayload();
        if(!isset($data->shift))
            Mage::throwException($h->__('No shift data provided.'));

        $shiftData = $data->shift;
        $deviceId = $this->getDeviceId();

        $shift = Mage::getModel($this->_model)->load($shiftData->guid, 'shift_guid');
        if($shift->getId())
            Mage::throwException("Duplicate POST for `{$shiftData->guid}`.");

        $openCurrencies = $shiftData->json_open_currencies ? $shiftData->json_open_currencies : json_encode($shiftData->opened_currencies);
        $closeCurrencies = $shiftData->json_closed_currencies ? $shiftData->json_closed_currencies : json_encode($shiftData->closed_currencies);
        $nextdayCurrencies = isset($shiftData->json_nextday_currencies) and $shiftData->json_nextday_currencies ? $shiftData->json_nextday_currencies : json_encode($shiftData->nextday_currencies);

        $shift->setShiftGuid($shiftData->guid)
            ->setDeviceShiftId($shiftData->id)
            ->setDeviceId($deviceId)
            ->setUser($shiftData->user)
            ->setOpenDate($shiftData->opened_date)
            ->setOpenNotes($shiftData->opened_notes)
            ->setJsonOpenCurrencies($openCurrencies)
            ->setCloseDate($shiftData->closed_date)
            ->setCloseNotes($shiftData->closed_notes)
            ->setJsonCloseCurrencies($closeCurrencies)
            ->setCountedAmount($shiftData->counted_amount)
            ->setState($shiftData->state)
            ->setSalesAmountCurrency($shiftData->sales_amount_currency)
            ->setJsonVatbreakdown($shiftData->json_vatbreakdown)
            ->setJsonNextdayCurrencies($nextdayCurrencies)
            ->save();

        $salesAmt = 0;

        $activities = $data->activities;
        foreach($activities as $_activity)
        {
            $activity = Mage::getModel('bakerloo_restful/shift_activity')
                ->setShiftId($shift->getId())
                ->setActivityDate($_activity->date)
                ->setComment($_activity->comment)
                ->setType($_activity->activity)
                ->save();

            foreach($_activity->movements as $_movement)
            {
                Mage::getModel('bakerloo_restful/shift_movement')
                    ->setActivityId($activity->getId())
                    ->setShiftId($shift->getId())
                    ->setCurrencyCode($_movement->currency_code)
                    ->setAmount($_movement->amount)
                    ->setRefunds($_movement->refunds)
                    ->setBalance($_movement->balance)
                    ->save();

                $salesAmt += $_movement->amount;
            }

        }

        $transactions = $data->transactions;
        foreach($transactions as $transaction) {
            $orders = implode(', ', $transaction->orders);
            $comment = empty($orders) ? '' : $h->__("Orders: %s", $orders);

            $payment = "[{$transaction->payment_code}] \n {$transaction->payment_title}";

            $activity = Mage::getModel('bakerloo_restful/shift_activity')
                ->setShiftId($shift->getId())
                ->setActivityDate($transaction->date)
                ->setType(Ebizmarts_BakerlooRestful_Model_Activity::TYPE_TRANSACTION)
                ->setPaymentMethod($payment)
                ->setComments($comment)
                ->save();


            Mage::getModel('bakerloo_restful/shift_movement')
                ->setActivityId($activity->getId())
                ->setShiftId($shift->getId())
                ->setCurrencyCode($transaction->currency_code)
                ->setAmount($transaction->total)
                ->setRefunds($transaction->total_refunds)
                ->save();

            $salesAmt += $transaction->total;
        }

//        $adjustments = $data->adjustments;
//        foreach($adjustments as $adjustment) {
//            $activity = Mage::getModel('bakerloo_restful/shift_activity')
//                ->setShiftId($shift->getId())
//                ->setActivityDate($adjustment->date)
//                ->setType(Ebizmarts_BakerlooRestful_Model_Activity::TYPE_ADJUSTMENT)
//                ->setComments($adjustment->notes)
//                ->save();
//
//
//            Mage::getModel('bakerloo_restful/shift_movement')
//                ->setActivityId($activity->getId())
//                ->setShiftId($shift->getId())
//                ->setCurrencyCode($adjustment->currency_code)
//                ->setAmount($adjustment->amount)
//                ->setBalance($adjustment->balance)
//                ->save();
//
//            $salesAmt += $adjustment->amount;
//
//        }

        $shift->setSalesAmount($salesAmt)
            ->save();

        return $this->_createDataObject(null, $shift);
    }
}