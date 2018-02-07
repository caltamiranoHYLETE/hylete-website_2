<?php
/**
 * Class Mediotype_OffersTab_Block_Adminhtml_Promo_Offerstab
 *
 * @author Myles Forrest <myles@mediotype.com>
 */

class Mediotype_OffersTab_Block_Adminhtml_Offerstab_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	/**
	 * @return Mage_Adminhtml_Block_Widget_Form
	 */
	protected function _prepareForm()
	{
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('mediotype_offerstab/offer')->load($id);
        $form = new Varien_Data_Form([
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', ['id' => $id]),
            'method' => 'post',
        ]);

		$helper = Mage::helper('mediotype_offerstab');
		$fieldset = $form->addFieldset('display', array(
			'legend' => $helper->__('Offer Settings'),
			'class' => 'fieldset-wide'
		));

		$fieldset->addField('title', 'text', array(
			'name' => 'title',
			'label' => $helper->__('Title'),
			'value' => '',
			'class' => 'required-entry',
			'required' => true
		));

		$fieldset->addField('static_block_id', 'text', array(
			'name' => 'static_block_id',
			'label' => $helper->__('Static Block Id'),
			'value' => '',
			'class' => 'required-entry',
			'required' => true
		));

		$fieldset->addField('customer_group_ids', 'text', array(
			'name' => 'customer_group_ids',
			'label' => $helper->__('Customer Group Ids'),
			'value' => ''
		));

		$fieldset->addField('category_ids', 'text', array(
			'name' => 'category_ids',
			'label' => $helper->__('Category Ids'),
			'value' => ''
		));

		$fieldset->addField('product_ids', 'text', array(
			'name' => 'product_ids',
			'label' => $helper->__('Product Ids'),
			'value' => ''
		));

		$fieldset->addField('priority', 'text', array(
			'name' => 'priority',
			'label' => $helper->__('Priority'),
			'value' => ''
		));

		$fieldset->addField('landing_page_url', 'text', array(
			'name' => 'landing_page_url',
			'label' => $helper->__('Landing Page URL'),
			'value' => ''
		));

		$fieldset->addField('status', 'select', array(
			'name' => 'status',
			'label' => $helper->__('Status'),
			'value' => '',
			'class' => 'required-entry',
			'required' => true,
            'options'    => array(
                '1' => Mage::helper('catalogrule')->__('Active'),
                '0' => Mage::helper('catalogrule')->__('Inactive'),
            ),
		));

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
		return parent::_prepareForm();
	}
}
