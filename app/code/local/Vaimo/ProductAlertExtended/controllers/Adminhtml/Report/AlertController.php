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
class Vaimo_ProductAlertExtended_Adminhtml_Report_AlertController extends Mage_Adminhtml_Controller_Action {
    
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/report/customers/alerts');
    }
    
    public function _initAction() {
        $act = $this->getRequest ()->getActionName ();
        if (! $act)
            $act = 'default';
        
        $this->loadLayout ()->_addBreadcrumb ( Mage::helper ( 'reports' )->__ ( 'Reports' ), Mage::helper ( 'reports' )->__ ( 'Reports' ) )->_addBreadcrumb ( Mage::helper ( 'reports' )->__ ( 'Customers' ), Mage::helper ( 'reports' )->__ ( 'Customers' ) );
        return $this;
    }
    
    public function indexAction() {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
    }
    
    public function allAction() {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->_title ( $this->__ ( 'Reports' ) )->_title ( $this->__ ( 'Customers' ) )->_title ( $this->__ ( 'Alert Requests' ) );
        
        $this->_initAction ()->_setActiveMenu ( 'report/customers/alerts' )->_addBreadcrumb ( Mage::helper ( 'adminhtml' )->__ ( 'Alert Requests' ), Mage::helper ( 'adminhtml' )->__ ( 'Alert Requests' ) )->_addContent ( $this->getLayout ()->createBlock ( 'productalertextended/adminhtml_report_alert' ) )->renderLayout ();
    }
    
    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('productalertextended/adminhtml_report_alert_grid')->toHtml());
    }
}