<?php

/**
 * Class Mediotype_OffersTab_Block_Adminhtml_Form_Edit_Form
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Block_Adminhtml_Form_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	/**
	 * @return Mage_Adminhtml_Block_Widget_Form
	 */
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(
			array(
				'id' => 'edit_form',
				'action' => $this->getUrl('*/*/save'),
				'method' => 'post',
			)
		);

		$form->setUseContainer(true);
		$this->setForm($form);

		$helper = Mage::helper('mediotype_offerstab');
		$fieldset = $form->addFieldset('display', array(
			'legend' => $helper->__('Offer Settings'),
			'class' => 'fieldset-wide'
		));

		$fieldset->addField('title', 'text', array(
			'name' => 'title',
			'label' => $helper->__('Title'),
		));

		if (Mage::registry('mediotype_offerstab')) {
			$form->setValues(Mage::registry('mediotype_offerstab')->getData());
		}

		return parent::_prepareForm();
	}
}
