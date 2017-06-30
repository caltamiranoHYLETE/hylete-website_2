<?php
class Icommerce_Dibs_Block_Failure extends Mage_Core_Block_Template
{
    // Dead-beat detected.

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
        $this->setTemplate('icommerce/dibs/failure.phtml');
    }
}
