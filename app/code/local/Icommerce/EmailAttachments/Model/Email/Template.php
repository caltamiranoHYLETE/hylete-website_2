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
class Icommerce_EmailAttachments_Model_Email_Template extends Mage_Core_Model_Email_Template
{
    const MIME_TYPE_PDF = 'application/pdf';

    /**
     * Add PDF file as email attachment
     *
     * @param Mage_Sales_Model_Order_Pdf_Abstract $pdf
     * @param string $name
     *
     * @return Icommerce_EmailAttachments_Model_Email_Template
     */
    public function addAttachment($pdf, $name = 'attachment')
    {
        $file = $this->_getRenderedPdf($pdf);
        $this->addGenericAttachment($file, self::MIME_TYPE_PDF, $name . '.pdf');
        return $this;
    }

    /**
     * Add user agreements as email attachment
     *
     * @param int|Mage_Core_Model_Store $store
     *
     * @return Icommerce_EmailAttachments_Model_Email_Template
     */
    public function addAgreements($store)
    {
        $agreements = Mage::getModel('checkout/agreement')->getCollection()
            ->addStoreFilter($store)
            ->addFieldToFilter('is_active', 1);

        if ($agreements) {
            foreach ($agreements as $agreement){
                $agreement->load($agreement->getId());
                $agreementName = $agreement->getName();
                $agreementContent = $agreement->getContent();
                if ($agreement->getIsHtml()) {
                    $html = '<html><head><meta charset="utf-8" /><title>' . Mage::helper('core')->htmlEscape($agreementName) . '</title></head>'
                          . '<body>' . $agreementContent . '</body></html>';
                    $this->addGenericAttachment($html, Zend_Mime::TYPE_HTML, utf8_decode($agreementName) . '.html');
                } else {
                    $this->addGenericAttachment(
                        Mage::helper('core')->stripTags($agreementContent),
                        Zend_Mime::TYPE_TEXT,
                        utf8_decode($agreementName) . '.txt'
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Add file as email attachment
     *
     * @param  string $body
     * @param  string $mimeType
     * @param  string $filename OPTIONAL A filename for the attachment
     * @param  string $disposition
     * @param  string $encoding
     *
     * @return Icommerce_EmailAttachments_Model_Email_Template
     */
    public function addGenericAttachment($body,
                                         $mimeType    = Zend_Mime::TYPE_OCTETSTREAM,
                                         $filename    = null,
                                         $disposition = Zend_Mime::DISPOSITION_ATTACHMENT,
                                         $encoding    = Zend_Mime::ENCODING_BASE64)
    {
        $helper = Mage::helper('emailattachments');
        $filename = $helper->sanitizeFilename($filename);
        $paramKeys = $helper->getAttachmentParamSequence();
        $paramValues = array(
            $body, $mimeType, $disposition,
            $encoding, $filename,
        );

        $attachmentDetails = array_combine($paramKeys, $paramValues);

        if ($this->hasQueue() && $this->getQueue() instanceof Mage_Core_Model_Email_Queue) {
            $this->addAttachmentToQueuedEmail($attachmentDetails);
        } else {
            $helper->createAttachment($this->getMail(), $attachmentDetails);
        }

        return $this;
    }

    /**
     * Add attachment to the mailer queue
     *
     * @param array $attachmentDetails
     *
     * @return Icommerce_EmailAttachments_Model_Email_Template
     */
    public function addAttachmentToQueuedEmail($attachmentDetails)
    {
        $queue = $this->getQueue();
        $attachments = $queue->getAttachments();

        if (!is_array($attachments)) {
            $attachments = array();
        }
        $attachments[] = $attachmentDetails;
        $queue->setAttachments($attachments);

        return $this;
    }

    /**
     * Retrieve PDF content
     *
     * @param Mage_Sales_Model_Order_Pdf_Abstract $pdf
     *
     * @return string
     */
    protected function _getRenderedPdf($pdf)
    {
        return $pdf->render();
    }
}
