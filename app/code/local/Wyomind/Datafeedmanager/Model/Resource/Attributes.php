<?php
/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
class Wyomind_Datafeedmanager_Model_Resource_Attributes extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        // Note that the Datafeedmanager_id refers to the key field in your database table.
        $this->_init('datafeedmanager/attributes', 'attribute_id');
    }
}
