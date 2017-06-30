<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Icommerce
 * @package     Icommerce_EmailAttachments
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 */

class Icommerce_EmailAttachments_Model_Order extends Mage_Sales_Model_Order
{

    const XPATH_USE_MAILER_QUEUE = 'sales_email/order/use_mailer_queue';
    const XPATH_WORLD_CUSTOMER_FEATURE = 'sales_email/order/world_customer_feature';

    /**
     * Sending email with order data
     *
     * @return Icommerce_EmailAttachments_Model_Order
     */
    public function sendNewOrderEmail()
    {
        $storeId = $this->getStore()->getId();

        // Vaimo addition
        if (Mage::getStoreConfig(self::XPATH_USE_MAILER_QUEUE, $storeId)
            && method_exists('Mage_Sales_Model_Order', 'queueNewOrderEmail')
        ) {
            return $this->queueNewOrderEmail(true);
        }
        // Vaimo addition

        if (!Mage::helper('sales')->canSendNewOrderEmail($storeId)) {
            return $this;
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);


        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($this->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);


        $mailTemplate = Mage::getModel('core/email_template');
        /* @var $mailTemplate Mage_Core_Model_Email_Template */
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $this->getStore()->getId());
        if ($copyTo && $copyMethod == 'bcc') {
            foreach ($copyTo as $email) {
                $mailTemplate->addBcc($email);
            }
        }

        // Vaimo addition
        list($template, $customerName) = $this->_prepareNewOrderTemplate();
        // Vaimo addition

        $sendTo = array(
            array(
                'email' => $this->getCustomerEmail(),
                'name'  => $customerName
            )
        );
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $sendTo[] = array(
                    'email' => $email,
                    'name'  => null
                );
            }
        }

        // Vaimo addition
        Mage::dispatchEvent(
            'sales_order_send_new_order_email_before_send',
            array('order' => $this, 'email_template' => $mailTemplate)
        );

        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$this->getStore()->getId()))
                ->sendTransactional(
                    $template,
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $this->getStore()->getId()),
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'order'         => $this,
                        'billing'       => $this->getBillingAddress(),
                        'payment_html'  => $paymentBlockHtml,
                    )
                );
        }

        $translate->setTranslateInline(true);

        // Vaimo addition
        Mage::dispatchEvent(
            'sales_order_send_new_order_email_after_send',
            array('order' => $this, 'email_template' => $mailTemplate)
        );

        return $this;
    }

    /**
     * Queue email with new order data
     * This method was introduced in Magento CE 1.9.1.0 / EE 1.14.1.0
     *
     * @param bool $forceMode if true then email will be sent regardless of the fact that it was already sent previously
     *
     * @return Icommerce_EmailAttachments_Model_Order
     * @throws Exception
     */
    public function queueNewOrderEmail($forceMode = false)
    {
        $storeId = $this->getStore()->getId();

        // Vaimo addition
        if (!method_exists('Mage_Sales_Model_Order', 'queueNewOrderEmail')
            || !Mage::getStoreConfig(self::XPATH_USE_MAILER_QUEUE, $storeId)
        ) {
            $this
                ->sendNewOrderEmail()
                ->setEmailSent(true);
            return $this;
        }
        // Vaimo addition

        if (!Mage::helper('sales')->canSendNewOrderEmail($storeId)) {
            return $this;
        }

        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);

        // Start store emulation process
        /** @var $appEmulation Mage_Core_Model_App_Emulation */
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($this->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Vaimo addition
        // Retrieve corresponding email template id and customer name
        list($templateId, $customerName) = $this->_prepareNewOrderTemplate();
        // Vaimo addition


        /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $mailer = Mage::getModel('core/email_template_mailer');
        /** @var $emailInfo Mage_Core_Model_Email_Info */
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($this->getCustomerEmail(), $customerName);
        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }
        $mailer->addEmailInfo($emailInfo);

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
            'order'        => $this,
            'billing'      => $this->getBillingAddress(),
            'payment_html' => $paymentBlockHtml
        ));

        /** @var $emailQueue Mage_Core_Model_Email_Queue */
        $emailQueue = Mage::getModel('core/email_queue');
        $emailQueue->setEntityId($this->getId())
            ->setEntityType(self::ENTITY)
            ->setEventType(self::EMAIL_EVENT_NAME_NEW_ORDER)
            ->setIsForceCheck(!$forceMode);

        // Vaimo addition
        $extraData = new Varien_Object(array(
            'instance'       => $this,
        ));
        $emailQueue->setCustomData($extraData);
        // Vaimo addition

        $mailer->setQueue($emailQueue)->send();

        $this->setEmailSent(true);
        $this->_getResource()->saveAttribute($this, 'email_sent');

        return $this;
    }

    /**
     * Preparing of email template and customer name for order confirmation email
     * depending on customer's country
     *
     * @return array
     */
    protected function _prepareNewOrderTemplate()
    {
        // This code provides with possibility to use 2 additional email templates for foreign customers.
        // Foreign customer is customer who's country ID is different from default country ID of the web store.
        // There is also fallback to default email templates in case if additional template is not set.

        $storeId = $this->getStore()->getId();
        $template = '';
        // 2012-01-20 Peter Lembke
        $billing_country_id = $this->getBillingAddress()->getCountryId();
        $store_country_id = Mage::getStoreConfig('general/country/default');

        if ($billing_country_id != $store_country_id) { // We have a world customer, use world templates
            if ($this->getCustomerIsGuest()) {
                $template = Mage::getStoreConfig('sales_email/order/guest_template_world', $storeId);
                $customerName = $this->getBillingAddress()->getName();
            } else {
                $template = Mage::getStoreConfig('sales_email/order/template_world', $storeId);
                $customerName = $this->getCustomerName();
            }
        }

        // We have a swedish customer, use normal templates OR we have got no email template from above
        if ($billing_country_id == $store_country_id || $template == '') {
            if ($this->getCustomerIsGuest()) {
                $template = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
                $customerName = $this->getBillingAddress()->getName();
            } else {
                $template = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
                $customerName = $this->getCustomerName();
            }
        }
        // 2012-01-20 Peter Lembke

        $templateTransport = new Varien_Object(array('template' => $template));
        Mage::dispatchEvent('icommerce_emailattachments_prepare_order_mail_template_after', array('order' => $this, 'transport' => $templateTransport));
        $template = $templateTransport->getTemplate();

        return array($template, $customerName);
    }
}
