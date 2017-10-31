<?php

class Globale_Base_Model_Observers_Admin
{
    /**
     * Joins extra tables for adding Global-e Order Id to Mage_Adminhtml_Block_Sales_Order_Grid
	 * EVENT ==> sales_order_grid_collection_load_before
     * @param Varien_Event_Observer $Observer
     * @internal param $observer
     */
    public function salesOrderGridCollectionLoadBefore(Varien_Event_Observer $Observer){

    	//Apply Grid changes on main order grid only
		$OriginalPathInfo = Mage::app()->getRequest()->getOriginalPathInfo();
		if (strpos($OriginalPathInfo, '/sales_order/') === false) {
			return;
		}

        $Collection = $Observer->getOrderGridCollection();
        $Select = $Collection->getSelect();
        $Select->joinLeft(
            array(
                'globale'=>$Collection->getTable('globale_order/orders')
            ),
            'globale.order_id=main_table.increment_id',
            array(
                'globale_order_id'=>'globale_order_id'
            )
        );

    }

    /**
     * Get Global-e Barcode URL and open a new window to print it.
     * @return $this
     */
    public function addBarcodeButton(){
        $Block = Mage::app()->getLayout()->getBlock('sales_order_edit');
        if (!$Block){
            return $this;
        }
        $Order = Mage::registry('current_order');
        $GeOrder = Mage::getModel('globale_order/orders')->load($Order->getIncrementId(),'order_id');
        $GeOrderId = $GeOrder->getGlobaleOrderId();
        if(empty($GeOrderId)){
            return $this;
        }

        /** @var GlobalE\SDK\SDK $GlobaleSDK */
        $GlobaleSDK = Mage::registry('globale_sdk');
        $BarCode = $GlobaleSDK->Admin()->GetBarCodeUrl($GeOrderId);

		if($BarCode->getData() == ''){
			$printBarCodeWindows = "var printBarcodeWindow = window.open('','Print Barcode - GlobalE Order {$GeOrderId}','width=400, height=100, scrollbars=0, toolbar=0, status=0, titlebar=0'); printBarcodeWindow.document.write('<p>No Barcode URL provided ,Please Contact Global-e</p>');";
		}else{
			$printBarCodeWindows = "window.open('{$BarCode->getData()}', 'Print Barcode - GlobalE Order {$GeOrderId}', 'width=400, height=200, scrollbars=0, toolbar=0, status=0, titlebar=0')";
		}

        if($BarCode->getSuccess()){
            $Block->addButton('globale_barcode', array(
                'label'     => Mage::helper('sales')->__('Print Global-e BarCode'),
                'onclick'   => $printBarCodeWindows,
                'class'     => 'go'
            ));
        }

        return $this;
    }

}