<?php

class Icommerce_Slideshow_Model_Transitions
{
   public function toOptionArray()
    {
        return array(
        	array('value'=>'hslide', 'label'=>Mage::helper('slideshow')->__('Vertikal')),
            array('value'=>'vslide', 'label'=>Mage::helper('slideshow')->__('Horisontell')),
            array('value'=>'fade', 'label'=>Mage::helper('slideshow')->__('Fade')),
        );
    }
 
}