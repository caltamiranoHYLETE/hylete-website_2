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

class SubscribePro_Autoship_Block_Payment_Form_Echeck_Saved extends SubscribePro_Autoship_Block_Payment_Form_Echeck
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('autoship/payment/form/echeck_saved.phtml');
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

}
