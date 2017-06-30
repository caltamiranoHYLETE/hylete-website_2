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
class Icommerce_SlideshowManager_Block_Slideshow_Item_Addlayeredhtml_Form extends Mage_Adminhtml_Block_Widget_Form
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
        return array('value'=>'layeredhtml', 'label'=>Mage::helper('slideshowmanager')->__('Layered HTML'));
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Newsletter_Template_Edit_Form
     */
    protected function _prepareForm()
    {
        $model  = Mage::getModel('slideshowmanager/text');

        $form   = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('slideshowmanager')->__('Enter the image information below'),
            'class'     => 'fieldset-wide'
        ));

		$itemType = $this->getItemType();
		$fieldset->addField('type', 'hidden', array(
            'name'      => 'type',
            'value'  => $itemType['value'],
        ));

		$fieldset->addField('title', 'text', array(
            'name'      => 'title',
            'label'     => Mage::helper('slideshowmanager')->__('Block Title'),
            'title'     => Mage::helper('slideshowmanager')->__('Block Title'),
            'required'  => true,
        ));

        $fieldset->addField('slideshow_content', 'editor', array(
            'name'      => 'slideshow_content',
            'label'     => Mage::helper('slideshowmanager')->__('Content'),
            'title'     => Mage::helper('slideshowmanager')->__('Content'),
            'style'     => 'height:36em',
            'required'  => true,
	    	'wysiwyg' => true,
            'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig()
        ));

        $fieldset->addField('positiontop', 'text', array(
            'name'      => 'positiontop',
            'label'     => Mage::helper('slideshowmanager')->__('Content top position'),
            'title'     => Mage::helper('slideshowmanager')->__('Content top position'),
            'required'  => true,
            'value'		=> 0
        ));

        $fieldset->addField('positiontoptype', 'select', array(
            'name'      => 'positiontoptype',
            'label'     => Mage::helper('slideshowmanager')->__('Type'),
            'title'     => Mage::helper('slideshowmanager')->__('Type'),
            'required'  => true,
            'values'  => Mage::helper('slideshowmanager')->getWithAndHeightType()
        ));

        $fieldset->addField('positionleft', 'text', array(
            'name'      => 'positionleft',
            'label'     => Mage::helper('slideshowmanager')->__('Content left position'),
            'title'     => Mage::helper('slideshowmanager')->__('Content left position'),
            'required'  => true,
            'value'		=> 0
        ));

        $fieldset->addField('positionlefttype', 'select', array(
            'name'      => 'positionlefttype',
            'label'     => Mage::helper('slideshowmanager')->__('Type'),
            'title'     => Mage::helper('slideshowmanager')->__('Type'),
            'required'  => true,
            'values'  => Mage::helper('slideshowmanager')->getWithAndHeightType()
        ));

        $fieldset->addField('align', 'select', array(
            'name'      => 'align',
            'label'     => Mage::helper('slideshowmanager')->__('Align: left, right or center(left pos needs to be set to 50%)'),
            'title'     => Mage::helper('slideshowmanager')->__('Align: left, right or center(left pos needs to be set to 50%)'),
            'required'  => true,
            'values'  => Mage::helper('slideshowmanager')->getAlign(),
            'after_element_html' => '<br /><small>To center the block horizontally the left pos needs to be set to 50%</small>'
        ));

        $fieldset->addField('backgroundimage', 'image', array(
            'name'      => 'backgroundimage',
            'label'     => Mage::helper('slideshowmanager')->__('Background image'),
            'title'     => Mage::helper('slideshowmanager')->__('Background image'),
            'required'  => true,
        ));

        $fieldset->addField('backgroundimage_tablet', 'image', array(
                'name'      => 'backgroundimage_tablet',
                'label'     => Mage::helper('slideshowmanager')->__('Background image (tablet)'),
                'title'     => Mage::helper('slideshowmanager')->__('Background image (tablet)'),
                'required'  => false,
        ));

        $fieldset->addField('backgroundimage_mobile', 'image', array(
                'name'      => 'backgroundimage_mobile',
                'label'     => Mage::helper('slideshowmanager')->__('Background image (mobile)'),
                'title'     => Mage::helper('slideshowmanager')->__('Background image (mobile)'),
                'required'  => false,
        ));

        $fieldset->addField('status', 'select', array(
            'name'      => 'status',
            'label'     => Mage::helper('slideshowmanager')->__('Status'),
            'title'     => Mage::helper('slideshowmanager')->__('Status'),
            'required'  => true,
            'values'  => Mage::helper('slideshowmanager')->getStatuses()
        ));

        $fieldset->addField('position', 'text', array(
            'name'      => 'position',
            'label'     => Mage::helper('slideshowmanager')->__('Position'),
            'title'     => Mage::helper('slideshowmanager')->__('Position'),
            'required'  => false,
            'value'		=> 0
        ));

        $form->setAction($this->getUrl('*/*/savelayeredhtml'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
