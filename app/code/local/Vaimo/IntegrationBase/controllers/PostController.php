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
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_IntegrationBase
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 */

class Vaimo_IntegrationBase_PostController extends Mage_Core_Controller_Front_Action
{
    protected function _importAction($importerCode)
    {
        try {
            if (!$this->getRequest()->isPost()) {
                Mage::throwException('Not a post');
            }

            if (!Mage::getStoreConfig('integrationbase/post/enabled')) {
                Mage::throwException('Posting not allowed');
            }

            if ($this->getRequest()->getParam('password') != Mage::helper('core')->decrypt(Mage::getStoreConfig('integrationbase/post/password'))) {
                Mage::throwException('Login failed');
            }

            /** @var Vaimo_IntegrationBase_Model_Importer_Csv_Abstract $importer */
            $importer = Mage::helper('integrationbase')->getImporter($importerCode);

            if (!$importer) {
                Mage::throwException('Invalid type');
            }

            $destinationFolder = Mage::getBaseDir('var') . DS . 'integrationbase' . DS . 'post' . DS . date('Y') . DS . date('m');

            /** @var Mage_Core_Model_File_Uploader $uploader */
            $uploader = Mage::getModel('core/file_uploader', 'file');
            $uploader->setAllowedExtensions(array('csv'));
            $uploader->setAllowRenameFiles(true);

            if ($result = $uploader->save($destinationFolder)) {
                $filename = $result['path'] . DS . $result['file'];
            } else {
                $filename = false;
            }

            if (!$filename) {
                Mage::throwException('File upload failed');
            }

            $importer->import($filename);
            $body = $this->__('%d record(s) imported, %d record(s) failed to import', $importer->getSuccessCount(), $importer->getFailureCount());

            if ($importer->getErrors()) {
                $status = 400;
                $body .= "\n" . implode("\n", $result);
            } else {
                $status = 200;
            }
        } catch (Exception $e) {
            $status = 400;
            $body = $e->getMessage();
        }

        $this->getResponse()
            ->setHttpResponseCode($status)
            ->setHeader('Content-Type', 'text/plain', true)
            ->setBody($body);
    }

    public function productAction()
    {
        $this->_importAction('csv_product');
    }

    public function productAttributeAction()
    {
        $this->_importAction('csv_product_attribute');
    }
}