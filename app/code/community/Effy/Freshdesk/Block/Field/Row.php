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
 * Class Effy_Freshdesk_Block_Field_Row
 *
 * @method Effy_Freshdesk_Model_Field getField
 * @method Effy_Freshdesk_Block_Field_Abstract setField
 */
class Effy_Freshdesk_Block_Field_Row extends Effy_Freshdesk_Block_Field_Abstract
{
    const CHILD_BLOCK_NAME_INPUT_FIELD = 'edit_input_field';

    public function _construct()
    {
        $this->setTemplate('freshdesk/field/row_2_column.phtml');

        parent::_construct();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $blockType = $this->getField()->getFieldBlockType($this->getField()->getType());
        $block     = $this->getLayout()->createBlock($blockType);        
        if (!$block instanceof Effy_Freshdesk_Block_Field_Abstract) {
            return;
            throw new Effy_Freshdesk_Exception(Mage::helper('freshdesk')->__('Invalid block "%s"', $blockType));
        }

        $block->setField($this->getField());

        if ($block instanceof Effy_Freshdesk_Block_Field_Abstract) {
            if($rowTemplate = $block->getFieldRowTemplate()) {
                $this->setTemplate($rowTemplate);
            }
            $this->setChild(self::CHILD_BLOCK_NAME_INPUT_FIELD, $block);
        }
    }

    public function getFieldName()
    {
        if($this->_getData('field_name') === null) {
            if(!is_object($this->getChild(self::CHILD_BLOCK_NAME_INPUT_FIELD)) || !($fieldName = $this->getChild(self::CHILD_BLOCK_NAME_INPUT_FIELD)->getFieldName())) {
                $fieldName = parent::getFieldName();
            }
            $this->setData('field_name', $fieldName);
        }

        return $this->_getData('field_name');
    }

    public function getFieldId()
    {
        if($this->_getData('field_id') === null) {
            if(!is_object($this->getChild(self::CHILD_BLOCK_NAME_INPUT_FIELD)) || !($fieldId = $this->getChild(self::CHILD_BLOCK_NAME_INPUT_FIELD)->getFieldId())) {
                $fieldId = parent::getFieldId();
            }
            $this->setData('field_id', $fieldId);
        }

        return $this->_getData('field_id');
    }


    public function getInputFieldHtml()
    {
        return $this->getChildHtml(self::CHILD_BLOCK_NAME_INPUT_FIELD);
    }
}