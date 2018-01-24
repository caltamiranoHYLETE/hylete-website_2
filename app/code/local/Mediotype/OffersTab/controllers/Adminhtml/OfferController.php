<?php

/**
 * Class OfferController
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Adminhtml_OfferController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * @return $this
	 */
	protected function _initAction()
	{
		$this->loadLayout()
			->_setActiveMenu('promo/mediotype_offerstab')
			->_title($this->__('Offers Tab'))->_title($this->__('Offers Tab'));

		return $this;
	}

	/**
	 *
	 */
	public function indexAction()
	{
		$this->_initAction();

		$this->_addContent(
			$this->getLayout()->createBlock('mediotype_offerstab/adminhtml_promo_offerstab')
		);

		$this->renderLayout();
	}

	/**
	 *
	 */
	public function newAction()
	{
		$this->_forward('edit');
	}

	/**
	 *
	 */
	public function editAction()
	{
		$id = $this->getRequest()->getParam('id');
		$model = Mage::getModel('mediotype_offerstab/offer');

		if ($id) {
			$model->load($id);

			if (!$model->getId()) {
				Mage::getSingleton('adminhtml/session')->addError($this->__('That Offer no longer exists.'));
				$this->_redirect('*/*/');

				return;
			}
		}

		$this->_title($model->getId() ? $model->getTitle() : $this->__('New'));

		$this->_initAction()
			->_addContent($this->getLayout()->createBlock('mediotype_offerstab/adminhtml_form_edit_form')->setData('action', $this->getUrl('*/*/save'))->setData('offer', $model))
			->renderLayout();
	}

	/**
	 *
	 */
	public function saveAction()
	{
		$model = Mage::getModel('mediotype_offerstab/offer');

		// Get params
		$params = $this->getRequest()->getParams();
		$offerData = $params["offer"];
		$id = $this->getRequest()->getParam('id');

		if (!empty($id)) { // Saving an extant Offer
			// Load
			$model->load($id);

			if (!$model->getId()) {
				Mage::getSingleton('adminhtml/session')->addError($this->__('That Offer does not exist.'));
			}

			// Apply data, set update timestamp
			$model->addData($offerData);
			$model->setData("update_time", time());

			$model->save();

			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Offer saved.'));

		} else { // Saving a new Offer
			// Create and apply our data
			$model->setData($offerData);

			// Set timestamps
			$model->setData("created_time", time());
			$model->setData("update_time", time());

			$model->save();

			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Offer saved.'));
		}

		$this->_redirect('*/*/edit', array('id' => $model->getId()));
	}

	/**
	 *
	 */
	public function deleteAction()
	{
		// If it was deleted, provide a message
		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Offer was deleted.'));

		$this->_redirect('*/*/');
	}

	/**
	 * @return bool
	 */
	protected function _isAllowed()
	{
		return true;
	}
}
