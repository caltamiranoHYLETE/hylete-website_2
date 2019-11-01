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
 * Class Effy_Freshdesk_Block_Customer_Ticket_Create
 *
 * @method Effy_Freshdesk_Model_Resource_Field_Collection getFields
 * @method Effy_Freshdesk_Model_User getUser
 * @method Effy_Freshdesk_Block_Customer_Ticket_Create setFields
 * @method Effy_Freshdesk_Block_Customer_Ticket_Create setUser
 */
class Effy_Freshdesk_Block_Customer_Ticket_Create extends Effy_Freshdesk_Block_Customer_Ticket_Abstract
{
    static $HIDE_FIELDS = array(
        Effy_Freshdesk_Model_Field::FIELD_REQUESTER,
        Effy_Freshdesk_Model_Field::FIELD_STATUS
    );

    public function __construct()
    {
        parent::__construct();

        if (Mage::helper('freshdesk')->isTicketTabEnabled()) {
            $fields = Mage::getModel('effy_freshdesk/field')
                ->getCollection()
                ->load();

            if (!$fields->getSize()) {
                throw new Effy_Freshdesk_Exception($this->helper('freshdesk')->__('Wrong ticket\'s fields object'));
            }

            $this->setFields($fields);
            $this->setUser(Mage::helper('freshdesk')->getCurrentUser());
        }
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->getTitle());
        }

        return $this;
    }

    public function getTitle()
    {
        return $this->__('New Ticket');
    }

    /**
     * @param Effy_Freshdesk_Model_Field $field
     *
     * @return bool|mixed
     */
    public function isFieldVisible($field)
    {
        if (in_array($field->getName(), self::$HIDE_FIELDS)) {
            return false;
        } elseif (!$field->isVisible()) {
            return false;
        } elseif (!$field->isEditable()) {
            return false;
        }

        return true;
    }

    public function getFieldHtml($field)
    {
        $block = $this->getLayout()->createBlock(
            'freshdesk/field_row',
            'freshdesk_field_row',
            array(
                'field'    => $field,
                'template' => 'freshdesk/field/row.phtml'
            )
        );

        if ($block instanceof Effy_Freshdesk_Block_Field_Row) {
            return $block->toHtml();
        } else {
            return '';
        }
    }

    public function getCancelUrl()
    {
        return Mage::getUrl('*/*/list');
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current' => true));
    }

    public function getSaveNewUrl()
    {
        return $this->getUrl('*/*/save', array('_current' => true, 'back' => 'new'));
    }

    public function getSaveCloseUrl()
    {
        return $this->getUrl('*/*/save', array('_current' => true, 'back' => 'close'));
    }

    protected function _toHtml()
    {
        if (Mage::helper('freshdesk')->isTicketTabEnabled() && $this->getFields()->getSize() > 0) {
            return parent::_toHtml();
        }

        return '';
    }
}
