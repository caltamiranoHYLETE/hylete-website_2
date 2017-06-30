<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Quote payment information
 */
class Icommerce_QuickCheckout_Model_Quote_Payment extends Mage_Sales_Model_Quote_Payment
{
    /**
     * Import data
     *
     * @param array $data
     * @throws Mage_Core_Exception
     * @return Mage_Sales_Model_Quote_Payment
     */
    public function importData(array $data)
    {
        $data = new Varien_Object($data);
        Mage::dispatchEvent(
            $this->_eventPrefix . '_import_data_before',
            array(
                $this->_eventObject=>$this,
                'input'=>$data,
            )
        );

        $this->setMethod($data->getMethod());
        $method = $this->getMethodInstance();

        /**
         * Payment avalability related with quote totals.
         * We have recollect quote totals before checking
         */
    	if (Icommerce_Default::getMagentoVersion()>=1400) {
	        $this->getQuote()->collectTotals();
        }

        if (!$method->isAvailable($this->getQuote())) {
            Mage::throwException(Mage::helper('sales')->__('Requested Payment Method is not available'));
        }

        $method->assignData($data);
        /*
        * validating the payment data
        */
        //$method->validate();
        return $this;
    }
}