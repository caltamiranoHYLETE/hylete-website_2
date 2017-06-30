<?php


class Icommerce_JsonProductInfo_Model_AttributeForm_Observer {

    /*protected function prepareOptions( $opts_in ){
        $opts_out = array();
        foreach( $opts_in as $opt ){
            $opts_out[$opt] = $opt;
        }
        return $opts_out;
    }

    protected function prepareTabArray(){
        $codes = Icommerce_DynamicTabs_Helper_Data::getTabCodes();
        //$r = array( "label"=>"", "value"=>"" );
        foreach( $codes as $ix => $v ){
            $r[] = array( "label"=>$v, "value"=>$v );
        }
        return $r;
    }*/


    public function onPrepareForm( $obs ){
        $form = $obs->getData("form");
        $attr = $obs->getData("attribute");

        //$fieldset = $form->getElement('front_fieldset');
        // Dyn tabs properties fieldset
        $fieldset = $form->addFieldset('jsonproductinfo_fieldset', array('legend'=>Mage::helper('catalog')->__('Json Product Info')));

        //$hlp = Mage::helper("jsonproductinfo");

        $yesno = Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray();

        $fieldset->addField('jsonproductinfo_cache', 'select', array(
            'name' => 'jsonproductinfo_cache',
            'label' => Mage::helper('eav')->__('Cache for Aggregate/Simple'),
            'title' => Mage::helper('eav')->__('Cache for Aggregate/Simple '),
            'note'  => Mage::helper('eav')->__('Store values for this attribute in cache for aggregate product relations'),
            'values' => $yesno,
        ));

        /* @var $fieldset Varien_Data_Form_Element_Fieldset */
        $fieldset = $form->getElement('base_fieldset');
        if( $attr->getData("is_configurable") ){
            // This is the field to instruct that we want to supress a give super attribute when creating
            // the array to "walk product in  a structured way"
            $fieldset->addField('jsonproductinfo_suppress_lookup', 'select', array(
                'name' => 'jsonproductinfo_suppress_lookup',
                'label' => Mage::helper('eav')->__('Suppress frontend lookup in Json'),
                'title' => Mage::helper('eav')->__('Suppress frontend lookup in Json '),
                'note'  => Mage::helper('eav')->__('Supress lookuing up simple values with this superattribut on frontend'),
                'values' => $yesno,
            ));
        }
    }

}

