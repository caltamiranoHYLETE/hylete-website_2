<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Adminhtml Newsletter Template Edit Form Block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Icommerce_PageManager_Block_Page_Row_Item_Addwidget_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Define Form settings
     *
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getItemType()
    {
        return array('value'=>'widget', 'label'=>Mage::helper('pagemanager')->__('WIDGET'));
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Newsletter_Template_Edit_Form
     */
    protected function _prepareForm()
    {
        $model  = Mage::getModel('pagemanager/text');

        $form   = new Varien_Data_Form(array(
            'id'        => 'addwidget_form',
            'name'      => 'addwidget_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('pagemanager')->__('Enter the image information below'),
            'class'     => 'fieldset-wide'
        ));

		$itemType = $this->getItemType();
		$fieldset->addField('type', 'hidden', array(
            'name'      => 'type',
            'value'  => $itemType['value']
        ));
        
        $fieldset->addField('row_id', 'hidden', array(
            'name'      => 'row_id',
            'value'  => $this->getRequest()->getParam('id')
        ));
		
		$fieldset->addField('title', 'text', array(
            'name'      => 'title',
            'label'     => Mage::helper('pagemanager')->__('Block Title'),
            'title'     => Mage::helper('pagemanager')->__('Block Title'),
            'required'  => true,
        ));

        $fieldset->addField('status', 'select', array(
            'name'      => 'status',
            'label'     => Mage::helper('pagemanager')->__('Status'),
            'title'     => Mage::helper('pagemanager')->__('Status'),
            'required'  => true,
            'values'  => Mage::helper('pagemanager')->getStatuses()
        ));

        $fieldset->addField('position', 'text', array(
            'name'      => 'position',
            'label'     => Mage::helper('pagemanager')->__('Position'),
            'title'     => Mage::helper('pagemanager')->__('Position'),
            'required'  => false,
            'value'		=> 0
        ));

        $fieldset->addField('select_widget_type', 'select', array(
            'label'                 => $this->helper('widget')->__('Widget Type'),
            'title'                 => $this->helper('widget')->__('Widget Type'),
            'name'                  => 'widget_type',
            'required'              => true,
            'options'               => $this->_getWidgetSelectOptions(),
            'after_element_html'    => $this->_getWidgetSelectAfterHtml(),
            'onchange'=>'fetchParameters(this)',
        ));

        $form->setAction($this->getUrl('*/*/savewidget'));
        $form->setUseContainer(true);
        $this->setForm($form);


        return parent::_prepareForm();
    }

    public function getFormHtml(){
        $html = parent::getFormHtml();
        $html .= '<div id="widget-options"></div>';
        return $html;
    }

    protected function _getWidgetSelectOptions()
    {
        foreach ($this->_getAvailableWidgets(true) as $data) {
            $options[$data['type']] = $data['name'];
        }
        return $options;
    }

    protected function _getWidgetSelectAfterHtml()
    {
        $html = '<p class="nm"><small></small></p>';
        $i = 0;
        foreach ($this->_getAvailableWidgets(true) as $data) {
            $html .= sprintf('<div id="widget-description-%s" class="no-display">%s</div>', $i, $data['description']);
            $i++;
        }
        return $html;
    }

    protected function _getAvailableWidgets($withEmptyElement = false)
    {
        if (!$this->hasData('available_widgets')) {
            $result = array();
            $allWidgets = Mage::getModel('widget/widget')->getWidgetsArray();
            $skipped = $this->_getSkippedWidgets();
            foreach ($allWidgets as $widget) {
                if (is_array($skipped) && in_array($widget['type'], $skipped)) {
                    continue;
                }
                $result[] = $widget;
            }
            if ($withEmptyElement) {
                array_unshift($result, array(
                    'type'        => '',
                    'name'        => $this->helper('adminhtml')->__('-- Please Select --'),
                    'description' => '',
                ));
            }
            $this->setData('available_widgets', $result);
        }

        return $this->_getData('available_widgets');
    }

    protected function _getSkippedWidgets()
    {
        return Mage::registry('skip_widgets');
    }


}
