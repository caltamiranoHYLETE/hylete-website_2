<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_AtpController extends Mage_Core_Controller_Front_Action
{
    /**
     * Holds the URL placeholder for when there is a blanket rejection.  To customize the rejection message design override
     * the cybersourceatp/reject.phtml template.  You can customize the basic messaging under System Configuration :
     * Customers / Customer Configuration / Account Transfer Protection
     */
    public function rejectAction()
    {
        $this->loadLayout()->renderLayout();
    }
}
