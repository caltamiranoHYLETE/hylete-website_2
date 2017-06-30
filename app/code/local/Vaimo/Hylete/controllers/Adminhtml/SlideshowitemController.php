<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
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
 * @package     Veimo_Hylete
 * @file        Process.php
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Vitali Rassolov <vitali.rassolov@vaimo.com>
 */

require_once 'Icommerce/SlideshowManager/controllers/Adminhtml/SlideshowitemController.php';

class Vaimo_Hylete_Adminhtml_SlideshowitemController extends Icommerce_SlideshowManager_Adminhtml_SlideshowitemController
{
    private function _deleteOldImage($oldImageName)
    {

        $helper = Mage::helper('slideshowmanager');
        $oldTargetPath = $helper->getAbsoluteTargetPath() . $oldImageName;
        if ($oldImageName && file_exists($oldTargetPath)) {
            unlink($oldTargetPath);
        }
    }

    private function _uploadImage($imageName)
    {

        // File handling
        $file = $_FILES[$imageName];
        $fileName = strtolower($file['name']);

        if (empty($fileName)) {
            return null;
        }

        $fileExtension = preg_split("/[\.]+/", $fileName);
        if (is_array($fileExtension) && count($fileExtension) > 1) {
            $fileExtension = $fileExtension[count($fileExtension) - 1];
        }
        $type = $file['type'];
        $tmpName = $file['tmp_name'];
        $error = $file['error'];
        $size = $file['size'];
        $helper = Mage::helper('slideshowmanager');


        if (!$helper->isFileExtensionAllowed($fileExtension)) {
            throw new Exception($this->__('File extension is not allowed. Only .jpeg, .jpg, .gif and .png is allowed.'));
        }

        if (!$helper->isFileTypeAllowed($type)) {
            throw new Exception($this->__('File type is not allowed. Only jpeg, gif and png is allowed.'));
        }

        if ($size > $helper->getMaxAllowedFileSize()) {
            throw new Exception($this->__('Maximum allowed file size is %s KB. Please resize image and try again.', ($helper->getMaxAllowedFileSize() / 1024)));
        }

        $fileName = date('Y-m-d-h-m-s', Mage::getModel('core/date')->timestamp(time()));
        $fileName .= rand();
        $fileName .= '.' . $fileExtension;

        $targetPath = $helper->getAbsoluteTargetPath() . $fileName;

        if (file_exists($targetPath)) {
            throw new Exception($this->__('The file %s already exist.', $helper->getTargetPath() . $fileName));
        }

        if (!move_uploaded_file($tmpName, $targetPath)) {
            throw new Exception($this->__('File could not be moved. Please check write permissions on folder %s.', $helper->getTargetPath()));
        }

        return $fileName;

    }

    public function updateAction()
    {
        $post = $this->getRequest()->getPost();
        
        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $id = $post['editform']['id'];
            $item = Mage::getModel('slideshowmanager/item')->getItem($id);

            $params = array();

            // File handling

            $fileName = $this->_uploadImage('file');
            if ($fileName != null) {
                $params['filename'] = $fileName;
            }

            $tabletFileName = $this->_uploadImage('backgroundimage_tablet');
            if ($tabletFileName != null) {
                $params['backgroundimage_tablet'] = $tabletFileName;
            } elseif (isset($post['remove-image-tablet'])) {
                $params['backgroundimage_tablet'] = '';
            }

            $mobileFileName = $this->_uploadImage('backgroundimage_mobile');
            if ($mobileFileName != null) {
                $params['backgroundimage_mobile'] = $mobileFileName;
            } elseif (isset($post['remove-image-mobile'])) {
                $params['backgroundimage_mobile'] = '';
            }
            // End of file handling

            $params['title'] = $post['editform']['title'];
            $params['type'] = $post['editform']['type'];
            $params['image_text'] = $post['editform']['image_text'];
            $params['image_text_tablet'] = $post['editform']['image_text_tablet'];
            $params['image_text_phone'] = $post['editform']['image_text_phone'];
            $params['image_alt'] = $post['editform']['image_alt'];
            $params['link'] = $post['editform']['link'];
            $params['link_target'] = $post['editform']['link_target'];
            $params['status'] = $post['editform']['status'];
            $params['position'] = $post['editform']['position'];
            $params['hotspots'] = $post['editform']['hotspots'];
            $params['text_placement'] = $post['editform']['text_placement'];
            $params['align_text'] = $post['editform']['align_text'];
            $params['invert'] = $post['editform']['invert'];

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->update('icommerce_slideshow_item', $params, $where);

            if ($fileName) {
                $this->_deleteOldImage($item['filename']);
            }

            if ($tabletFileName || isset($post['remove-image-tablet'])) {
                $this->_deleteOldImage($item['backgroundimage_tablet']);
            }

            if ($mobileFileName || isset($post['remove-image-mobile'])) {
                $this->_deleteOldImage($item['backgroundimage_mobile']);
            }

            $message = $this->__('Your image has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

            /**
             * TODO: implement smart cache clear, to afferct only required pages.
             */
            Enterprise_PageCache_Model_Cache::getCacheInstance()->clean();
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::logException($e);

            $this->_redirect('*/slideshowitem/edit/id/' . $id);
            return;
        }

        $this->_redirect('*/slideshowmanager/edit/id/' . $_SESSION['slideshow_id']);
    }

    public function saveAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $params = array();

            // File handling
            $fileName = $this->_uploadImage('file');

            $tabletFileName = $this->_uploadImage('backgroundimage_tablet');
            if ($tabletFileName != null){
                $params['backgroundimage_tablet'] = $tabletFileName;
            }
            $mobileFileName = $this->_uploadImage('backgroundimage_mobile');
            if ($mobileFileName != null){
                $params['backgroundimage_mobile'] = $mobileFileName;
            }
            // End of file handling

            $params['filename'] = $fileName;
            $params['slideshow_id'] = $_SESSION['slideshow_id'];
            $params['type'] = $post['addform']['type'];
            $params['title'] = $post['addform']['title'];
            $params['image_text'] = $post['addform']['image_text'];
            $params['image_text_tablet'] = $post['addform']['image_text_tablet'];
            $params['image_text_phone'] = $post['addform']['image_text_phone'];
            $params['image_alt'] = $post['addform']['image_alt'];
            $params['link'] = $post['addform']['link'];
            $params['link_target'] = $post['addform']['link_target'];
            $params['status'] = $post['addform']['status'];
            $params['position'] = $post['addform']['position'];
            $params['created_on'] = date('Y-m-d h:m:s', Mage::getModel('core/date')->timestamp(time()));
            $params['text_placement'] = $post['editform']['text_placement'];
            $params['align_text'] = $post['editform']['align_text'];
            $params['invert'] = $post['editform']['invert'];
            
            $session = Mage::getSingleton('admin/session');
            $userId = $session->getUser()->getUserId();

            $params['created_by'] = $userId;

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $r = $wr->insert( 'icommerce_slideshow_item', $params );

            $message = $this->__('Your image has been saved successfully.');

            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::logException($e);

            $this->_redirect('*/slideshowitem/add/');
            return;
        }

        $this->_redirect('*/slideshowmanager/edit/id/'. $_SESSION['slideshow_id']);
    }
}
