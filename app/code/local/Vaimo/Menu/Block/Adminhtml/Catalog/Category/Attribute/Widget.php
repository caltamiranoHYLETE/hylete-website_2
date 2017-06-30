<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_Menu
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Vaimo_Menu_Block_Adminhtml_Catalog_Category_Attribute_Widget extends Varien_Data_Form_Element_Text
{
    protected $_widgetData = array();

    public function setValue($value)
    {
        if ($attribute = $this->getEntityAttribute()) {
            $value = $attribute->getFrontend()->getValue(new Varien_Object(array($this->_id => $value)));
        }

        try {
            parse_str($value, $this->_widgetData);
        } catch (Exception $e) {}

        return parent::setValue($value);
    }

    protected function _getButtonsHtml()
    {
        $buttonsHtml = '';
        $layout = Mage::app()->getLayout();
        $widgetData = $this->getWidgetData();
        $selectedWidget = !$widgetData ? 'Add' : 'Configure';
        $libInstanceName = Mage::getBlockSingleton('vaimo_menu/adminhtml_catalog_category_widget')->getInstanceName();
        $instanceId = '';
        if (isset($widgetData['instance_id'])) {
            $instanceId = $widgetData['instance_id'];
        }

        $htmlId = $this->getHtmlId();

        $buttonsHtml .= $layout->createBlock('adminhtml/widget_button', '', array(
            'label'     => Mage::helper('catalog')->__($selectedWidget),
            'type'      => 'button',
            'disabled'  => $this->getDisabled(),
            'class'     => 'btn-widget widget-configuration-button ' . (!$this->getDisabled() ? ($widgetData ? 'success' : 'add') : ''),
            'onclick'   => "${libInstanceName}.configure('${htmlId}', '${instanceId}')"
        ))->toHtml();

        $buttonsHtml .= $layout->createBlock('adminhtml/widget_button', '', array(
            'label'     => Mage::helper('catalog')->__('Remove'),
            'type'      => 'button',
            'disabled'  => !$widgetData || $this->getDisabled(),
            'class'     => 'btn-widget widget-remove-button ' . ($widgetData && !$this->getDisabled() ? 'delete' : '') ,
            'onclick'   => "${libInstanceName}.remove('${htmlId}', '${instanceId}')"
        ))->toHtml();

        return $buttonsHtml;
    }

    public function getWidgetData()
    {
        return $this->_widgetData;
    }

    public function getElementHtml()
    {
        $widgetData = $this->getWidgetData();
        $labelStyle = !$widgetData ? 'display: none' : '';

        $label = (isset($widgetData['widget_label']) ? $widgetData['widget_label'] : '');
        $html = '<span class="btn-widget-label label" style="'. $labelStyle . '">' . $label . '</span>';
        $this->addClass('widget-attribute');

        $buttonsHtml = $this->_getButtonsHtml();

        $html .= '<span>' . $buttonsHtml . '</span>';

        return $html . parent::getElementHtml();
    }
}