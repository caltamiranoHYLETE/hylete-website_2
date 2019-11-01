<?php
/**
 * Effy Freshdesk extension
 *
 * @category    Effy_Freshdesk
 * @package     Effy_Freshdesk
 * @copyright   Copyright (c) 2014 Effy. (http://www.effy.com)
 * @license     http://www.effy.com/disclaimer.html
 */

/**
 * Class Effy_Freshdesk_Block_Adminhtml_Ticket_Edit_Form_Fields
 */
class Effy_Freshdesk_Block_Adminhtml_Ticket_Edit_Form_Fields extends Mage_Core_Block_Template
{
    protected $_helper;

    public function __construct()
    {
        parent::__construct();

        $this->_helper = $this->helper('freshdesk');

        $this->setTemplate('freshdesk/ticket/edit/form/fields.phtml');
    }

    /**
     * @return Effy_Freshdesk_Model_Resource_Field_Collection
     */
    public function getFields()
    {
        if ($this->_getData('fields') === null) {
            $this->setData('fields', Mage::getResourceModel('effy_freshdesk/field_collection'));

        }
        return $this->_getData('fields');
    }

    /**
     * @param Effy_Freshdesk_Model_Field $field
     *
     * @return string
     */
    public function getFieldHtml($field)
    {        
        if ($field != null && $field != '') {
            $block = $this->getLayout()->createBlock(
            'effy_freshdesk/field_row',
            'effy_freshdesk_field_row',
            array('field' => $field)
            );
            if ($block instanceof Effy_Freshdesk_Block_Field_Row) {
                return $block->toHtml();
            } else {
                return '';   
            }
        }        
    }

    public function _helper()
    {
        return $this->_helper;
    }

    protected function _toHtml()
    {
        return parent::_toHtml();
    }
}
