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

class SubscribePro_Autoship_Block_Mysubscriptions_Subscription_Form_Payment extends SubscribePro_Autoship_Block_Mysubscriptions_Subscription
{

    /**
     * @return \SubscribePro\Service\PaymentProfile\PaymentProfileInterface
     */
    public function getPaymentProfile()
    {
        return $this->getSubscription()->getPaymentProfile();
    }

    public function useVerification()
    {
        return false;
    }

}
