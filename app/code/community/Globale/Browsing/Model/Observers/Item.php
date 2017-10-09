<?php
class Globale_Browsing_Model_Observers_Item {


	/**
	 * Change Prices when add to quote
	 * Event => sales_quote_collect_totals_before
	 * @param Varien_Event_Observer $observer
	 */
	public function updateQuoteTotals(Varien_Event_Observer $observer){
		/**@var $ItemModel Globale_Browsing_Model_Item */
		$ItemModel = Mage::getModel('globale_browsing/item');
		$ItemModel->updateQuoteTotals($observer);

	}
}