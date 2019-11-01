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
 * Class Effy_Freshdesk_Model_Abstract
 */
class Effy_Freshdesk_Model_Abstract extends Mage_Core_Model_Abstract
{
    public function getFreshdeskModel()
    {
        return $this->getResource()->getFreshdeskModel();
    }

    public function save()
    {
        try {
            $this->_beforeSave();
            if ($this->_dataSaveAllowed) {
                $this->_getResource()->save($this);
                $this->_afterSave();
            }
            $this->_hasDataChanges = false;
        } catch (Exception $e) {
            $this->_hasDataChanges = true;
            Mage::logException($e);
            throw $e;
        }

        return $this;
    }
}