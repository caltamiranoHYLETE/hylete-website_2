<?php

class Icommerce_OrderStatus_Helper_Data extends Mage_Payment_Helper_Data
{
	static function getStateName($state)
	{
		switch ($state) {
			case Mage_Sales_Model_Order::STATE_NEW:				return("STATE_NEW"); break;
			case Mage_Sales_Model_Order::STATE_PENDING_PAYMENT:	return("STATE_PENDING_PAYMENT"); break;
			case Mage_Sales_Model_Order::STATE_PROCESSING:		return("STATE_PROCESSING"); break;
			case Mage_Sales_Model_Order::STATE_COMPLETE:		return("STATE_COMPLETE"); break;
			case Mage_Sales_Model_Order::STATE_CLOSED:			return("STATE_CLOSED"); break;
			case Mage_Sales_Model_Order::STATE_CANCELED:		return("STATE_CANCELED"); break;
			case Mage_Sales_Model_Order::STATE_HOLDED:			return("STATE_HOLDED"); break;
		}
		return("STATE_UNKNOWN");
	}
	
	static function logIsStatusInState($status,$state)
	{
		if (!$state) return;
		$shouldbestate = NULL;
		switch ($status) {
			case "pending":
			case "reserved":
				$shouldbestate = Mage_Sales_Model_Order::STATE_NEW;
				break;
			case "pay_pending":
			case "pending_paypal":
			case "pending_amazon_asp":
				$shouldbestate = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
				break;
			case "processing":
			case "captured":
				$shouldbestate = Mage_Sales_Model_Order::STATE_PROCESSING;
				break;
			case "complete":
				$shouldbestate = Mage_Sales_Model_Order::STATE_COMPLETE;
				break;
			case "closed":
				$shouldbestate = Mage_Sales_Model_Order::STATE_CLOSED;
				break;
			case "canceled":
			case "pay_failed":
			case "pay_aborted":
				$shouldbestate = Mage_Sales_Model_Order::STATE_CANCELED;
				break;
			case "holded":
			case "holded_prepayment":
			case "holded_supplier":
				$shouldbestate = Mage_Sales_Model_Order::STATE_HOLDED;
				break;
		}
		if ($shouldbestate) {
			if ($state!=$shouldbestate) {
				Icommerce_Default::logAppend( "Setting status '" . $status . "' while in " . self::getStateName($state) . " should only be used in " . self::getStateName($shouldbestate), "var/orderstatus/messages.log" );
			}
		} else {
			Icommerce_Default::logAppend( "MISSING CHECK for status '" . $status . "' currently in " . self::getStateName($state), "var/orderstatus/messages.log" );
		}
	}
	
	static function getStatus($reqstatus,$state = NULL)
	{
		$status = $reqstatus;
		if ($status=="") {
			switch ($state) {
				case Mage_Sales_Model_Order::STATE_NEW: $status = "pending"; break; // Magento Standard
				case Mage_Sales_Model_Order::STATE_PENDING_PAYMENT: $status = "pay_pending"; break;
				case Mage_Sales_Model_Order::STATE_PROCESSING: $status = "processing"; break; // Magento Standard
				case Mage_Sales_Model_Order::STATE_COMPLETE: $status = "complete"; break; // Magento Standard
				case Mage_Sales_Model_Order::STATE_CLOSED: $status = "closed"; break; // Magento Standard
				case Mage_Sales_Model_Order::STATE_CANCELED: $status = "pay_failed"; break;
				case Mage_Sales_Model_Order::STATE_HOLDED: $status = "holded"; break; // Magento Standard
			}
			Icommerce_Default::logAppend( "Trying to set blank status while in " . self::getStateName($state) . ", defaulting to '" . $status . "'", "var/orderstatus/messages.log" );
		}
		self::logIsStatusInState($status,$state);
		return $status;
	}
	
    // This was added ages ago, I just never commited... now I can't remember what it was for... I know it initializes some globals... but which ones...
    static function initStatuses()
    {
        $sourceModel = Mage::getSingleton('adminhtml/system_config_source_order_status');
        $optionArray = $sourceModel->toOptionArray(false);
    }
}