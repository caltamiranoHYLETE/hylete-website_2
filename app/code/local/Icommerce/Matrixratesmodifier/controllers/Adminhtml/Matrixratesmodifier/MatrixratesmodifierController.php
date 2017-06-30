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
 * @package     Icommerce_Matrixratesmodifier
 * @copyright   Copyright (c) 2009-2011 Icommerce Nordic AB
 */
class Icommerce_Matrixratesmodifier_Adminhtml_Matrixratesmodifier_MatrixratesmodifierController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/matrixratesmodifier_adminform');
    }

    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('system/matrixratesmodifier');
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('matrixratesmodifier/adminhtml_matrixratesmodifier'));
        $this->renderLayout();
    }

    public function editAction()
    {
        $helper = Mage::helper('matrixratesmodifier');
        $matrixratesmodifierId = $this->getRequest()->getParam('id');
        $matrixratesmodifierModel = Mage::getModel('matrixratesmodifier/matrixratesmodifier')->load($matrixratesmodifierId);

        if ($matrixratesmodifierModel->getId() || $matrixratesmodifierId == 0) {

            // Set proper path
            if ($matrixratesmodifierModel->getLogo()) {
                $matrixratesmodifierModel->setLogo($helper->getLogoBaseUrl() . $matrixratesmodifierModel->getLogo());
            }

            Mage::register('matrixratesmodifier_data', $matrixratesmodifierModel);

            $this->_initAction();

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('matrixratesmodifier/adminhtml_matrixratesmodifier_edit'))
                ->_addLeft($this->getLayout()->createBlock('matrixratesmodifier/adminhtml_matrixratesmodifier_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('matrixratesmodifier')->__('The rate does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        $helper = Mage::helper('matrixratesmodifier');

        if ($this->getRequest()->getPost()) {
            try {
                $request = $this->getRequest();
                /** @var Icommerce_Matrixratesmodifier_Model_Matrixratesmodifier $matrixratesmodifierModel */
                $matrixratesmodifierModel = Mage::getModel('matrixratesmodifier/matrixratesmodifier');

                $matrixratesmodifierModel->setId($request->getParam('id'))
                    ->setWebsiteId((int)$request->getParam('website_id'))
                    ->setDestCountryId($request->getParam('dest_country_id'))
                    ->setDestRegionId((int)$request->getParam('dest_region_id'))
                    ->setDestZip(str_replace(" ", "", $request->getParam('dest_zip')))
                    ->setDestZipTo(str_replace(" ", "", $request->getParam('dest_zip_to')))
                    ->setConditionName($request->getParam('condition_name'))
                    ->setConditionFromValue($request->getParam('condition_from_value'))
                    ->setConditionToValue($request->getParam('condition_to_value'))
                    ->setPrice($request->getParam('price'))
                    ->setCost($request->getParam('cost'))
                    ->setDeliveryType($request->getParam('delivery_type'))
                    ->setDescription($request->getParam('description'))
                    ->setShortDescription($request->getParam('short_description', ''));

                if (Icommerce_Db::columnExists("shipping_matrixrate", "code")) {
                    $matrixratesmodifierModel->setCode($request->getParam('code'));
                }
                if (Icommerce_Default_Helper_Data::isModuleActive('Icommerce_MatrixrateExtended') && Icommerce_Db::columnExists('shipping_matrixrate', 'freightcat')) {
                    $matrixratesmodifierModel->setFreightcat($request->getParam('freightcat'));
                }
                /* Handle logo upload */
                if ($helper->showLogoField() && isset($_FILES['logo']) &&  $_FILES['logo']['size'] > 0) {
                    $uploader = new Mage_Core_Model_File_Uploader('logo');
                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(true);

                    $fileResult = $uploader->save(
                        $helper->getLogoDir()
                    );

                    $matrixratesmodifierModel->setLogo($fileResult['file']);
                }

                /*
                    Handle logo delete
                    See also Icommerce_Matrixratesmodifier_Model_Matrixratesmodifier _beforeDelete()
                */
                if ($request->getParam('logo')) {
                    $logoParams = $request->getParam('logo');

                    if (isset($logoParams['delete']) && $logoParams['delete'] == '1') {
                        $logo = str_replace($helper->getLogoBaseUrl(), '', $logoParams['value']);
                        $logo = $helper->getLogoDir() . $logo;

                        if (file_exists($logo)) {
                            unlink($logo);
                            $matrixratesmodifierModel->setLogo(null);
                        }
                    }
                }

                Mage::dispatchEvent('matrixratemodifier_save_rate_before',array('request' => $request, 'rate_model' => $matrixratesmodifierModel ));

                $matrixratesmodifierModel->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The rate has been saved.'));
                Mage::getSingleton('adminhtml/session')->setMatrixratesmodifierData(false);

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setMatrixratesmodifierData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $matrixratesmodifierModel = Mage::getModel('matrixratesmodifier/matrixratesmodifier');

                $matrixratesmodifierModel->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The rate has been deleted.'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Product grid for AJAX request.
     * Sort and filter result for example.
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('importedit/adminhtml_matrixratesmodifier_grid')->toHtml()
        );
    }
}
