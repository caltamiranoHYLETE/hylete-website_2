<?php

class Cminds_Coupon_Block_Promo_Quote_Edit_Tab_Coupons_Grid_Renderer_Errorvar
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());

        return (string)(($value === null) ? 0 : $value);
    }
}

