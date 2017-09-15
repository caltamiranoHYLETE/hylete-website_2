<?php

/**
 * Sweet Tooth
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the SWEET TOOTH POINTS AND REWARDS
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL:
 * https://www.sweettoothrewards.com/terms-of-service
 * The Open Software License is available at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * By adding to, editing, or in any way modifying this code, Sweet Tooth is
 * not held liable for any inconsistencies or abnormalities in the
 * behaviour of this code.
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by Sweet Tooth, outlined in the
 * provided Sweet Tooth License.
 * Upon discovery of modified code in the process of support, the Licensee
 * is still held accountable for any and all billable time Sweet Tooth spent
 * during the support process.
 * Sweet Tooth does not guarantee compatibility with any other framework extension.
 * Sweet Tooth is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 */

/**
 * Diagnostics Observer
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @copyright  Copyright (c) 2016 Sweet Tooth Inc. (http://www.sweettoothrewards.com)
 * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_Rewards_Model_Observer_Diagnostics
{
    const CORE_RESOURCE_CODE = 'rewards_setup';

    /**
     * Dispatched when admin clicks 'Re-Install DB' and will delete 
     * the 'core_resource' entry for this module
     * 
     * @param  Varien_Event_Observer $observer
     * @event controller_action_predispatch_adminhtml_manage_diagnostics_reinstalldb
     * @return $this
     */
    public function reinstallDb(Varien_Event_Observer $observer)
    {
        $code = static::CORE_RESOURCE_CODE;
        Mage::helper('rewards/debug')->printMessage("<br />Deleting core_resource table entry with code '{$code}'...");
        
        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $conn->beginTransaction();
        
        $table_prefix = Mage::getConfig()->getTablePrefix();
        $conn->query("DELETE FROM `{$table_prefix}core_resource` WHERE `code` = '{$code}';");
        Mage::helper('rewards/debug')->printMessage("Done<br />");

        $conn->commit();
        return $this;
    }
}

