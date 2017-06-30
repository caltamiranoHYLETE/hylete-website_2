<?php

class Ebizmarts_BakerlooRestful_Helper_Email extends Mage_Core_Helper_Abstract {

    const XML_PATH_RECEIPT_EMAIL_IDENTITY  = 'bakerloorestful/pos_receipt/identity';
    const XML_PATH_RECEIPT_EMAIL_TEMPLATE  = 'bakerloorestful/pos_receipt/template';

    const XML_PATH_COUPON_EMAIL_IDENTITY  = 'bakerloorestful/pos_coupon/identity';
    const XML_PATH_COUPON_EMAIL_TEMPLATE  = 'bakerloorestful/pos_coupon/template';

    const XML_PATH_WELCOME_EMAIL_IDENTITY               = 'bakerloorestful/new_customer_account/identity';
    const XML_PATH_WELCOME_EMAIL_TEMPLATE               = 'bakerloorestful/new_customer_account/template';
    const XML_PATH_WELCOME_CONFIRMATION_EMAIL_TEMPLATE  = 'bakerloorestful/new_customer_account/confirmation_template';

    private $_emailSent = false;

    public function setEmailSent($bool) {
        $this->_emailSent = $bool;
    }

    public function getEmailSent() {
        return $this->_emailSent;
    }

    /**
     * Send coupon code to a given email address.
     *
     * @param $email
     * @param $coupon
     * @return Varien_Object
     */
    public function sendCoupon($email, $coupon, $storeId) {

        $result = new Varien_Object;

        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($email, '');

        $emailTemplate = Mage::getModel('core/email_template');

        $emailTemplate->getMail()->createAttachment(base64_decode($coupon->content), $coupon->type, Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $coupon->name);

        $emailTemplate
            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
            ->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_COUPON_EMAIL_TEMPLATE, $storeId),
                Mage::getStoreConfig(self::XML_PATH_COUPON_EMAIL_IDENTITY, $storeId),
                $emailInfo->getToEmails(),
                $emailInfo->getToNames(),
                array(),
                $storeId
            );

        $result->setEmailSent(true);

        return $result;
    }

    /**
     * Send receipt to customer.
     *
     * @param $order
     * @param $receipt
     * @return Ebizmarts_BakerlooRestful_Helper_Email
     */
    public function sendReceipt($order, $receipt) {
        $storeId = $order->getStoreId();

        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($order->getCustomerEmail(), $order->getCustomerName());

        $emailTemplate = Mage::getModel('core/email_template');

        $emailTemplate->getMail()->createAttachment(base64_decode($receipt->content), $receipt->type, Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $receipt->name);

        $emailTemplate
            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
            ->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_RECEIPT_EMAIL_TEMPLATE, $storeId),
                Mage::getStoreConfig(self::XML_PATH_RECEIPT_EMAIL_IDENTITY, $storeId),
                $emailInfo->getToEmails(),
                $emailInfo->getToNames(),
                array('order' => $order),
                $storeId
            );

        $this->setEmailSent(true);

        return $this;
    }

    /**
     * Send new account email to customer if enabled in config.
     *
     * @param type $customer
     * @return type
     */
    public function sendWelcome($customer, $storeId = null) {

        $shouldSendEmail = (int)Mage::helper('bakerloo_restful')->config('new_customer_account/send_welcome_email');
        if($shouldSendEmail !== 1 or !$customer->getId())
            return;

        $template = $customer->isConfirmationRequired() ? self::XML_PATH_WELCOME_CONFIRMATION_EMAIL_TEMPLATE : self::XML_PATH_WELCOME_EMAIL_TEMPLATE;

        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($customer->getEmail(), $customer->getName());

        $emailTemplate = Mage::getModel('core/email_template');

        $emailTemplate
            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
            ->sendTransactional(
                Mage::getStoreConfig($template, $storeId),
                Mage::getStoreConfig(self::XML_PATH_WELCOME_EMAIL_IDENTITY, $storeId),
                $emailInfo->getToEmails(),
                $emailInfo->getToNames(),
                array('customer' => $customer),
                $storeId
            );

        $this->setEmailSent(true);

        return $this;

    }

}