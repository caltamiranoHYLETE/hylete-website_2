<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Resource_Token_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Main constructor
     */
    protected function _construct()
    {
        // Specify the model and resource model
        $this->_init('cybersourcesop/token');
    }

    /**
     * Filters tokens by customer id.
     * @param string $customer_id
     * @return $this
     */
    public function addCustomerFilter($customer_id)
    {
        $this->addFieldToFilter('customer_id', $customer_id);
        return $this;
    }

    /**
     * @return $this
     */
    public function addDefaultFilter()
    {
        $this->addFieldToFilter('is_default', 1);
        return $this;
    }
}
