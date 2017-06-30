<?php


class Icommerce_EmailAttachments_Model_Order_Invoice extends Mage_Sales_Model_Order_Invoice
{

    public function sendEmail($notifyCustomer=true, $comment='')
    {
        if (!Mage::helper('sales')->canSendNewInvoiceEmail($this->getOrder()->getStore()->getId())) {
            return $this;
        }

        $area = Mage::getDesign()->getArea();
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($this->getStoreId(), $area);

        try {

            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);

            $order = $this->getOrder();

            $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
            $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $this->getStoreId());

            if (!$notifyCustomer && !$copyTo) {
                $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                return $this;
            }

            // Start store emulation process
            $initialFrontendEnvironmentInfo = $appEmulation->startEnvironmentEmulation($this->getStoreId());

            try {
                // Retrieve specified view block from appropriate design package (depends on emulated store)
                $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                    ->setIsSecureMode(true);
                $paymentBlock->getMethod()->setStore($this->getStoreId());
                $paymentBlockHtml = $paymentBlock->toHtml();
            } catch (Exception $exception) {
                // Stop store emulation process
                $appEmulation->stopEnvironmentEmulation($initialFrontendEnvironmentInfo);
                throw $exception;
            }

            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialFrontendEnvironmentInfo);

            $mailTemplate = Mage::getModel('core/email_template');

            if ($order->getCustomerIsGuest()) {
                $template = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $order->getStoreId());
                $customerName = $order->getBillingAddress()->getName();
            } else {
                $template = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $order->getStoreId());
                $customerName = $order->getCustomerName();
            }

            if ($notifyCustomer) {
                $sendTo[] = array(
                    'name' => $customerName,
                    'email' => $order->getCustomerEmail()
                );
                if ($copyTo && $copyMethod == 'bcc') {
                    foreach ($copyTo as $email) {
                        $mailTemplate->addBcc($email);
                    }
                }

            }

            if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
                foreach ($copyTo as $email) {
                    $sendTo[] = array(
                        'name' => null,
                        'email' => $email
                    );
                }
            }

            if (Mage::getStoreConfig('sales_email/invoice/attachpdf', $this->getStoreId())) {
                //Create Pdf and attach to email - play nicely with PDF Customiser
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf(array($this), null, null, true);
                $mailTemplate->addAttachment($pdf, Mage::helper('sales')->__('Invoice') . "_" . $this->getIncrementId());
            }

            if (Mage::getStoreConfig('sales_email/invoice/attachagreement', $this->getStoreId())) {
                $mailTemplate->addAgreements($this->getStoreId());
            }

            foreach ($sendTo as $recipient) {
                $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $order->getStoreId()))
                    ->sendTransactional(
                        $template,
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $order->getStoreId()),
                        $recipient['email'],
                        $recipient['name'],
                        array(
                            'order' => $order,
                            'invoice' => $this,
                            'comment' => $comment,
                            'billing' => $order->getBillingAddress(),
                            'payment_html' => $paymentBlockHtml,
                        )
                    );
            }

            $translate->setTranslateInline(true);

        } catch (Exception $e) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        }
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        return $this;
    }

    /**
     * Sending email with invoice update information
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function sendUpdateEmail($notifyCustomer=true, $comment='')
    {
        if (!Mage::helper('sales')->canSendInvoiceCommentEmail($this->getOrder()->getStore()->getId())) {
            return $this;
        }

        $area = Mage::getDesign()->getArea();
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($this->getStoreId(), $area);

        try {

            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);

            $order = $this->getOrder();

            $copyTo = $this->_getEmails(self::XML_PATH_UPDATE_EMAIL_COPY_TO);
            $copyMethod = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_COPY_METHOD, $this->getStoreId());

            if (!$notifyCustomer && !$copyTo) {
                $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                return $this;
            }

            $sendTo = array();

            $mailTemplate = Mage::getModel('core/email_template');

            if ($order->getCustomerIsGuest()) {
                $template = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $order->getStoreId());
                $customerName = $order->getBillingAddress()->getName();
            } else {
                $template = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_TEMPLATE, $order->getStoreId());
                $customerName = $order->getCustomerName();
            }

            if ($notifyCustomer) {
                $sendTo[] = array(
                    'name' => $customerName,
                    'email' => $order->getCustomerEmail()
                );
                if ($copyTo && $copyMethod == 'bcc') {
                    foreach ($copyTo as $email) {
                        $mailTemplate->addBcc($email);
                    }
                }

            }

            if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
                foreach ($copyTo as $email) {
                    $sendTo[] = array(
                        'name' => null,
                        'email' => $email
                    );
                }
            }

            if (Mage::getStoreConfig('sales_email/invoice_comment/attachpdf', $this->getStoreId())) {
                //Create Pdf and attach to email - play nicely with PDF Customiser
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf(array($this), null, null, true);
                $mailTemplate->addAttachment($pdf, Mage::helper('sales')->__('Invoice') . "_" . $this->getIncrementId());
            }

            if (Mage::getStoreConfig('sales_email/invoice_comment/attachagreement', $this->getStoreId())) {
                $mailTemplate->addAgreements($this->getStoreId());
            }

            foreach ($sendTo as $recipient) {
                $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $order->getStoreId()))
                    ->sendTransactional(
                        $template,
                        Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_IDENTITY, $order->getStoreId()),
                        $recipient['email'],
                        $recipient['name'],
                        array(
                            'order' => $order,
                            'billing' => $order->getBillingAddress(),
                            'invoice' => $this,
                            'comment' => $comment
                        )
                    );
            }

            $translate->setTranslateInline(true);

        } catch (Exception $e) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        }
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        return $this;
    }

}

