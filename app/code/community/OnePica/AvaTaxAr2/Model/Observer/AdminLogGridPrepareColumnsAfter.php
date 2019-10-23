<?php
/**
 * OnePica_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 * @copyright  Copyright (c) 2015 One Pica, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Avatax Observer AdminLogGridPrepareColumnsAfter
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 */
class OnePica_AvaTaxAr2_Model_Observer_AdminLogGridPrepareColumnsAfter extends OnePica_AvaTax_Model_Observer_Abstract
{
    /**
     *  Iterprate Logs
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function execute(Varien_Event_Observer $observer)
    {
        try {
            /** @var Mage_Adminhtml_Block_Widget_Grid $grid */
            $grid = $observer->getGrid();
            $grid->addColumnAfter('request_method', array(
                'header'    => $this->_getHelper()->__('Method'),
                'index'     => 'request_method',
                'filter'    => false,
                'sortable'  => false
            ),'type');

            $grid->sortColumnsByOrder();

        } catch (Exception $ex) {
            $this->_getCoreSession()->addError($ex->getMessage());
        }

        return $this;
    }

    /**
     * @return \OnePica_AvaTaxAr2_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('avataxar2');
    }

    /**
     * Get Core Session
     *
     * @return \Mage_Core_Model_Session
     */
    protected function _getCoreSession()
    {
        return Mage::getSingleton('core/session');
    }
}
