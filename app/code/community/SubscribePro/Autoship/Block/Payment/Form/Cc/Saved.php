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

use \SubscribePro\Service\PaymentProfile\PaymentProfileInterface;

class SubscribePro_Autoship_Block_Payment_Form_Cc_Saved extends SubscribePro_Autoship_Block_Payment_Form_Cc
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('autoship/payment/form/cc_saved.phtml');
    }

    /**
     * @return PaymentProfileInterface
     */
    public function getSavedPaymentProfile()
    {
        /** @var SubscribePro_Autoship_Model_Payment_Method_Cc $method */
        $method = $this->getMethod();
        $paymentProfile = $method->getSavedPaymentProfile();

        return $paymentProfile;
    }

    public function getObscuredCardNumber()
    {
        $paymentProfile = $this->getSavedPaymentProfile();

        return $paymentProfile->getCreditcardFirstDigits() . 'XXXXXX' . $paymentProfile->getCreditcardLastDigits();
    }

    public function getMagentoCardType()
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');
        // Get payment profile
        $paymentProfile = $this->getSavedPaymentProfile();

        return $vaultHelper->mapSubscribeProCardTypeToMagento($paymentProfile->getCreditcardType(), false);
    }

    /**
     * Should require CVV?
     *
     * @return boolean
     */
    public function requireCvv()
    {
        if ($this->getSavedPaymentProfile()->getPaymentMethodType() == PaymentProfileInterface::TYPE_THIRD_PARTY_TOKEN) {
            return false;
        }
        if ($this->getMethod()) {
            $configData = $this->getMethod()->getConfigData('useccv', $this->getQuote()->getStoreId());
            if (is_null($configData)) {
                return true;
            }

            return (bool)$configData;
        }

        return true;
    }

}
