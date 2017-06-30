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
class Icommerce_PageManager_Block_Page_Row_Item_Editwidget_Form extends Mage_Adminhtml_Block_Widget_Form
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

    private $_paramters;

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Newsletter_Template_Edit_Form
     */
    protected function _prepareForm()
    {
        $model  = Mage::getModel('pagemanager/text');
		$params = $this->getRequest()->getParams();
		$id = (int)$params['id'];
		$item = $model->getItem($id);

        $form   = new Varien_Data_Form(array(
            'id'        => 'editwidget_form',
            'action'    => $this->getData('action'),
            'name'      => 'editwidget_form',
            'method'    => 'post'
        ));

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('pagemanager')->__('Enter the information below'),
            'class'     => 'fieldset-wide'
        ));

		$itemType = $this->getItemType();

        $this->_parameters = $this->_getWidgetParameters($item['page_content']);

		$fieldset->addField('type', 'hidden', array(
            'name'      => 'type',
            'value'  => $itemType['value'],
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
            'value'		=> $item['title']
        ));

        $fieldset->addField('status', 'select', array(
            'name'      => 'status',
            'label'     => Mage::helper('pagemanager')->__('Status'),
            'title'     => Mage::helper('pagemanager')->__('Status'),
            'required'  => true,
            'values'  => Mage::helper('pagemanager')->getStatuses(),
            'value'		=> $item['status']
        ));

        $fieldset->addField('position', 'text', array(
            'name'      => 'position',
            'label'     => Mage::helper('pagemanager')->__('Position'),
            'title'     => Mage::helper('pagemanager')->__('Position'),
            'required'  => false,
            'value'		=> $item['position']
        ));

        $fieldset->addField('select_widget_type', 'select', array(
            'label'                 => $this->helper('widget')->__('Widget Type'),
            'title'                 => $this->helper('widget')->__('Widget Type'),
            'name'                  => 'widget_type',
            'required'              => true,
            'values'               => $this->_getWidgetSelectOptions(),
            'after_element_html'    => $this->_getWidgetSelectAfterHtml(),
            'value'                 => $this->_parameters["type"],
            'onchange'=>'fetchParameters(this)',
        ));

        $this->_setSessionJsonParameters();

        $form->setAction($this->getUrl('*/*/updatewidget'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function _setSessionJsonParameters(){

        $parameters = $this->_parameters;
        unset($parameters["type"]);
        $_SESSION["json_widget_parameters"] = json_encode($parameters);
    }

    public function getFormHtml(){
        $html = parent::getFormHtml();
        $end_tag_pos = mb_strripos($html, '</form>', null, 'UTF-8');
        if ($end_tag_pos !== false) {
            $before = mb_substr($html, 0, $end_tag_pos, 'UTF-8');
            $after = mb_substr($html, $end_tag_pos, mb_strlen($html, 'UTF-8'), 'UTF-8');
            $html = $before . '<div id="widget-options"></div>' . $after;
        } else {
            $html .= '<div id="widget-options"></div>';
        }
        return $html;
    }

    protected function _getWidgetParameters($text){

        $parameters = array();
        $pattern = "/ ([a-zA-Z_]+)=\"([a-zA-Z\/0-9_]+)\"/i";

        preg_match_all($pattern,
            $text, $matches);

        if(isset($matches) && sizeof($matches) > 0){
            for($i = 0; $i < sizeof($matches[1]); $i++){
                $parameters[$matches[1][$i].""] = $matches[2][$i];
            }
        }

        return $parameters;

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
