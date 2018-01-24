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
			'name' => 'offer[title]',
			'label' => $helper->__('Title'),
			'value' => $this->getOffer()->getTitle()
		));

		$fieldset->addField('status', 'text', array(
			'name' => 'offer[status]',
			'label' => $helper->__('Status'),
			'value' => $this->getOffer()->getStatus()
		));

		$fieldset->addField('offer_id', 'hidden', array(
			'name' => 'offer[offer_id]',
			'value' => $this->getOffer()->getOfferId()
		));

		// Provide a submit button
		$fieldset->addField('submit', 'submit', array(
			'label' => '',
			'value' => 'Save'
		));

		// Provide a delete button (currently just submits form)
		if (!empty($this->getOffer()->getOfferId())) {
			$fieldset->addField('delete', 'submit', array(
				'label' => '',
				'value' => 'Delete'
			));
		}

		return parent::_prepareForm();
	}
}
