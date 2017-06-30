<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Widget_Grid_Column_Renderer_OrderPaymentMethod extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row){
        $result = parent::render($row);

        $method_code = $row->getMethod();
        Mage::getStoreConfig('bakerloo_payment');

        return $result;
    }
}