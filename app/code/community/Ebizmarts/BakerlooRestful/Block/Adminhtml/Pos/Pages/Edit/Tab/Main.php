<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Pages_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('pos_staticpage');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('bakerloo_restful')->__('Configuration')));

        $fieldset->addField('pagesize', 'text', array(
            'name'     => 'page[pagesize]',
            'label'    => Mage::helper('bakerloo_restful')->__('Page Size'),
            'id'       => 'pagesize',
            'required' => true,
        ));

        $fieldset->addField('startpage', 'text', array(
            'name'     => 'page[startpage]',
            'label'    => Mage::helper('bakerloo_restful')->__('Start Page'),
            'id'       => 'startpage',
            'required' => true,
        ));

        $fieldset->addField('resource', 'select', array(
            'name'      => 'page[resource]',
            'label'     => Mage::helper('bakerloo_restful')->__('Resource'),
            'title'     => Mage::helper('bakerloo_restful')->__('Resource'),
            'required'  => true,
            'values'    => array(
                array(
                    'label' => Mage::helper('bakerloo_restful')->__('Categories'),
                    'value' => 'categories'
                ),
                array(
                    'label' => Mage::helper('bakerloo_restful')->__('Customers'),
                    'value' => 'customers'
                ),
                array(
                    'label' => Mage::helper('bakerloo_restful')->__('Products'),
                    'value' => 'products'
                ),
                array(
                    'label' => Mage::helper('bakerloo_restful')->__('Inventory'),
                    'value' => 'inventory'
                ),
            )
        ));

        $fieldset->addField('store_id', 'select', array(
            'name'      => 'page[store_id]',
            'label'     => Mage::helper('bakerloo_restful')->__('Store View'),
            'title'     => Mage::helper('bakerloo_restful')->__('Store View'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false),
        ));

        if(!$model->getPagesize()) {
            $model->setPagesize(100);
        }
        if(!$model->getStartpage()) {
            $model->setStartpage(1);
        }

        $fieldset->addField('extensive_cache_url', 'hidden',
            array(
                'name'  => 'extensive_cache_url'
            ));

        $model->setExtensiveCacheUrl($this->getGenerateCacheUrl());

        $data = $model->getData();

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getGenerateCacheUrl() {
        return $this->getUrl('adminhtml/pos_pages/cache');
    }

}