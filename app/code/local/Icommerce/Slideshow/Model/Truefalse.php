<?php

class Icommerce_Slideshow_Model_Truefalse
{
   public function toOptionArray()
    {
        return array(
        	array('value'=>'true', 'label'=>Mage::helper('slideshow')->__('Ja')),
            array('value'=>'false', 'label'=>Mage::helper('slideshow')->__('Nej')),
        );
    }
 
}