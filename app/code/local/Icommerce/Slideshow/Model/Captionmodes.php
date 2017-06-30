<?php

class Icommerce_Slideshow_Model_Captionmodes
{
   public function toOptionArray()
    {
        return array(
        	array('value'=>'onload', 'label'=>Mage::helper('slideshow')->__('Alltid')),
            array('value'=>'onhover', 'label'=>Mage::helper('slideshow')->__('Vid mouse over')),
        );
    }
 
}