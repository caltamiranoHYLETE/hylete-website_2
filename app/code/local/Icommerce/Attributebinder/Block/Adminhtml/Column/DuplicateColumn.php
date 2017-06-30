<?php

class Icommerce_Attributebinder_Block_Adminhtml_Column_DuplicateColumn extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        return "<a href=\"#\" class=\"add_more_bindings\">Add more bindings to this value</a>";
    }

}
