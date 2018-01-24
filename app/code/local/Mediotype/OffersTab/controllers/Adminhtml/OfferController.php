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

		if (!empty($offerData['offer_id'])) { // Saving an extant Offer
			// Load
			$model->load($offerData['offer_id']);

			if (!$model->getId()) {
				Mage::getSingleton('adminhtml/session')->addError($this->__('That Offer does not exist.'));
			}

			$model->setData($offerData);
			$model->save();

		} else { // Saving a new Offer
			// Create
			$model->setData($offerData);
			$model->setData("offer_id", null);
			$model->setData("created_time", time());
			$model->setData("update_time", time());
			$model->save();
		}

		$this->_redirect('*/*/edit', array('id' => $model->getId()));
	}

	/**
	 *
	 */
	public function deleteAction()
	{
	}

	/**
	 * @return bool
	 */
	protected function _isAllowed()
	{
		return true;
	}
}
