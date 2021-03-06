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
class Vaimo_ProductAlertExtended_Model_Resource_Stock extends Vaimo_ProductAlertExtended_Model_Resource_Abstract {
    
    protected function _construct() {
        $this->_init ( 'productalertextended/stock', 'alert_id' );
    }
    
    /**
     * Before save action
     *
     * @param Mage_Core_Model_Abstract $object            
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object) {
        if (is_null ( $object->getId () ) && ($object->getCustomerId () || $object->getEmail ()) && $object->getProductId () && $object->getWebsiteId ()) {
            if ($row = $this->_getAlertRow ( $object )) {
                $object->addData ( $row );
                $object->setStatus ( 0 );
            }
        }
        if (is_null ( $object->getAddDate () )) {
            $object->setAddDate ( Mage::getModel ( 'core/date' )->gmtDate () );
            $object->setStatus ( 0 );
        }
        return parent::_beforeSave ( $object );
    }
}