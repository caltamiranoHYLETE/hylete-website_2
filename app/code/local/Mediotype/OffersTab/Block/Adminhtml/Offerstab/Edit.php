<?php

/**
 * Class Mediotype_OffersTab_Block_Adminhtml_Form_Edit_Form
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Block_Adminhtml_Offerstab_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

		$this->_blockGroup = 'mediotype_offerstab';
		$this->_objectId = 'id';
		$this->_controller = 'adminhtml_offerstab';

		$this->_addButton('save_and_continue_edit', [
			'class' => 'save',
			'label' => Mage::helper('mediotype_offerstab')->__('Save and Continue Edit'),
            'onclick' => 'editForm.submit($(\'edit_form\').action + \'back/edit/\')',
		], 10);

		$this->_updateButton('save', 'label', Mage::helper('adminhtml')->__('Save'));
		$this->_updateButton('delete', 'label', Mage::helper('adminhtml')->__('Delete'));
	}
}
