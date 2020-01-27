<?php
/**
 * Class Mediotype_OffersTab_Block_Adminhtml_Promo_Offerstab
 *
 * @author Myles Forrest <myles@mediotype.com>
 */

class Mediotype_OffersTab_Block_Adminhtml_Offerstab_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _getStaticBlockOptions()
    {
        return Mage::getModel('mediotype_offerstab/attribute_source_staticBlock');
    }

	/**
	 * @return Mage_Adminhtml_Block_Widget_Form
	 */
	protected function _prepareForm()
	{
        $id = $this->getRequest()->getParam('id');
        $staticBlockOptions = $this->_getStaticBlockOptions()->getAllOptions();
        $staticBlockOptionsSelect = array();

        foreach ($staticBlockOptions as $option) {
            foreach ($option as $value => $label) {
                $staticBlockOptionsSelect[$option['value']] = $option['label'];
            }
        }

        $model = Mage::getModel('mediotype_offerstab/offer')->load($id);
        $form = new Varien_Data_Form([
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', ['id' => $id]),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
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
//Remove select field from offers form
//		$fieldset->addField('static_block_id', 'select', array(
//			'name' => 'static_block_id',
//			'label' => $helper->__('Static Block ID'),
//			'value' => '',
//            'options' => $staticBlockOptionsSelect
//		));

		$fieldset->addField('customer_group_ids', 'multiselect', array(
			'name' => 'customer_group_ids',
			'label' => $helper->__('Customer Groups'),
			'values' => Mage::getResourceModel('customer/group_collection')->toOptionArray(),
		));

		$fieldset->addField('category_ids', 'text', array(
			'name' => 'category_ids',
			'label' => $helper->__('Category IDs'),
			'value' => ''
		));

		$fieldset->addField('product_ids', 'text', array(
			'name' => 'product_ids',
			'label' => $helper->__('Product IDs'),
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

        $fieldset->addField('redemption_message', 'text', array(
            'name' => 'redemption_message',
            'label' => $helper->__('Redemption Message'),
            'value' => ''
        ));

        $fieldset->addField('description', 'textarea', array(
            'name' => 'description',
            'label' => $helper->__('Description'),
            'value' => ''
        ));

        $fieldset->addField('image', 'image', array(

            'label'     => $helper->__('Upload Image'),
            'required'  => false,
            'name'      => 'image',

        ));

		$fieldset->addField('status', 'select', array(
			'name' => 'status',
			'label' => $helper->__('Status'),
			'value' => '',
			'class' => 'required-entry',
			'required' => true,
            'options' => array(
                '1' => Mage::helper('catalogrule')->__('Active'),
                '0' => Mage::helper('catalogrule')->__('Inactive'),
            ),
		));

        $fieldset->addField('feed_status', 'select', array(
            'name' => 'feed_status',
            'label' => $helper->__('Add To Feed'),
            'value' => '',
            'class' => 'required-entry',
            'required' => true,
            'options' => array(
                '1' => Mage::helper('catalogrule')->__('Yes'),
                '0' => Mage::helper('catalogrule')->__('No'),
            ),
        ));

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
		return parent::_prepareForm();
	}
}
