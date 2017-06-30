<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @category    Vaimo
 * @package     Vaimo_IntegrationBase
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 *
 * @method string getIncrementId()
 * @method Vaimo_IntegrationBase_Model_Invoice setIncrementId(string $value)
 */

class Vaimo_IntegrationBase_Model_Invoice extends Vaimo_IntegrationBase_Model_Abstract
{
    protected $_items = null;
    protected $_comments = null;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/invoice');
    }

    /**
     * Save items and comments
     *
     * @return $this
     */
    protected function _afterSave()
    {
        parent::_afterSave();
        if (null !== $this->_items) {
            $this->getItemsCollection()->save();
        }
        if (null !== $this->_comments) {
            $this->getCommentsCollection()->save();
        }
        return $this;
    }

    /**
     * Get all items for this invoice
     *
     * @return Vaimo_IntegrationBase_Model_Resource_Invoice_Item_Collection
     */
    public function getItemsCollection()
    {
        if ($this->hasItemsCollection()) {
            return $this->getData('items_collection');
        }
        if (is_null($this->_items)) {
            $this->_items = Mage::getModel('integrationbase/invoice_item')->getCollection();
            $this->_items->setInvoice($this);
        }
        return $this->_items;
    }

    /**
     * Add item import data to invoice import data
     *
     * @param Vaimo_IntegrationBase_Model_Invoice_Item $item
     * @return $this
     */
    public function addItem(Vaimo_IntegrationBase_Model_Invoice_Item $item)
    {
        $item->setInvoice($this);
        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
        }
        return $this;
    }

    /**
     * Add invoice item by data
     *
     * @param string $sku
     * @param float $qty
     * @return $this
     */
    public function addItemByData($sku, $qty)
    {
        $item = Mage::getModel('integrationbase/invoice_item')
            ->setSku($sku)
            ->setQty($qty);

        return $this->addItem($item);
    }

    /**
     * Get all comments for this invoice
     *
     * @return Vaimo_IntegrationBase_Model_Resource_Invoice_Comment_Collection
     */
    public function getCommentsCollection()
    {
        if ($this->hasCommentCollection()) {
            return $this->getData('comments_collection');
        }
        if (is_null($this->_comments)) {
            $this->_comments = Mage::getModel('integrationbase/invoice_comment')->getCollection();
            $this->_comments->setInvoice($this);
        }
        return $this->_comments;
    }

    /**
     * Add comment
     *
     * @param Vaimo_IntegrationBase_Model_Invoice_Comment $comment
     * @return $this
     */
    public function addComment(Vaimo_IntegrationBase_Model_Invoice_Comment $comment)
    {
        $comment->setInvoice($this);
        if (!$comment->getId()) {
            $this->getCommentsCollection()->addItem($comment);
        }
        return $this;
    }

    /**
     * Add comment by data
     *
     * @param bool $notify
     * @param bool $visible
     * @param string $comment
     * @return $this
     */
    public function addCommentByData($notify, $visible, $comment)
    {
        $item = Mage::getModel('integrationbase/invoice_comment')
            ->setIsCustomerNotified($notify)
            ->setIsVisibleOnFront($visible)
            ->setComment($comment);

        return $this->addComment($item);
    }
}