<?php
/**
 * Effy_Freshdesk extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Freshdesk
 * @package        Effy_Freshdesk
 * @copyright      Copyright (c) 2018
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Token admin controller
 *
 * @category    Freshdesk
 * @package     Effy_Freshdesk
 * @author      Ultimate Module Creator
 */
class Effy_Freshdesk_Adminhtml_Freshdesk_TokenController extends Effy_Freshdesk_Controller_Adminhtml_Freshdesk
{

    /**
     * init the token
     *
     * @access protected
     * @return Effy_Freshdesk_Model_Token
     */
    protected function _initToken()
    {
        $tokenId  = (int) $this->getRequest()->getParam('id');
        $token    = Mage::getModel('effy_freshdesk/token');
        if ($tokenId) {
            $token->load($tokenId);
        }
        Mage::register('current_token', $token);
        return $token;
    }

    /**
     * default action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title(Mage::helper('freshdesk')->__('Freshdesk'))
             ->_title(Mage::helper('freshdesk')->__('Tokens'));
        $this->renderLayout();
    }

    /**
     * grid action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * edit token - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function editAction()
    {
        $tokenId    = $this->getRequest()->getParam('id');
        $token      = $this->_initToken();
        if ($tokenId && !$token->getId()) {
            $this->_getSession()->addError(
                Mage::helper('freshdesk')->__('This token no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getTokenData(true);
        if (!empty($data)) {
            $token->setData($data);
        }
        Mage::register('token_data', $token);
        $this->loadLayout();
        $this->_title(Mage::helper('freshdesk')->__('Freshdesk'))
             ->_title(Mage::helper('freshdesk')->__('Tokens'));
        if ($token->getId()) {
            $this->_title($token->getHash());
        } else {
            $this->_title(Mage::helper('freshdesk')->__('Add token'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new token action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * save token - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('token')) {
            try {
                $token = $this->_initToken();
                $token->addData($data);
                $token->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('freshdesk')->__('Token was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $token->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setTokenData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('freshdesk')->__('There was a problem saving the token.')
                );
                Mage::getSingleton('adminhtml/session')->setTokenData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('freshdesk')->__('Unable to find token to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete token - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $token = Mage::getModel('effy_freshdesk/token');
                $token->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('freshdesk')->__('Token was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('freshdesk')->__('There was an error deleting token.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('freshdesk')->__('Could not find token to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete token - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massDeleteAction()
    {
        $tokenIds = $this->getRequest()->getParam('token');
        if (!is_array($tokenIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('freshdesk')->__('Please select tokens to delete.')
            );
        } else {
            try {
                foreach ($tokenIds as $tokenId) {
                    $token = Mage::getModel('effy_freshdesk/token');
                    $token->setId($tokenId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('freshdesk')->__('Total of %d tokens were successfully deleted.', count($tokenIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('freshdesk')->__('There was an error deleting tokens.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass status change - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massStatusAction()
    {
        $tokenIds = $this->getRequest()->getParam('token');
        if (!is_array($tokenIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('freshdesk')->__('Please select tokens.')
            );
        } else {
            try {
                foreach ($tokenIds as $tokenId) {
                $token = Mage::getSingleton('effy_freshdesk/token')->load($tokenId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d tokens were successfully updated.', count($tokenIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('freshdesk')->__('There was an error updating tokens.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * export as csv - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function exportCsvAction()
    {
        $fileName   = 'token.csv';
        $content    = $this->getLayout()->createBlock('effy_freshdesk/adminhtml_token_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as MsExcel - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function exportExcelAction()
    {
        $fileName   = 'token.xls';
        $content    = $this->getLayout()->createBlock('effy_freshdesk/adminhtml_token_grid')
            ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as xml - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function exportXmlAction()
    {
        $fileName   = 'token.xml';
        $content    = $this->getLayout()->createBlock('effy_freshdesk/adminhtml_token_grid')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }
}
