<?php
/**
 * Subscribe Pro - Subscriptions Management Extension
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of SUBSCRIBE PRO INC.
 *
 * @category  SubscribePro
 * @package   SubscribePro_Autoship
 * @author    Subscribe Pro Support <support@subscribepro.com>
 * @copyright 2009-2020 SUBSCRIBE PRO INC. All Rights Reserved.
 * @license   http://www.subscribepro.com/terms-of-service/ Subscribe Pro Terms of Service
 * @link      http://www.subscribepro.com/
 *
 */

/**
 * Payment info
 */
class SubscribePro_Autoship_Block_Payment_Info_Echeck extends Mage_Payment_Block_Info
{

    /**
     * Prepare payment info
     *
     * @param Varien_Object|array $transport
     * @return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $transport = parent::_prepareSpecificInformation($transport);
        $data = array();
        if ($this->getInfo()->getAdditionalInformation('bank_routing_number')) {
            $data[Mage::helper('payment')->__('Bank Routing Number')] = $this->getInfo()->getAdditionalInformation('bank_routing_number');
        }
        if ($this->getInfo()->getAdditionalInformation('payment_profile_name')) {
            $data[Mage::helper('payment')->__('Bank Account')] = $this->getInfo()->getAdditionalInformation('payment_profile_name');
        }

        return $transport->setData(array_merge($data, $transport->getData()));
    }

}
