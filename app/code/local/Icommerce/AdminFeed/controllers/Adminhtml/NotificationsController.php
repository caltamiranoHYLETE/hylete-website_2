<?php
/**
 * Copyright (c) 2009-2011 Icommerce Nordic AB
 *
 * Icommerce reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Icommerce, except as provided by licence. A licence
 * under Icommerce's rights in the Program may be available directly from
 * Icommerce.
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
 * @package     Icommerce_AdminFeed
 * @copyright   Copyright (c) 2009-2011 Icommerce Nordic AB
 */

class Icommerce_AdminFeed_Adminhtml_NotificationsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {        
        $this->loadLayout();
    	$this->_setActiveMenu('icommerce/adminfeed');
    	$this->renderLayout();
    }
    
    public function markAsReadAction()
    {
        $notification_id = $this->getRequest()->getParam('id');
        try 
        {
            if (empty($notification_id)) 
                Mage::throwException($this->__('Invalid form data.'));
            
            /* here's my form processing */
            $wr = Icommerce_Db::getDbWrite();
			$sql = "UPDATE adminnotification_inbox SET is_read='1' WHERE notification_id=$notification_id";
			$r = $wr->query( $sql );
            /* here's my form processing */
            
            $message = $this->__('Your message has been marked as read.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        } 
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*');
    }
    
    public function markAsUnReadAction()
    {
        $notification_id = $this->getRequest()->getParam('id');
        try 
        {
            if (empty($notification_id)) 
                Mage::throwException($this->__('Invalid form data.'));
            
            /* here's my form processing */
            $wr = Icommerce_Db::getDbWrite();
			$sql = "UPDATE adminnotification_inbox SET is_read='0' WHERE notification_id=$notification_id";
			$r = $wr->query( $sql );
            /* here's my form processing */
            
            $message = $this->__('Your message has been marked as unread.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        } 
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*');
    }
    
    public function deleteAction()
    {
        $notification_id = $this->getRequest()->getParam('id');
        try 
        {
            if (empty($notification_id)) 
                Mage::throwException($this->__('Invalid form data.'));
            
            /* here's my form processing */
            $wr = Icommerce_Db::getDbWrite();
			$sql = "UPDATE adminnotification_inbox SET is_remove='1' WHERE notification_id=$notification_id";
			$r = $wr->query( $sql );
            /* here's my form processing */
            
            $message = $this->__('Your message has been deleted.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        } 
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*');
    }
}