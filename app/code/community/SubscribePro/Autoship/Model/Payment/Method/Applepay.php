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
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */

class SubscribePro_Autoship_Model_Payment_Method_Applepay extends SubscribePro_Autoship_Model_Payment_Method_Cc
{

    const METHOD_CODE = 'subscribe_pro_applepay';

    /**
     * Payment method code
     */
    protected $_code = self::METHOD_CODE;

    // Don't show the form at checkout
    // (Although customer CAN use it at checkout via Apple Pay button)
    protected $_canUseCheckout = false;
    // Don't show in admin checkout
    protected $_canUseInternal = false;

}
