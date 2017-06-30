<?php

class Icommerce_Slideshow_Model_Intervals
{
   public function toOptionArray()
    {
        return array(
        	array('value'=>1000, 'label'=>Mage::helper('slideshow')->__('1')),
            array('value'=>2000, 'label'=>Mage::helper('slideshow')->__('2')),
            array('value'=>3000, 'label'=>Mage::helper('slideshow')->__('3')),
            array('value'=>4000, 'label'=>Mage::helper('slideshow')->__('4')),
            array('value'=>5000, 'label'=>Mage::helper('slideshow')->__('5')),
            array('value'=>10000, 'label'=>Mage::helper('slideshow')->__('10')),
            array('value'=>15000, 'label'=>Mage::helper('slideshow')->__('15')),
        );
    }
 
}