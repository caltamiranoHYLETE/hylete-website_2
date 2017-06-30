<?php

class Icommerce_Scheduler_Block_Adminhtml_History_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $form->addField('created_at', 'label', array(
            'label'     => Mage::helper('scheduler')->__('Started'),
            'name'      => 'created_at',
        ));

        $form->addField('finished_at', 'label', array(
            'label'     => Mage::helper('scheduler')->__('Finished'),
            'name'      => 'finished_at',
        ));

        $form->addField('status', 'select', array(
            'label'     => Mage::helper('scheduler')->__('Status'),
            'name'      => 'status',
            'values'    => Mage::helper('scheduler')->getHistoryStatusesOptionArray(true),
        ));

        $form->addField('message', 'label', array(
            'label'     => Mage::helper('scheduler')->__('Message'),
            'name'      => 'message',
        ));

        $form->addField('result', 'textarea', array(
            'label'     => '', //Mage::helper('scheduler')->__('Result'),
            'name'      => 'result',
            'required'  => false,
            'style'     => 'width: 100%; height: 400px;',
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        $form->setValues(Mage::registry('current_scheduler_history')->getData());

        return parent::_prepareForm();
    }

}