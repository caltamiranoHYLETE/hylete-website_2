<?php
/**
 * Sweet Tooth
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Sweet Tooth SWEET TOOTH POINTS AND REWARDS
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL:
 *      https://www.sweettoothrewards.com/terms-of-service
 * The Open Software License is available at this URL:
 *      http://opensource.org/licenses/osl-3.0.php
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
 * If you did not receive a copy of the license, please send an email to
 * support@sweettoothrewards.com or call 1.855.699.9322, so we can send you a copy
 * immediately.
 *
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2014 Sweet Tooth Inc. (http://www.sweettoothrewards.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Diagnostics module
 *
 * @category   TBT
 * @package    TBT_RewardsReferral
 * * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_RewardsPlat_Model_Diagnostics_Database extends Mage_Core_Model_Abstract
{

    /**
     * This controller action will remove the database install entry from the Magento
     * core_resource table. This in turn will force Magento to re-install the database scripts.
     */
    public function reinstallDb()
    {
        $helper = Mage::helper('rewards/debug');
        
        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $conn->beginTransaction();
        $this->_clearDbInstallMemory($conn, 'rewardsplat_setup');
        $this->_clearDbInstallMemory($conn, 'rewardsapi_setup');
        $this->_clearDbInstallMemory($conn, 'rewardsonly_setup');

        $helper->printMessage("<br>...Done<br>");
        $conn->commit();
    }

    public function _clearDbInstallMemory($conn, $code)
    {
        $table_prefix = Mage::getConfig()->getTablePrefix();
        $conn->query("DELETE FROM `{$table_prefix}core_resource` WHERE `code` = '{$code}';");
        Mage::helper('rewards/debug')->printMessage("<br>Resource DB for {$code} has been cleared");

        return $this;
    }
}

