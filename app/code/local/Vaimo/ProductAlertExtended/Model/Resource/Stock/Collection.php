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
class Vaimo_ProductAlertExtended_Model_Resource_Stock_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {
    
    protected function _construct() {
        $this->_init ( "productalertextended/stock" );
    }
    
    /**
     * Add website filter
     *
     * @param mixed $website            
     * @return Mage_ProductAlert_Model_Resource_Stock_Collection
     */
    public function addWebsiteFilter($website) {
        $adapter = $this->getConnection ();
        if (is_null ( $website ) || $website == 0) {
            return $this;
        }
        if (is_array ( $website )) {
            $condition = $adapter->quoteInto ( 'website_id IN(?)', $website );
        } elseif ($website instanceof Mage_Core_Model_Website) {
            $condition = $adapter->quoteInto ( 'website_id=?', $website->getId () );
        } else {
            $condition = $adapter->quoteInto ( 'website_id=?', $website );
        }
        $this->addFilter ( 'website_id', $condition, 'string' );
        return $this;
    }
    
    /**
     * Add status filter
     *
     * @param int $status            
     * @return Mage_ProductAlert_Model_Resource_Stock_Collection
     */
    public function addStatusFilter($status) {
        $condition = $this->getConnection ()->quoteInto ( 'status=?', $status );
        $this->addFilter ( 'status', $condition, 'string' );
        return $this;
    }
    
    /**
     * Set order by customer
     *
     * @param string $sort            
     * @return Mage_ProductAlert_Model_Resource_Stock_Collection
     */
    public function setCustomerOrder($sort = 'ASC') {
        $this->getSelect ()->order ( 'customer_id ' . $sort );
        return $this;
    }
}