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
 * @package     Vaimo_ProductAlertExtended
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Giorgos Tsioutsiouliklis <giorgos@vaimo.com>
 */
abstract class Vaimo_ProductAlertExtended_Model_Resource_Abstract extends Mage_Core_Model_Resource_Db_Abstract {
    
    /**
     * Retrieve alert row by object parameters
     *
     * @param Mage_Core_Model_Abstract $object            
     * @return array bool
     */
    protected function _getAlertRow(Mage_Core_Model_Abstract $object) {
        $adapter = $this->_getReadAdapter ();
        if ($object->getCustomerId () && $object->getProductId () && $object->getWebsiteId ()) {
            $select = $adapter->select ()->from ( $this->getMainTable () )->where ( 'customer_id = :customer_id' )->where ( 'product_id  = :product_id' )->where ( 'website_id  = :website_id' );
            $bind = array (
                    ':customer_id' => $object->getCustomerId (),
                    ':product_id' => $object->getProductId (),
                    ':website_id' => $object->getWebsiteId () 
            );
            return $adapter->fetchRow ( $select, $bind );
        } else if ($object->getEmail () && $object->getProductId () && $object->getWebsiteId ()) {
            $select = $adapter->select ()->from ( $this->getMainTable () )->where ( 'email = :email' )->where ( 'product_id  = :product_id' )->where ( 'website_id  = :website_id' );
            $bind = array (
                    ':email' => $object->getEmail (),
                    ':product_id' => $object->getProductId (),
                    ':website_id' => $object->getWebsiteId () 
            );
            return $adapter->fetchRow ( $select, $bind );
        }
        return false;
    }
    
    /**
     * Delete all customer alerts on website
     *
     * @param Mage_Core_Model_Abstract $object
     * @param int $customerId
     * @param int $websiteId
     * @return Mage_ProductAlert_Model_Resource_Abstract
     */
    public function deleteCustomerByMail(Mage_Core_Model_Abstract $object, $customerEmail, $websiteId=null)
    {
        $adapter = $this->_getWriteAdapter();
        $where   = array();
        $where[] = $adapter->quoteInto('email=?', $customerEmail);
        if ($websiteId) {
            $where[] = $adapter->quoteInto('website_id=?', $websiteId);
        }
        $adapter->delete($this->getMainTable(), $where);
        return $this;
    }
    
    /**
     * Load object data by parameters
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_ProductAlert_Model_Resource_Abstract
     */
    public function loadByParam(Mage_Core_Model_Abstract $object)
    {
        $row = $this->_getAlertRow($object);
        if ($row) {
            $object->setData($row);
        }
        return $this;
    }
}