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
class Icommerce_SlideshowManager_Block_Slideshow_Item_Editeasy_Form extends Mage_Adminhtml_Block_Widget_Form
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
        return array('value'=>'easy', 'label'=>Mage::helper('slideshowmanager')->__('Easy'));
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Newsletter_Template_Edit_Form
     */
    protected function _prepareForm()
    {
        $model  = Mage::getModel('slideshowmanager/text');
        $params = $this->getRequest()->getParams();
        $id = (int)$params['id'];
        $item = $model->getItem($id);

        $form   = new Varien_Data_Form(array(
            'id'        => 'editeasy_form',
            'action'    => $this->getData('action'),
            'method'    => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('slideshowmanager')->__('Enter the block information below'),
            'class'     => 'fieldset-wide'
        ));

        $itemType = $this->getItemType();
        $fieldset->addField('type', 'hidden', array(
            'name'      => 'type',
            'value'  => $itemType['value'],
        ));

        $fieldset->addField('id', 'hidden', array(
            'name' => 'id',
            'value' => $id
        ));

        $fieldset->addField('backgroundimage', 'image', array(
            'name'      => 'backgroundimage',
            'label'     => Mage::helper('slideshowmanager')->__('Background image'),
            'title'     => Mage::helper('slideshowmanager')->__('Background image'),
            'required'  => true,
            'value' => Mage::helper('slideshowmanager')->getBackgroundImageUrl($item)
        ));

        $fieldset->addField('backgroundimage_tablet', 'image', array(
                'name'      => 'backgroundimage_tablet',
                'label'     => Mage::helper('slideshowmanager')->__('Background image (tablet)'),
                'title'     => Mage::helper('slideshowmanager')->__('Background image (tablet)'),
                'required'  => false,
                'value' => Mage::helper('slideshowmanager')->getBackgroundImageTabletUrl($item)
        ));

        $fieldset->addField('backgroundimage_mobile', 'image', array(
                'name'      => 'backgroundimage_mobile',
                'label'     => Mage::helper('slideshowmanager')->__('Background image (mobile)'),
                'title'     => Mage::helper('slideshowmanager')->__('Background image (mobile)'),
                'required'  => false,
                'value' => Mage::helper('slideshowmanager')->getBackgroundImageMobileUrl($item)
        ));

        $fieldset->addField('border', 'select', array(
            'name'      => 'border',
            'label'     => Mage::helper('slideshowmanager')->__('Border'),
            'title'     => Mage::helper('slideshowmanager')->__('Border'),
            'values'  => Mage::helper('slideshowmanager')->getYesNo(),
            'value'     => $item['border']
        ));

        $fieldset->addField('subtitle', 'text', array(
            'name'      => 'subtitle',
            'label'     => Mage::helper('slideshowmanager')->__('Subtitle'),
            'title'     => Mage::helper('slideshowmanager')->__('Subtitle'),
            'value'     => $item['subtitle']
        ));

        $fieldset->addField('title', 'text', array(
            'name'      => 'title',
            'label'     => Mage::helper('slideshowmanager')->__('Title'),
            'title'     => Mage::helper('slideshowmanager')->__('Title'),
            'value'     => $item['title']
        ));

        $fieldset->addField('title_link', 'text', array(
            'name'      => 'title_link',
            'label'     => Mage::helper('slideshowmanager')->__('Title Link'),
            'title'     => Mage::helper('slideshowmanager')->__('Title Link'),
            'value'     => $item['title_link']
        ));

        $fieldset->addField('title_position', 'select', array(
            'name'      => 'title_position',
            'label'     => Mage::helper('slideshowmanager')->__('Title Position'),
            'title'     => Mage::helper('slideshowmanager')->__('Title Position'),
            'values'  => Mage::helper('slideshowmanager')->getTitlePosition(),
            'value'     => $item['title_position']
        ));

        $fieldset->addField('text_color', 'select', array(
            'name'      => 'text_color',
            'label'     => Mage::helper('slideshowmanager')->__('Text Color'),
            'title'     => Mage::helper('slideshowmanager')->__('Text Color'),
            'values'  => Mage::helper('slideshowmanager')->getColor(),
            'value'     => $item['text_color']
        ));

        $fieldset->addField('button_1_title', 'text', array(
            'name'      => 'button_1_title',
            'label'     => Mage::helper('slideshowmanager')->__('Button 1 Title'),
            'title'     => Mage::helper('slideshowmanager')->__('Button 1 Title'),
            'value'     => $item['button_1_title']
        ));

        $fieldset->addField('button_1_title_link', 'text', array(
            'name'      => 'button_1_title_link',
            'label'     => Mage::helper('slideshowmanager')->__('Button 1 Title Link'),
            'title'     => Mage::helper('slideshowmanager')->__('Button 1 Title Link'),
            'value'     => $item['button_1_title_link']
        ));

        $fieldset->addField('button_2_title', 'text', array(
            'name'      => 'button_2_title',
            'label'     => Mage::helper('slideshowmanager')->__('Button 2 Title'),
            'title'     => Mage::helper('slideshowmanager')->__('Button 2 Title'),
            'value'     => $item['button_2_title']
        ));

        $fieldset->addField('button_2_title_link', 'text', array(
            'name'      => 'button_2_title_link',
            'label'     => Mage::helper('slideshowmanager')->__('Button 2 Title Link'),
            'title'     => Mage::helper('slideshowmanager')->__('Button 2 Title Link'),
            'value'     => $item['button_2_title_link']
        ));

        $fieldset->addField('button_3_title', 'text', array(
            'name'      => 'button_3_title',
            'label'     => Mage::helper('slideshowmanager')->__('Button 3 Title'),
            'title'     => Mage::helper('slideshowmanager')->__('Button 3 Title'),
            'value'     => $item['button_3_title']
        ));

        $fieldset->addField('button_3_title_link', 'text', array(
            'name'      => 'button_3_title_link',
            'label'     => Mage::helper('slideshowmanager')->__('Button 3 Title Link'),
            'title'     => Mage::helper('slideshowmanager')->__('Button 3 Title Link'),
            'value'     => $item['button_3_title_link']
        ));

        $fieldset->addField('button_color', 'select', array(
            'name'      => 'button_color',
            'label'     => Mage::helper('slideshowmanager')->__('Button Color'),
            'title'     => Mage::helper('slideshowmanager')->__('Button Color'),
            'values'  => Mage::helper('slideshowmanager')->getColor(),
            'value'     => $item['button_color']
        ));

        $fieldset->addField('status', 'select', array(
            'name'      => 'status',
            'label'     => Mage::helper('slideshowmanager')->__('Status'),
            'title'     => Mage::helper('slideshowmanager')->__('Status'),
            'required'  => true,
            'values'  => Mage::helper('slideshowmanager')->getStatuses(),
            'value'     => $item['status']
        ));

        $form->setAction($this->getUrl('*/*/updateeasy'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
