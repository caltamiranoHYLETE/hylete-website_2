<?php

class Ebizmarts_BakerlooReports_Block_Adminhtml_Pos_Reports_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getUrl('*/*/save'), 'method' => 'post'));
        $form->setUseContainer(true);

        $model = $this->getCurrentReport();
        if(!$model)
            $model = Mage::getModel('bakerloo_reports/report');

        $h = Mage::helper('bakerloo_reports');

        $form->setHtmlIdPrefix('pos_report_');

        $fieldset = $form->addFieldset(
            'base_fieldset', 
            array('legend' => $h->__('Report options')));

        $fieldset->addField('title', 'text', array(
            'name'     => 'report[report_name]',
            'label'    => $h->__('Report name'),
            'id'       => 'report_name',
            'required' => true,
        ));

        $fieldset->addField('columns', 'multiselect', array(
            'name'      => 'report[columns]',
            'label'     => $h->__('Columns'),
            'title'     => $h->__('Columns'),
            'required'  => true,
            'values'    => $h->getPosOrderColumns()
        ));

        $fieldset->addField('from', 'date', array(
            'name'      => 'report[from]',
            'label'     => $h->__('From'),
            'title'     => $h->__('From'),
            'required'  => false,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT) ,
            'value'     => date(Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), strtotime('next weekday')),
//            'after_element_html' => '<small>Comments</small>',
        ));

        $fieldset->addField('to', 'date', array(
            'name'      => 'report[to]',
            'label'     => $h->__('To'),
            'title'     => $h->__('To'),
            'required'  => false,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT) ,
            'value'     => date(Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), strtotime('next weekday'))
        ));

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