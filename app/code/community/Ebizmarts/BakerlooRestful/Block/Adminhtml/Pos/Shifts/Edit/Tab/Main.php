<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Shifts_Edit_Tab_Main extends Mage_Adminhtml_Block_Abstract { //Mage_Adminhtml_Block_Widget_Form {

    public function _construct() {
        parent::_construct();

        $this->setTemplate('bakerloo_restful/shifts/edit.phtml');
    }

    public function getShift() {
        return Mage::registry('pos_shift');
    }

    public function getShiftState() {
        return ((int)$this->getShift()->getState() == 0 ? 'closed' : 'open');
    }

    public function getOpeningComments() {
        return $this->getShift()->getOpenNotes();
    }

    public function getClosingComments() {
        return $this->getShift()->getCloseNotes();
    }

    public function getOpenDetails() {
        $shift = $this->getShift();

        $details = array();

        if($shift !== false and is_array($shift->getOpenAmounts())) {
            foreach($shift->getOpenAmounts() as $amt) {
                $_currency = Mage::app()->getLocale()->currency($amt->currency_code);

                $details[] = array(
                    'amount' => $_currency->toCurrency($amt->amount),
                    'refunds' => $_currency->toCurrency($amt->refunds),
                    'balance' => $_currency->toCurrency($amt->balance)
                );
            }
        }

        return $details;
    }

    public function getCloseDetails() {
        $shift = $this->getShift();

        $details = array();

        if($shift !== false and is_array($shift->getCloseAmounts())) {

            $nextday = $shift->getNextdayCurrenciesByCode() ? $shift->getNextdayCurrenciesByCode() : array();

            foreach($shift->getCloseAmounts() as $amt) {
                $_currency = Mage::app()->getLocale()->currency($amt->currency_code);

                $left = isset($nextday[$amt->currency_code]) ? $nextday[$amt->currency_code] : 0;
                if($left)
                    $left = $left->amount;
                else
                    $left = $amt->amount;

                $details[$amt->currency_code] = array(
                    'amount' => $_currency->toCurrency($amt->amount),
                    'amount_left' => $_currency->toCurrency($left),
                    'refunds' => $_currency->toCurrency($amt->refunds),
                    'balance' => $_currency->toCurrency($amt->balance)
                );
            }
        }

        return $details;
    }

    public function getOpenTableHeaders() {
        return array(
            'Amount',
            'Refunds',
            'Balance'
        );
    }

    public function getCloseTableHeaders() {
        return array(
            'Amount',
            'Amount left',
            'Refunds',
            'Balance'
        );
    }

    public function getBackUrl() {
        return $this->getUrl('*/*/');
    }
}