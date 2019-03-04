<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

class Hylete_CustomCss_Block_CustomCss_Adminhtml_Customcss_Edit_Form extends Vaimo_CustomCss_Block_Adminhtml_Customcss_Edit_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('customcss_data');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'    => 'post'
        ));

        $form->setHtmlIdPrefix('customcss_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('customcss')->__('CSS Information'), 'class' => 'fieldset-wide'));

        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('customcss')->__('Status'),
            'title'     => Mage::helper('customcss')->__('Status'),
            'name'      => 'is_active',
            'required'  => true,
            'options'   => array(
                '1' => Mage::helper('customcss')->__('Enabled'),
                '0' => Mage::helper('customcss')->__('Disabled'),
            ),
        ));

        $fieldset->addField(
            'name', 'text',
            array(
                'label'    => Mage::helper('customcss')->__('Name'),
                'name'     => 'name',
                'index'    => 'name',
                'required' => true,
                 'after_element_html' => '<p class="nm"><small>eg. General - Cart; General - Footer; Custom page - Footer</small></p>'
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('store_id', 'multiselect', array(
                'name'      => 'stores[]',
                'label'     => Mage::helper('customcss')->__('Store View'),
                'title'     => Mage::helper('customcss')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $field->setRenderer($renderer);
        }
        else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $fieldset->addField('filename', 'hidden', array(
            'name'  => 'filename'
        ));

        $fieldset->addField('code', 'textarea', array(
            'name'      => 'code',
            'label'     => Mage::helper('customcss')->__('CSS Code'),
            'title'     => Mage::helper('customcss')->__('CSS Code'),
            'style'     => 'display:none',
            'required'  => false,
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        $form->setValues($model->getData());

        return $this;
    }
}
