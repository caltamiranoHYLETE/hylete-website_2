<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Bakerloodiscount_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('bakerloodiscount');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('bakerloodiscount_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('bakerloo_restful')->__('General data')));

        $fieldset->addField('discount_max', 'text', array(
            'name'  => 'discount[discount_max]',
            'label' => Mage::helper('bakerloo_restful')->__('Discount Max.'),
            'id'    => 'discount_max',
            'required' => true,
        ));

        $fieldset->addField('discount_type', 'select', array(
            'name'  => 'discount[discount_type]',
            'label' => Mage::helper('bakerloo_restful')->__('Discount Type'),
            'id'    => 'discount_type',
            'required' => true,
            'options'   => Mage::getModel('bakerloo_restful/source_discounttype')->toOptions(),
        ));

        $fieldset->addField('discount_description', 'textarea', array(
            'name'  => 'discount[discount_description]',
            'label' => Mage::helper('bakerloo_restful')->__('Discount Description'),
            'id'    => 'discount_description',
            'required' => true,
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'multiselect', array(
                'name'      => 'discount[stores][]',
                'label'     => Mage::helper('bakerloo_restful')->__('Store View'),
                'title'     => Mage::helper('bakerloo_restful')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        }
        else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'discount[stores][]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'discount[id]',
            ));
        }

        $data = $model->getData();

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}