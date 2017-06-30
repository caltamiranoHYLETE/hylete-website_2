<?php
class Icommerce_Dibs_Block_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('icommerce/dibs/info.phtml');
    }

    public function getPaymentInfo()
    {
        try {
            $arr = unserialize($this->getInfo()->getAdditionalData());
        } catch (Exception $e) {
            $arr = array();
            $arr['errorMessage'] = "Identification information corrupt, unknown DIBS Order ID";
        }
        return $arr;
    }
}