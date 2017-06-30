<?php

class Ebizmarts_BakerlooPayment_Block_Info_Manualcc extends Ebizmarts_BakerlooPayment_Block_Info_Default {

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml() {

        $output = $this->getMethod()->getTitle();

        $info = $this->getInfo();

        $poNumber = $info->getPoNumber();
        if($poNumber)
            $output .= "<br />" . $this->__("Authorization Number: %s", $poNumber);

        $ccType = null;

        if($info->getCcType()) {
            $ccType = $info->getCcType();
        }
        else {
            $posOrder = $this->loadPosOrder($this->getInfo()->getOrder()->getId());
            if($posOrder->getId()) {

                $payload = json_decode($posOrder->getJsonPayload());

                if(is_object($payload)) {
                    $ccType = $payload->payment->cc_type;
                }
            }
        }

        if($ccType) {
            $ccTypeName = $this->getCcTypeName($ccType);
            $output .= "<br />" . $this->__("Credit Card: %s", $ccTypeName);
        }

        return $output;
    }

    /**
     * Retrieve credit card type name
     * @param string $ccType
     * @return string
     */
    public function getCcTypeName($ccType = null) {
        $types  = Mage::getSingleton('payment/config')->getCcTypes();
        $ccType = is_null($ccType) ? $this->getInfo()->getCcType() : $ccType;
        if (isset($types[$ccType])) {
            return $types[$ccType];
        }
        return (empty($ccType)) ? Mage::helper('payment')->__('N/A') : $ccType;
    }

}