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
class Icommerce_PageManager_Block_Page_Row_Item_Edithtml_Form extends Mage_Adminhtml_Block_Widget_Form
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
        return array('value'=>'html', 'label'=>Mage::helper('pagemanager')->__('HTML'));
    }

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
            'id'        => 'edithtml_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('pagemanager')->__('Enter the information below'),
            'class'     => 'fieldset-wide'
        ));

		$itemType = $this->getItemType();
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

        $fieldset->addField('page_content', 'editor', array(
            'name'      => 'page_content',
            'label'     => Mage::helper('pagemanager')->__('Content'),
            'title'     => Mage::helper('pagemanager')->__('Content'),
            'style'     => 'height:36em',
            'required'  => true,
	    	'wysiwyg' => true,
	    	'value'		=> $item['page_content'],
            'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig()
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


        $form->setAction($this->getUrl('*/*/savehtml'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
