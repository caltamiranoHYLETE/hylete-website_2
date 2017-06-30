<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Notifications_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('pos_notification');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('notification_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('bakerloo_restful')->__('General data')));

        $fieldset->addField('title', 'text', array(
            'name'     => 'notification[title]',
            'label'    => Mage::helper('bakerloo_restful')->__('Title'),
            'id'       => 'title',
            'required' => true,
        ));

        $fieldset->addField('description', 'textarea', array(
            'name'     => 'notification[description]',
            'label'    => Mage::helper('bakerloo_restful')->__('Description'),
            'id'       => 'description',
            'required' => true,
        ));

        $fieldset->addField('severity', 'select', array(
            'name'     => 'notification[severity]',
            'label'    => Mage::helper('bakerloo_restful')->__('Severity'),
            'id'       => 'severity',
            'required' => true,
            'options'  => Mage::helper('bakerloo_restful')->getSeverityOptions()
            ));

        $fieldset->addField('url', 'text', array(
            'name'     => 'notification[url]',
            'label'    => Mage::helper('bakerloo_restful')->__('URL'),
            'id'       => 'url',
            'required' => false,
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'multiselect', array(
                'name'      => 'notification[stores][]',
                'label'     => Mage::helper('bakerloo_restful')->__('Store View'),
                'title'     => Mage::helper('bakerloo_restful')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        }
        else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'notification[stores][]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'notification[id]',
            ));
        }

        $data = $model->getData();

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}