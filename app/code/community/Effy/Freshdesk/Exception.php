<?php
/**
 * Effy Freshdesk extension
 *
 * @category    Effy_Freshdesk
 * @package     Effy_Freshdesk
 * @copyright   Copyright (c) 2013 Effy. (http://www.effy.com)
 * @license     http://www.effy.com/disclaimer.html
 */

class Effy_Freshdesk_Exception extends Mage_Core_Exception
{
    const ERROR_FIELDS = 1;

    public function isWrongConfig()
    {
        switch($this->getCode()) {
            case self::ERROR_FIELDS:
                return true;
        }

        return false;
    }
}