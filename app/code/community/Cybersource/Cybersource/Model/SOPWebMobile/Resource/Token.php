<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Resource_Token extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Main constructor
     */
    protected function _construct()
    {
        // Specify the DB table name and primary key
        $this->_init('cybersourcesop/token', 'id');
    }

    /**
     * Will run before the token is saved
     * @param Mage_Core_Model_Abstract $object
     * @return mixed
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $time = Varien_Date::now();
        $object->setModifiedAt($time);

        if ($object->isObjectNew()) {
            $object->setCreatedAt($time);
        }
        return parent::_beforeSave($object);
    }
}
