<?php

class Icommerce_Attributebinder_Block_Adminhtml_Attributebinder_Edit_Tab_Attributes extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getRead()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getWrite()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    protected function _getTableName($modelEntity)
    {
        return Mage::getSingleton('core/resource')->getTableName($modelEntity);
    }

    protected function _syncBindOptions()
    {
        if (!$data = Mage::registry('attributebinder_data')) {
            return;
        }

        if (!$data->getId()) {
            return;
        }

        $select = $this->_getRead()->select()
            ->from(array('o' => $this->_getTableName('eav/attribute_option')), '')
            ->join(array('ov' => $this->_getTableName('eav/attribute_option_value')),
                'ov.option_id = o.option_id AND ov.store_id = 0',
                array(new Zend_Db_Expr('"' . $data->getId() . '" as id'), 'value'))
            ->joinLeft(array('b' => $this->_getTableName('attributebinder/bindings')),
                'b.attributebinder_id = ' . (int)$data->getId() . ' AND b.bind_attribute_value = ov.value', '')
            ->where('o.attribute_id = ?', $data->getBindAttributeId())
            ->where('b.bind_attribute_value IS NULL');

        $insert = $this->_getWrite()->insertFromSelect($select, $this->_getTableName('attributebinder/bindings'), array('attributebinder_id', 'bind_attribute_value'));
        $this->_getWrite()->query($insert);
    }


    private function getMainAttributeValues()
    {
        $data = Mage::registry('attributebinder_data')->getData();
        if( !$data ) return array();
        return Mage::getModel('attributebinder/attributebinder')->getAttributeValuesId($data["main_attribute_id"], true);
    }

    protected function _prepareForm()
    {
        $this->_syncBindOptions();

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $actionforward = $this->getRequest()->getBeforeForwardInfo('action_name');
        $actionmethod = $this->getRequest()->getActionName();

        $general = $form->addFieldset( 'general_form', array('legend' => $this->__('Attribute Details')) );

        /*if (!$actionforward && $actionmethod == 'edit')
        {
            $lookbook_id = Mage::app()->getRequest()->getParam('id');
        }*/

        $general->addField('main_attribute_id', 'select', array(
            'label'     => Mage::helper('attributebinder')->__('Target attribute'),
            'name'      => 'main_attribute_id',
            'required'  => true,
            'values'    => Mage::getModel("attributebinder/attributebinder")->getAttributes(),
            'note'      => 'The attribute that holds the generalized (multiple to one) values'
        ));


        $general->addField('bind_attribute_id', 'select', array(
            'label'     => Mage::helper('attributebinder')->__('Source attribute'),
            'name'      => 'bind_attribute_id',
            'required'  => true,
            'values'    => Mage::getModel("attributebinder/attributebinder")->getAttributes(),
            'note'      => 'The attribute that you are generalizing'
        ));

        $general->addField('default_main_attribute', 'select', array(
            'label'     => Mage::helper('attributebinder')->__('Default attribute'),
            'name'      => 'default_main_attribute',
            'required'  => false,
            'values'    => $this->getMainAttributeValues(),
            'note'      => 'The generalized value in case a binding has not been defined (Available after saving the source/target)'
        ));

        $general->addField('suppress_man_main_attr', 'select', array(
            'label'     => Mage::helper('attributebinder')->__('Suppress when manually set target attribute value'),
            'title'     => Mage::helper('attributebinder')->__('Suppress when manually set target attribute value'),
            'name'      => 'suppress_man_main_attr',
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray()
        ));

        if ( Mage::getSingleton('adminhtml/session')->getAttributeBinderData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getAttributeBinderData());
            Mage::getSingleton('adminhtml/session')->setAttributeBinderData(null);
        }
        elseif ( Mage::registry('attributebinder_data') )
            $form->setValues(Mage::registry('attributebinder_data')->getData());
        return parent::_prepareForm();
    }
}