<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Widget_Grid_Column_Renderer_OrderPaymentMethod extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $result = parent::render($row);

        $orderObj = json_decode($row->getJsonPayload());

        $payments = $this->_getPaymentMethods($orderObj);

        if(!empty($payments)) {

            foreach($payments as $_payment) {
                $result = "[{$_payment['code']}]";

                $result .= "<br />";

                if($_payment['label'] != '') {
                    $result .= $_payment['label'];

                    $result .= "<br />";
                }

                if(!empty($_payment['added_payments'])) {
                    foreach ($_payment['added_payments'] as $addedPaymentLabel)
                        $result .= $addedPaymentLabel . ", ";

                    $result = substr($result, 0, -2);
                }

            }

        }

        return $result;
    }

    /**
     * Render column for export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport(Varien_Object $row) {

        $result = '';

        $orderObj = json_decode($row->getJsonPayload());

        $payments = $this->_getPaymentMethods($orderObj);

        if(!empty($payments)) {

            foreach($payments as $_payment) {
                $result = '';

                if($_payment['label'] != '')
                    $result .= $_payment['label'];
                else
                    $result .= $_payment['code'];

                $result .= ', ';

                if(!empty($_payment['added_payments'])) {
                    foreach ($_payment['added_payments'] as $addedPaymentLabel)
                        $result .= $addedPaymentLabel . ", ";

                }

                $result = substr($result, 0, -2);

            }

        }

        return $result;
    }

    private function _getPaymentMethods($orderObj) {
        $return        = array();

        if($orderObj !== false) {

            if(isset($orderObj->payment)) {

                if(isset($orderObj->payment->payMethod)) {

                    $retdata = array(
                        'code'  => $orderObj->payment->payMethod->code,
                        'label' => $orderObj->payment->payMethod->label,
                        'added_payments' => array()
                    );

                    if (isset($orderObj->payment->addedPayments) and !empty($orderObj->payment->addedPayments)) {
                        foreach ($orderObj->payment->addedPayments as $multiplePayment) {
                            if(!isset($multiplePayment->payMethod) or empty($multiplePayment->payMethod))
                                continue;

                            $retdata['added_payments'] [] = $multiplePayment->payMethod->label;
                        }
                    }

                    $return[] = $retdata;

                }
                else {
                    $return[] = array(
                        'code'  => $orderObj->payment->method,
                        'label' => ''
                    );
                }

            }
        }

        return $return;
    }

}