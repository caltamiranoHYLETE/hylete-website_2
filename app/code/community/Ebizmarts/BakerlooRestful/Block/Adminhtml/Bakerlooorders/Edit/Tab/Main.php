<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Bakerlooorders_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('bakerlooorder');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('bakerlooorder_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('bakerloo_restful')->__('General data')));

        $fieldset->addField('admin_user', 'text', array(
            'name'  => 'order[admin_user]',
            'label' => Mage::helper('bakerloo_restful')->__('Admin username'),
            'id'    => 'admin_user',
            'required' => true,
        ));

        $fieldset->addField('json_payload', 'textarea', array(
            'name'  => 'order[json_payload]',
            'label' => Mage::helper('bakerloo_restful')->__('JSON post data'),
            'id'    => 'json_payload',
            'required' => true,
            'style' => 'height: 400px; width: 500px;',
        ));

        $fieldset->addField('json_request_headers', 'textarea', array(
            'name'  => 'order[json_request_headers]',
            'label' => Mage::helper('bakerloo_restful')->__('JSON request Headers'),
            'id'    => 'json_request_headers',
            'required' => true,
        ));

        $fieldset->addField('user_agent', 'text', array(
            'name'  => 'order[user_agent]',
            'label' => Mage::helper('bakerloo_restful')->__('User Agent'),
            'id'    => 'user_agent',
        ));

        $fieldset->addField('request_url', 'text', array(
            'name'  => 'order[request_url]',
            'label' => Mage::helper('bakerloo_restful')->__('Url'),
            'id'    => 'request_url',
        ));

        $fieldset->addField('order_id', 'text', array(
            'name'  => 'order[order_id]',
            'label' => Mage::helper('bakerloo_restful')->__('Magento Order #'),
            'id'    => 'order_id',
        ));

        $fieldset->addField('order_increment_id', 'text', array(
            'name'  => 'order[order_increment_id]',
            'label' => Mage::helper('bakerloo_restful')->__('Magento Increment #'),
            'id'    => 'order_increment_id',
        ));

        $fieldset->addField('device_order_id', 'text', array(
            'name'  => 'order[device_order_id]',
            'label' => Mage::helper('bakerloo_restful')->__('Device Order #'),
            'id'    => 'device_order_id',
        ));

        $fieldset->addField('device_id', 'text', array(
            'name'  => 'order[device_id]',
            'label' => Mage::helper('bakerloo_restful')->__('Device ID'),
            'id'    => 'device_id',
        ));

        $fieldset->addField('fail_message', 'textarea', array(
            'name'  => 'order[fail_message]',
            'label' => Mage::helper('bakerloo_restful')->__('Error Message'),
            'id'    => 'fail_message',
            'note'     => Mage::helper('bakerloo_restful')->__('Error message when trying to save order in Magento, if present.'),
        ));

        $fieldset->addField('admin_user_auth', 'text', array(
            'name'  => 'order[admin_user_auth]',
            'label' => Mage::helper('bakerloo_restful')->__('Admin username override'),
            'id'    => 'admin_user_auth',
        ));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'order[id]',
            ));
        }

        $receipts = Mage::getModel('bakerloo_email/queue')
                        ->getCollection()
                        ->addFieldToFilter('order_id', array('eq' => $model->getOrderId()))
                        ->addFieldToFilter('attachment', array('notnull' => 'attachment'))
                        ->setCurPage(1)
                        ->setPageSize(1)
                        ->load();

        $receipt = $receipts->getFirstItem();
        $model->setReceipt($receipt->getAttachment());

        if($model->getReceipt()) {
            $fieldset->addField('receipt', 'link', array(
                'name'  => 'order[receipt]',
                'label' => Mage::helper('bakerloo_restful')->__('Receipt'),
                'id'    => 'receipt',
                'href'  => $this->getUrl('adminhtml/bakerlooorders/downloadreceipt', array('receipt' => $receipt->getId()))
            ));
        }

        $data = $model->getData();

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}