<?php
class Icommerce_OrderStatus_Model_Config_Status_Processing
{
    protected $_stateStatuses = array(
        Mage_Sales_Model_Order::STATE_PROCESSING,
    );

    public function toOptionArray()
    {
        Mage::helper('orderstatus')->initStatuses();
        if ($this->_stateStatuses) {
            $statuses = Mage::getSingleton('sales/order_config')->getStateStatuses($this->_stateStatuses);
        }
        else {
            $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
        }
        $options = array();
        $options[] = array(
        	   'value' => '',
        	   'label' => Mage::helper('adminhtml')->__('-- Please Select --')
        	);
        foreach ($statuses as $code=>$label) {
        	$options[] = array(
        	   'value' => $code,
        	   'label' => $label
        	);
        }
        return $options;
    }
}
