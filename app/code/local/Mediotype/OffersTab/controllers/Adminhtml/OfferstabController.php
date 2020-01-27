<?php
/**
 * Class OfferController
 *
 * @author Myles Forrest <myles@mediotype.com>
 */

class Mediotype_OffersTab_Adminhtml_OfferstabController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Offers Tab'))->_title($this->__('Offers Tab'));
        $this->loadLayout();
        $this->_setActiveMenu('promo/mediotype_offerstab');
        $this->_addContent(
            $this->getLayout()
                ->createBlock('mediotype_offerstab/adminhtml_offerstab')
        );
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()
            ->setBody(
                $this->getLayout()
                    ->createBlock('mediotype_offerstab/adminhtml_offerstab')
                    ->toHtml()
            );
    }

    public function editAction()
    {
        $this->_title('Edit Offers');
        $this->_loadLayout();
        $this->_addContent($this->getLayout()->createBlock('mediotype_offerstab/adminhtml_offerstab_edit')->setData('action', $this->getUrl('*/*/save')));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            if ($id) {
                $resource = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');
                $result = $writeConnection->delete("mediotype_offerstab_offers", "offer_id='" . $id . "'");
                if ($result) {
                    $this->_getSession()->addSuccess($this->__('Total of %d record(s) have been deleted.', count($id)));
                }
            }
        }
        $this->getResponse()
            ->setRedirect($this->getUrl('*/*/', array('store' => $this->getRequest()->getParam('store'))));
    }

    public function saveAction()
    {
        if ($postData = $this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('id');
            $model = Mage::getModel('mediotype_offerstab/offer');
            if ($id) {
                $model->load($id);
            }

            if(isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {

                try {

                    $uploader = new Varien_File_Uploader('image');

                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png')); // or pdf or anything

                    $uploader->setAllowRenameFiles(false);

                    $uploader->setFilesDispersion(false);

                    $path = Mage::getBaseDir('media') . '/offerstab/' ;
                    $uploader->save($path, $_FILES['image']['name']);
                    $postData['image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) ."offerstab/". $_FILES['image']['name'];
                }catch(Exception $e) {



                }
            }else{
                unset($postData['image']);
            }

            $model->addData($postData);
            try {
                $model->save();
                Mage::dispatchEvent('mediotype_offers_tab_save_after', array('mediotype_offerstab_offer' => $model)  );
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('mediotype_offerstab')->__('Successfully saved.')
                );
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('mediotype_offerstab')->__('An error occurred while saving.')
                );
            }
            $this->_redirectReferer();
        }
    }

    /**
     * @return \Mage_Adminhtml_Controller_Action
     */
    protected function _loadLayout()
    {
        return $this->loadLayout()
            ->_setActiveMenu('promo/mediotype_offerstab');
    }

    protected function _isAllowed()
    {
        return true;
    }
}