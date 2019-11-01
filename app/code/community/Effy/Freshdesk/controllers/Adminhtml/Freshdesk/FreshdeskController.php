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
 * module base admin controller
 *
 * @category    Freshdesk
 * @package     Effy_Freshdesk
 * @author      Ultimate Module Creator
 */
class Effy_Freshdesk_Adminhtml_Effy_FreshdeskController extends Mage_Adminhtml_Controller_Action
{
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
}