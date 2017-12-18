<?php

require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Customer' . DS . 'GroupController.php';

/**
 * Class Mediotype_HyletePrice_Adminhtml_Customer_GroupController
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_HyletePrice_Adminhtml_Customer_GroupController extends Mage_Adminhtml_Customer_GroupController
{
	/**
	 * Rewritten to save the CustomerGroupHyletePriceLabel as well.
	 */
	public function saveAction()
	{
		$customerGroup = Mage::getModel('customer/group');
		$id = $this->getRequest()->getParam('id');
		if (!is_null($id)) {
			$customerGroup->load((int)$id);
		}

		$taxClass = (int)$this->getRequest()->getParam('tax_class');

		if ($taxClass) {
			try {
				$customerGroupCode = (string)$this->getRequest()->getParam('code');

				if (!empty($customerGroupCode)) {
					$customerGroup->setCode($customerGroupCode);
				}

				$customerGroupHyletePriceLabel = (string)$this->getRequest()->getParam('customer_group_hylete_price_label');

				if (!empty($customerGroupHyletePriceLabel)) {
					$customerGroup->setCustomerGroupHyletePriceLabel($customerGroupHyletePriceLabel);
				}

				$customerGroup->setTaxClassId($taxClass)->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customer')->__('The customer group has been saved.'));
				$this->getResponse()->setRedirect($this->getUrl('*/customer_group'));
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setCustomerGroupData($customerGroup->getData());
				$this->getResponse()->setRedirect($this->getUrl('*/customer_group/edit', array('id' => $id)));
				return;
			}
		} else {
			$this->_forward('new');
		}
	}
}