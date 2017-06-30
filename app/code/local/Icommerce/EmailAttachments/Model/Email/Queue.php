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
class Icommerce_EmailAttachments_Model_Email_Queue extends Mage_Core_Model_Email_Queue
{
    /**
     * Validate recipients before saving
     *
     * @return Icommerce_EmailAttachments_Model_Email_Queue
     */
    protected function _beforeSave()
    {
        Mage::helper('emailattachments')->copyEmailAttachments($this);
        return parent::_beforeSave();
    }

    /**
     * Save bind recipients to message
     *
     * @return Icommerce_EmailAttachments_Model_Email_Queue
     */
    protected function _afterSave()
    {
        Mage::helper('emailattachments')->renameEmailAttachmentsDirectory($this);
        return parent::_afterSave();
    }

    /**
     * Send all messages in a queue
     *
     * @return Icommerce_EmailAttachments_Model_Email_Queue
     */
    public function send()
    {
        /** @var $collection Mage_Core_Model_Resource_Email_Queue_Collection */
        $collection = Mage::getModel('core/email_queue')->getCollection()
            ->addOnlyForSendingFilter()
            ->setPageSize(self::MESSAGES_LIMIT_PER_CRON_RUN)
            ->setCurPage(1)
            ->load();


        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        /** @var $message Mage_Core_Model_Email_Queue */
        foreach ($collection as $message) {
            if ($message->getId()) {
                $parameters = new Varien_Object($message->getMessageParameters());
                if ($parameters->getReturnPathEmail() !== null) {
                    $mailTransport = new Zend_Mail_Transport_Sendmail("-f" . $parameters->getReturnPathEmail());
                    Zend_Mail::setDefaultTransport($mailTransport);
                }

                $mailer = new Zend_Mail('utf-8');
                // Vaimo addition
                $this->includeCustomAttachments($mailer, $message);
                // Vaimo addition
                foreach ($message->getRecipients() as $recipient) {
                    list($email, $name, $type) = $recipient;
                    switch ($type) {
                        case self::EMAIL_TYPE_BCC:
                            $mailer->addBcc($email, '=?utf-8?B?' . base64_encode($name) . '?=');
                            break;
                        case self::EMAIL_TYPE_TO:
                        case self::EMAIL_TYPE_CC:
                        default:
                            $mailer->addTo($email, '=?utf-8?B?' . base64_encode($name) . '?=');
                            break;
                    }
                }

                if ($parameters->getIsPlain()) {
                    $mailer->setBodyText($message->getMessageBody());
                } else {
                    $mailer->setBodyHTML($message->getMessageBody());
                }

                $mailer->setSubject('=?utf-8?B?' . base64_encode($parameters->getSubject()) . '?=');
                $mailer->setFrom($parameters->getFromEmail(), $parameters->getFromName());
                if ($parameters->getReplyTo() !== null) {
                    $mailer->setReplyTo($parameters->getReplyTo());
                }
                if ($parameters->getReturnTo() !== null) {
                    $mailer->setReturnPath($parameters->getReturnTo());
                }

                try {
                    $mailer->send();
                    unset($mailer);
                    $message->setProcessedAt(Varien_Date::formatDate(true));
                    $message->save();
                }
                catch (Exception $e) {
                    unset($mailer);
                    $oldDevMode = Mage::getIsDeveloperMode();
                    Mage::setIsDeveloperMode(true);
                    Mage::logException($e);
                    Mage::setIsDeveloperMode($oldDevMode);

                    return false;
                }
            }
        }

        // Vaimo addition
        ini_restore('SMTP');
        ini_restore('smtp_port');
        // Vaimo addition

        return $this;
    }

    /**
     * Include custom email attachments if any
     *
     * @param Mage_Core_Model_Email_Template_Mailer $mailer
     * @param Mage_Core_Model_Email_Queue $queue
     *
     * @return Icommerce_EmailAttachments_Model_Email_Queue
     */
    public function includeCustomAttachments($mailer, $queue)
    {
        $attachments = $queue->getAttachments();
        if (!empty($attachments)) {
            $helper = Mage::helper('emailattachments');
            foreach ($attachments as $attachment) {
                if (is_array($attachment) && !empty($attachment)) {
                    $helper->createAttachment($mailer, $attachment, $queue);
                }
            }
        }
        return $this;
    }
}
