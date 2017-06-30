<?php

class Icommerce_Attributebinder_Block_Adminhtml_Column_HiddenFieldColumn extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $id =  $this->getColumn()->getId();
        $hiddenValue = $row->getData($this->getColumn()->getHiddenvalue());
        $value =  $row->getData($this->getColumn()->getIndex());
        return $value.'<input type="hidden" id="'.$id.'" name="'.$id.'" value="'.$value.'" />';
    }

}
