<?php
/**
 * Effy Freshdesk extension
 *
 * @category    Effy_Freshdesk
 * @package     Effy_Freshdesk
 * @copyright   Copyright (c) 2014 Effy. (http://www.effy.com)
 * @license     http://www.effy.com/disclaimer.html
 */

/**
 * Class Effy_Freshdesk_Adminhtml_FreshdeskController
 */
class Effy_Freshdesk_Adminhtml_FreshdeskController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed()
    {
        $action = $this->getRequest()->getActionName();

        if (in_array($action, array('portal', 'ticket', 'token', 'customer/view'))) {
            return Mage::getSingleton('admin/session')->isAllowed('admin/freshdesk/' . $action);
        }

        return Mage::getSingleton('admin/session')->isAllowed('admin/freshdesk/ticket');
    }

    public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::helper('freshdesk')->isConfigSet()) {
            $message = $this->__('Please fill in Freshdesk configuration options first');            
            if ($this->getRequest()->getActionName() == 'customerView') {
                echo $message;
            } else {
                $this->_getSession()->addError($message);
                $this->_redirect('*/system_config/edit/section/freshdesk');
                $this->getResponse()->sendResponse();
            }
            exit;
        }

        return $this;
    }

    /**
     * Displays the tickets overview grid.
     */
    public function ticketAction()
    {
        try {
            return $this->loadLayout()
                ->_setActiveMenu('freshdesk/ticket')
                ->_title($this->__('Freshdesk'))
                ->_title($this->__('Manage Tickets'))
                ->_addContent($this->getLayout()->createBlock('effy_freshdesk/adminhtml_ticket'))
                ->renderLayout();
        } catch (Effy_Freshdesk_Exception $mfe) {
            $this->_getSession()->addError($mfe->getMessage());
            $this->_getSession()->addError($this->__('Please check Freshdesk configuration options'));
            if ($mfe->isWrongConfig()) {
                $this->_redirect('*/system_config/edit/section/freshdesk');
            } else {
                $this->_redirect('*');
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*');
        }
    }

    public function portalAction()
    {
        $this->_redirectUrl(Mage::getSingleton('effy_freshdesk/freshdesk')->getDashboardUrl());
        Mage::app()->getResponse()->sendResponse();
        exit;
    }

    public function customerViewAction()
    {
        if (!is_object(Mage::registry('current_customer'))) {
            $customerId = $this->getRequest()->getParam('id');
            $customer   = Mage::getModel('customer/customer')->load($customerId);
            Mage::unregister('current_customer');
            Mage::register('current_customer', $customer);
        }

        $content = '';
        try {
            $content = $this->getLayout()
                ->createBlock('effy_freshdesk/adminhtml_ticket_grid')
                ->toHtml();
        } catch (Effy_Freshdesk_Exception $mfe) {
            $content .= $mfe->getMessage();
            $content .= $this->__('Please check Freshdesk configuration options');
        } catch (Exception $e) {
            $content .= $e->getMessage();
        }

        $this->getResponse()->setBody($content);
    }    

    public function tokenAction() {
        // get individual data
        $user = Mage::getSingleton('admin/session'); 
        $userId = $user->getUser()->getEmail();        
        $salt = time();
        $token = '';
        $existingToken = Mage::getModel('effy_freshdesk/token')->getCollection()->count();
        if($existingToken == 0) {
            $token = hash('sha256', $userId . $salt);
            $tokenModel    = Mage::getModel('effy_freshdesk/token');
            $tokenModel->addData(array('hash' => $token, 'status' => true));
            $tokenModel->save();
        }
        else {
            $token = Mage::getModel('effy_freshdesk/token')->getCollection()->getData()[0]['hash'];
        }
        $this->_title($this->__('Freshdesk Dashboard'));
        $this->loadLayout();
        $block = Mage::app()->getLayout()->getBlock('freshdeskBlock');
        if ($block) {//check if block actually exists
            $block->setToken($token);
        }
        $this->renderLayout();
    }

    public function regenerateAction() {
        $user = Mage::getSingleton('admin/session'); 
        $userId = $user->getUser()->getEmail();        
        $salt = time();
        $token = Mage::getModel('effy_freshdesk/token')->getCollection()->getFirstItem();
        $tokenHash = hash('sha256', $userId . $salt);
        $token->addData(array('hash' => $tokenHash, 'status' => true));
        $token->save();            
        $this->getResponse()
            ->setBody(json_encode(array('token' => $tokenHash)))
            ->setHttpResponseCode(200)
            ->setHeader('Content-type', 'application/json', true);
        return $this;
    }

    /**
     * upload file and get the uploaded name
     *
     * @access public
     * @param string $input
     * @param string $destinationFolder
     * @param array $data
     * @return string
     * @author Ultimate Module Creator
     */
    protected function _uploadAndGetName($input, $destinationFolder, $data)
    {
        try {
            if (isset($data[$input]['delete'])) {
                return '';
            } else {
                $uploader = new Varien_File_Uploader($input);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save($destinationFolder);
                return $result['file'];
            }
        } catch (Exception $e) {
            if ($e->getCode() != Varien_File_Uploader::TMP_NAME_EMPTY) {
                throw $e;
            } else {
                if (isset($data[$input]['value'])) {
                    return $data[$input]['value'];
                }
            }
        }
        return '';
    }
}