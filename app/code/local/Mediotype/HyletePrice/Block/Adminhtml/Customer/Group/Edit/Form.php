<?php

/**
 * Class Form
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_HyletePrice_Block_Adminhtml_Customer_Group_Edit_Form extends Mage_Adminhtml_Block_Customer_Group_Edit_Form
{
	/**
	 *
	 */
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		$form = $this->getForm();

		$fs = $form->getElement('base_fieldset');

		$fs->addField('customer_group_hylete_price_label', 'text',
			array(
				'name' => 'customer_group_hylete_price_label',
				'label' => 'Hylete price label',
				'title' => 'Hylete price label',
				'class' => '',
				'note' => 'Max 32 characters',
				'required' => false
			)
		);

		$customerGroup = Mage::registry('current_group');
		if (Mage::getSingleton('adminhtml/session')->getCustomerGroupData()) {
			$form->addValues(Mage::getSingleton('adminhtml/session')->getCustomerGroupData());
			Mage::getSingleton('adminhtml/session')->setCustomerGroupData(null);
		} else {
			$form->addValues($customerGroup->getData());
		}
	}
}
