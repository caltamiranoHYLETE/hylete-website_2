<?php
class Icommerce_Dibs_Block_Cancel extends Mage_Core_Block_Template
{
    function getDibs()
    {
        if ($this->getData('dibsmodel') == null) {
            $this->setDibsmodel(Mage::getModel('dibs/dibs'));
        }
        return $this->getData('dibsmodel');
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('icommerce/dibs/cancel.phtml');
    }
}

