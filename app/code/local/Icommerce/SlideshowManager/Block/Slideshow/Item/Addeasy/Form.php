<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
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
 * @package     Icommerce_SlideshowManager
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 */

class Icommerce_SlideshowManager_Block_Slideshow_Item_Addeasy_Form extends Mage_Adminhtml_Block_Widget_Form
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
        return array('value' => 'easy', 'label' => Mage::helper('slideshowmanager')->__('Easy'));
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Newsletter_Template_Edit_Form
     */
    protected function _prepareForm()
    {
        /** @var Icommerce_SlideshowManager_Helper_Data $helper */
        $helper = Mage::helper('slideshowmanager');

        $form = new Varien_Data_Form(array(
            'id'      => 'addeasy_form',
            'action'  => $this->getData('action'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $helper->__('Enter the block information below'),
            'class'  => 'fieldset-wide'
        ));

        $itemType = $this->getItemType();
        $fieldset->addField('type', 'hidden', array(
            'name'  => 'type',
            'value' => $itemType['value'],
        ));

        $fieldset->addField('backgroundimage', 'image', array(
            'name'     => 'backgroundimage',
            'label'    => $helper->__('Background image'),
            'title'    => $helper->__('Background image'),
            'required' => true
        ));

        $fieldset->addField('backgroundimage_tablet', 'image', array(
                'name'     => 'backgroundimage_tablet',
                'label'    => $helper->__('Background image (tablet)'),
                'title'    => $helper->__('Background image (tablet)'),
                'required' => false
        ));

        $fieldset->addField('backgroundimage_mobile', 'image', array(
                'name'     => 'backgroundimage_mobile',
                'label'    => $helper->__('Background image (mobile)'),
                'title'    => $helper->__('Background image (mobile)'),
                'required' => false
        ));

        $fieldset->addField('border', 'select', array(
            'name'   => 'border',
            'label'  => $helper->__('Border'),
            'title'  => $helper->__('Border'),
            'values' => $helper->getYesNo()
        ));

        $this->_addCustomTextField($fieldset, 'subtitle', $helper->__('Subtitle'));

        $this->_addCustomTextField($fieldset, 'title', $helper->__('Title'));
        $this->_addCustomTextField($fieldset, 'title_link', $helper->__('Title Link'));

        $fieldset->addField('title_position', 'select', array(
            'name'   => 'title_position',
            'label'  => $helper->__('Title Position'),
            'title'  => $helper->__('Title Position'),
            'values' => $helper->getTitlePosition()
        ));

        $fieldset->addField('text_color', 'select', array(
            'name'   => 'text_color',
            'label'  => $helper->__('Text Color'),
            'title'  => $helper->__('Text Color'),
            'values' => $helper->getColor()
        ));

        $this->_addCustomTextField($fieldset, 'button_1_title', $helper->__('Button 1 Title'));
        $this->_addCustomTextField($fieldset, 'button_1_title_link', $helper->__('Button 1 Title Link'));

        $this->_addCustomTextField($fieldset, 'button_2_title', $helper->__('Button 2 Title'));
        $this->_addCustomTextField($fieldset, 'button_2_title_link', $helper->__('Button 2 Title Link'));

        $this->_addCustomTextField($fieldset, 'button_3_title', $helper->__('Button 3 Title'));
        $this->_addCustomTextField($fieldset, 'button_3_title_link', $helper->__('Button 3 Title Link'));

        $fieldset->addField('button_color', 'select', array(
            'name'   => 'button_color',
            'label'  => $helper->__('Button Color'),
            'title'  => $helper->__('Button Color'),
            'values' => $helper->getColor()
        ));

        $fieldset->addField('status', 'select', array(
            'name'  => 'status',
            'label' => $helper->__('Status'),
            'title' => $helper->__('Status'),
            'required' => true,
            'values' => $helper->getStatuses()
        ));

        /*
        $this->_addCustomTextField($fieldset, 'position', $helper->__('Position'));
        */

        $form->setAction($this->getUrl('*/*/saveeasy'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _addCustomTextField($fieldset, $name, $label)
    {
        $fieldset->addField($name, 'text', array(
            'name'  => $name,
            'label' => $label,
            'title' => $label,
        ));
    }
}
