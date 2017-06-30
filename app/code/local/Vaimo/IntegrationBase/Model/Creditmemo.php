<?php
/**
 * Copyright (c) 2009-2013 Vaimo AB
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
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 *
 * @method Vaimo_IntegrationBase_Model_Creditmemo setRowStatus(int $value)
 * @method Vaimo_IntegrationBase_Model_Creditmemo setCreatedAt(string $value)
 * @method Vaimo_IntegrationBase_Model_Creditmemo setUpdatedAt(string $value)
 * @method Vaimo_IntegrationBase_Model_Creditmemo setIncrementId(string $value)
 * @method Vaimo_IntegrationBase_Model_Creditmemo setDoOffline(bool $value)
 * @method Vaimo_IntegrationBase_Model_Creditmemo setCommentText(string $value)
 * @method Vaimo_IntegrationBase_Model_Creditmemo setShippingAmount(float $value)
 * @method Vaimo_IntegrationBase_Model_Creditmemo setAdjustmentPositive(float $value)
 * @method Vaimo_IntegrationBase_Model_Creditmemo setAdjustmentNegative(float $value)
 * @method Vaimo_IntegrationBase_Model_Creditmemo setSendEmail(bool $value)
 * @method Vaimo_IntegrationBase_Model_Creditmemo setRefundCustomerbalanceReturnEnable(bool $value)
 * @method Vaimo_IntegrationBase_Model_Creditmemo setRefundCustomerbalanceReturn(float $value)
 * @method int getRowStatus()
 * @method string getCreatedAt()
 * @method string getUpdatedAt()
 * @method string getIncrementId()
 * @method bool getDoOffline()
 * @method string getCommentText()
 * @method float getShippingAmount()
 * @method float getAdjustmentPositive()
 * @method float getAdjustmentNegative()
 * @method bool getSendEmail()
 * @method bool getRefundCustomerbalanceReturnEnable()
 * @method float getRefundCustomerbalanceReturn()
 *
 */

class Vaimo_IntegrationBase_Model_Creditmemo extends Vaimo_IntegrationBase_Model_Abstract
{
    /**
     * Container for the credit memo items
     *
     * @var null|Vaimo_IntegrationBase_Model_Resource_Creditmemo_Item_Collection
     */
    protected $_items = null;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/creditmemo');
    }

    /**
     * Save the credit memo items
     *
     * @return $this|Mage_Core_Model_Abstract
     */
    protected function _afterSave()
    {
        parent::_afterSave();
        if (null !== $this->_items) {
            $this->getItemsCollection()->save();
        }
        return $this;
    }

    /**
     * Get the credit memo item collection
     *
     * @return Vaimo_IntegrationBase_Model_Resource_Creditmemo_Item_Collection
     */
    public function getItemsCollection()
    {
        if ($this->hasItemsCollection()) {
            return $this->getData('items_collection');
        }
        if (is_null($this->_items)) {
            $this->_items = Mage::getModel('integrationbase/creditmemo_item')->getCollection();
            $this->_items->setCreditmemo($this);
        }
        return $this->_items;
    }

    /**
     * Get the all credit memo items in an array
     *
     * @return array
     */
    public function getAllItems()
    {
        $items = array();
        foreach ($this->getItemsCollection() as $item) {
            $items[] =  $item;
        }
        return $items;
    }

    /**
     * Add an item to the credit memo import data
     *
     * @param Vaimo_IntegrationBase_Model_Creditmemo_Item $item
     * @return $this
     */
    public function addItem(Vaimo_IntegrationBase_Model_Creditmemo_Item $item)
    {
        $item->setCreditmemo($this);
        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
        }
        return $this;
    }
}