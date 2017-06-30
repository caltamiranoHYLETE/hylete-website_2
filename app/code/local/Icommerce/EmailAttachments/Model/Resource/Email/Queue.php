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
class Icommerce_EmailAttachments_Model_Resource_Email_Queue extends Mage_Core_Model_Resource_Email_Queue
{
    /**
     * Serializeable fields: message_parameters, attachments
     *
     * @var array
     */
    protected $_serializableFields   = array(
        'message_parameters' => array(null, array()),
        'attachments'        => array(null, null),
    );

    /**
     * Load recipients, unserialize message parameters
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Mage_Core_Model_Resource_Email_Queue
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $object->setRecipients($this->getRecipients($object->getId()));
        $this->unserializeFields($object);
        return $this;
    }

    /**
     * Prepare object data for saving
     *
     * @param Mage_Core_Model_Email_Queue|Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Email_Queue
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->formatDate(true));
        }
        $object->setMessageBodyHash(md5($object->getMessageBody()));
        return $this;
    }

    /**
     * Remove already sent messages
     *
     * @return Mage_Core_Model_Resource_Email_Queue
     */
    public function removeSentMessages()
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('main_table' => $this->getMainTable()), array('message_id'))
            ->where('processed_at IS NOT NULL')
            ->where('is_file_system_used = 1');

        $result = $this->_getReadAdapter()->fetchCol($select);

        if (!empty($result)) {
            Mage::helper('emailattachments')->removeAttachments($result);
        }

        return parent::removeSentMessages();
    }
}
