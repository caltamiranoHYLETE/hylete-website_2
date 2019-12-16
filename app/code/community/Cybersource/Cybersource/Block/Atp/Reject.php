<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Block_Atp_Reject extends Mage_Core_Block_Template
{
    /**
     * Retrieves the message title for the rejection page
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_getHelper()->getRejectionTitle();
    }

    /**
     * Retrieves the message for the rejection page
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_getHelper()->getRejectionMessage();
    }

    /**
     * Retrieves the module's helper
     *
     * @return Cybersource_Cybersource_Helper_Atp_Data
     */
    private function _getHelper()
    {
        return Mage::helper(Cybersource_Cybersource_Helper_Atp_Data::GROUPNAME);
    }

}
