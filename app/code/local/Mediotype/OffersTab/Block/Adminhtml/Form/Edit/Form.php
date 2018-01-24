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
		if (!empty($this->getOffer()->getOfferId()) && $this->getOffer()->getOfferId() != "") {
			$form = new Varien_Data_Form(
				array(
					'id' => 'edit_form',
					'action' => $this->getUrl('*/*/save', array('id' => $this->getOffer()->getOfferId())),
					'method' => 'post',
				)
			);

		} else {
			$form = new Varien_Data_Form(
				array(
					'id' => 'edit_form',
					'action' => $this->getUrl('*/*/save'),
					'method' => 'post',
				)
			);
		}

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
			'value' => $this->getOffer()->getTitle(),
			'class' => 'required-entry',
			'required' => true
		));

		$fieldset->addField('static_block_id', 'text', array(
			'name' => 'offer[static_block_id]',
			'label' => $helper->__('Static Block Id'),
			'value' => $this->getOffer()->getStaticBlockId(),
			'class' => 'required-entry',
			'required' => true
		));

		$fieldset->addField('customer_group_ids', 'text', array(
			'name' => 'offer[customer_group_ids]',
			'label' => $helper->__('Customer Group Ids'),
			'value' => $this->getOffer()->getCustomerGroupIds()
		));

		$fieldset->addField('category_ids', 'text', array(
			'name' => 'offer[category_ids]',
			'label' => $helper->__('Category Ids'),
			'value' => $this->getOffer()->getCategoryIds()
		));

		$fieldset->addField('product_ids', 'text', array(
			'name' => 'offer[product_ids]',
			'label' => $helper->__('Product Ids'),
			'value' => $this->getOffer()->getProductIds()
		));

		$fieldset->addField('priority', 'text', array(
			'name' => 'offer[priority]',
			'label' => $helper->__('Priority'),
			'value' => $this->getOffer()->getPriority()
		));

		$fieldset->addField('landing_page_url', 'text', array(
			'name' => 'offer[landing_page_url]',
			'label' => $helper->__('Landing Page URL'),
			'value' => $this->getOffer()->getLandingPageUrl()
		));

		$fieldset->addField('status', 'text', array(
			'name' => 'offer[status]',
			'label' => $helper->__('Status'),
			'value' => $this->getOffer()->getStatus(),
			'class' => 'required-entry',
			'required' => true
		));

		// Provide a submit button
		$fieldset->addField('submit-el', 'submit', array(
			'label' => '',
			'value' => 'Save'
		));

		// Provide a delete button (currently just submits form)
		if (!empty($this->getOffer()->getOfferId())) {
			$field = $fieldset->addField('delete', 'button', array(
				'name' => 'delete',
				'label' => '',
				'value' => 'Delete',
				'onclick' => 'deleteOffer();'
			));

			$deleteUrl = $this->getUrl('*/*/delete', array('id' => $this->getOffer()->getOfferId()));

			$field->setAfterElementHtml('
				<script>
				//< ![CDATA[
				function deleteOffer() {
				    $("edit_form").writeAttribute("action","' . $deleteUrl . '");
				    $("edit_form").submit();
				}
				//]]>
				</script>
			');
		}

		return parent::_prepareForm();
	}
}
