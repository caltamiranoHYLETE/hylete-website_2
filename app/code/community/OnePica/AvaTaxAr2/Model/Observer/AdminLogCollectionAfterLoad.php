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
 * Avatax Observer AdminLogCollectionAfterLoad
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 */
class OnePica_AvaTaxAr2_Model_Observer_AdminLogCollectionAfterLoad extends OnePica_AvaTax_Model_Observer_Abstract
{
    /**
     *  Add Method Column
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function execute(Varien_Event_Observer $observer)
    {
        try {
            $logTypes = Mage::getModel('avataxar2/source_avatax_logtype')->getAr2LogTypes();
            $collection = $observer->getCollection();
            foreach ($collection as $log) {
                if (in_array($log->getType(), $logTypes)) {
                    $rowRequest = $log->getSoapRequest();
                    $matches = array();
                    preg_match('/^(GET |PUT |DELETE |POST )/', $rowRequest, $matches);
                    if (empty($matches) === false) {
                        $log->setRequestMethod($matches[0]);
                    }
                }
            }
        } catch (Exception $ex) {
            $this->_getCoreSession()->addError($ex->getMessage());
        }

        return $this;
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
