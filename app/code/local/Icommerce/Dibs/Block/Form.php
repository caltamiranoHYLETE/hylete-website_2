<?php
class Icommerce_Dibs_Block_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('icommerce/dibs/form.phtml');
        parent::_construct();
    }
    
    public function getDibsFees()
    {
        $res = array();
        if (Icommerce_Default::isModuleActive('Icommerce_DibsFee')) {
            $res = Mage::getModel('dibsfee/fees')->getApplicableFees($this->getMethod()->getInfoInstance()->getQuote());
        }
    	return $res;
    }
    
    
}
