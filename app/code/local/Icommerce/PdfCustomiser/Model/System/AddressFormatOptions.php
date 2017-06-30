<?php
class Icommerce_PdfCustomiser_Model_System_AddressFormatOptions
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'us', 'label'=>Mage::helper('pdfcustomiser')->__('US')),
            array('value'=>'european', 'label'=>Mage::helper('pdfcustomiser')->__('European'))
        );
    }


}