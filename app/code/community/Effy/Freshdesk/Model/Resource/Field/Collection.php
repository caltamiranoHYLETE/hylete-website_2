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
 * Class Effy_Freshdesk_Model_Resource_Field_Collection
 *
 * @method Effy_Freshdesk_Model_Field getNewEmptyItem
 */
class Effy_Freshdesk_Model_Resource_Field_Collection extends Effy_Freshdesk_Model_Resource_Collection_Abstract
{
    protected $_fieldsByNames;

    public function __construct()
    {
        parent::__construct();
        $this->setItemObjectClass('effy_freshdesk/field');
        $this->loadFreshdeskData();
        $this->getItemsByNames();
    }

    public function createItem($data)
    {
        $item = Mage::getModel('effy_freshdesk/field')->getFieldModel($data);//$this->getNewEmptyItem()->getFieldModel($data);
        $this->addItem($item);
    }

    public function getItemsByNames()
    {        
        if(!is_array($this->_fieldsByNames)) {
            $fields = $this->getItems();
            foreach($fields as $field) {
                /** @var Effy_Freshdesk_Model_Field $field */
                $this->_fieldsByNames[$field->getName()] = $field;
            }
        }

        return $this->_fieldsByNames;
    }

    protected function loadFreshdeskData()
    {
        return $this->loadFields();
    }

    protected function loadFields()
    {
        if (!is_array($this->_freshdeskData)) {
            $this->_freshdeskData = //$this->getNewEmptyItem()
                Mage::getSingleTon('effy_freshdesk/freshdesk_fields')
                // Mage::getModel('effy_freshdesk/field')
                ->getFields();
            if (!is_array($this->_freshdeskData)) {
                $this->_freshdeskData = array();
            }
        }

        return $this->_freshdeskData;
    }
}