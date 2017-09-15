<?php

/**
 * Sweet Tooth
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Sweet Tooth SWEET TOOTH POINTS AND REWARDS
 * License, which extends the Open Software License (OSL 3.0).
 *
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
 * @category   TBT
 * @package    TBT_Rewards
 * * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_Rewards_Model_Birthday_Action extends TBT_Rewards_Model_Special_Configabstract
{
    const ACTION_CODE = 'customer_birthday';

    public function _construct()
    {
        $this->setCaption($this->_getHelper()->__('Customer birthday occurs'));
        $this->setDescription($this->_getHelper()->__('Customer will get points on a birthday.'));
        $this->setCode(self::ACTION_CODE);

        return parent::_construct();
    }

    public function getNewCustomerConditions()
    {
        return array(
            self::ACTION_CODE => $this->_getHelper()->__('Customer birthday occurs'),
        );
    }

    public function visitAdminConditions(&$fieldset)
    {
        return $this;
    }

    public function visitAdminActions(&$fieldset)
    {
        return $this;
    }

    public function getNewActions()
    {
        return array();
    }

    public function getAdminFormScripts()
    {
        return array();
    }

    /**
     * (non-PHPdoc)
     * @see TBT_Rewards_Model_Special_Configabstract::getAdminFormInitScripts()
     */
    public function getAdminFormInitScripts()
    {
        return array();
    }

    /**
     *
     * @return TBT_Rewards_Helper_Birthday
     */
    protected function _getHelper()
    {
        return Mage::helper('rewards/birthday');
    }

}
