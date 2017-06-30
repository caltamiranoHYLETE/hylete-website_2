<?php

class Icommerce_PdfCustomiser_Model_Order extends Icommerce_PdfCustomiser_Model_Abstract
{
    /**
    * Creates PDF using the tcpdf library from array of orderIds
    * @param array $invoices, $orderIds
    * @access public
    */
    public function getPdf($ordersGiven = array(),$orderIds = array(), $pdf = null, $suppressOutput = false)
    {
        //check if there is anything to print
		if(empty($pdf) && empty($ordersGiven) && empty($orderIds)){
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('There are no printable documents related to selected orders'));
			return false;
		}

        $this->order_ids_before = $orderIds;
        if(!empty($ordersGiven)){
            foreach ($ordersGiven as $orderGiven) {
                $this->order_ids_before[] = $orderGiven->getId();
            }
        }

        //need to get the store id from the first order to initialise pdf
        $this->_current_order_store_id = $order = Mage::getModel('sales/order')->load($this->order_ids_before[0])->getStoreId();

        $this->_beforeGetPdf();

        if(empty($pdf)){
            $pdf = $this->getPdfObject($this->_current_order_store_id);
        }

        foreach ($this->order_ids_before as $orderId) {
            //load data
            $order = Mage::getModel('sales/order')->load($orderId);

            if ($order->getStoreId()) {
                Mage::app()->getLocale()->emulate($order->getStoreId());
            }

            /** @var Icommerce_PdfCustomiser_Helper_Order $orderHelper */
            $orderHelper = Mage::helper('pdfcustomiser/order');

            $storeId = $order->getStoreId();
            if ($order->getStoreId()) {
                Mage::app()->getLocale()->emulate($order->getStoreId());
            }

            $orderHelper->setStoreId($storeId);
            // set standard pdf info
            $pdf->SetStandard($orderHelper);

            // add a new page
            $pdf->AddPage();

            $this->_processOrderPdfHeader($pdf, $order, $orderHelper);

            // Output heading for Items
            switch(Mage::getStoreConfig('sales_pdf/all/allpagesize',$storeId)){
                case 'A4':
                    $units = (595 - 2.83*2*$orderHelper->getPdfMargins('sides'))/10;
                    break;
                case 'LETTER':
                    $units = (612.00 - 2.83*2*(float)$orderHelper->getPdfMargins('sides'))/10;
                    break;
            }
            $tbl ='<table border="0" cellpadding="2" cellspacing="0">';
            $tbl.='<thead>';
            $tbl.='<tr>';
                $tbl.='<th width="'.(3*$units).'"><strong>'.Mage::helper('pdfcustomiser')->__('Product').'</strong></th>';
                $tbl.='<th width="'.(2*$units).'"><strong>'.Mage::helper('pdfcustomiser')->__('Article Number').'</strong></th>';
                $tbl.='<th width="'.(1.25*$units).'" align="center"><strong>'.Mage::helper('pdfcustomiser')->__('Price').'</strong></th>';
                $tbl.='<th width="'.(1.25*$units).'" align="center"><strong>'.Mage::helper('pdfcustomiser')->__('QTY').'</strong></th>';
                $tbl.='<th width="'.(1.25*$units).'" align="center"><strong>'.Mage::helper('pdfcustomiser')->__('Tax').'</strong></th>';
                $tbl.='<th width="'.(1.25*$units).'" align="right"><strong>'.Mage::helper('pdfcustomiser')->__('Sub Total').'</strong></th>';
            $tbl.='</tr>';
            $tbl.='<tr><td width="'.(10*$units).'" colspan="6"><hr style="width:10px;"/></td></tr>';
            $tbl.='</thead>';

            // Prepare Line Items
            $pdfItems = array();
            $pdfBundleItems = array();
            $pdf->prepareLineItems($orderHelper,$order->getAllItems(),$pdfItems,$pdfBundleItems);

            //Display both currencies if flag is set and order is in a different currency
            $displayBoth = $orderHelper->getDisplayBoth() && $order->isCurrencyDifferent();

            //Output Line Items
            $pdf->SetFont($orderHelper->getPdfFont(), '', $orderHelper->getPdfFontsize('small'));
            foreach ($pdfItems as $pdfItem){

                //we generallly don't want to display subitems of configurable products etc
                if($pdfItem['parentItemId']){
                        continue;
                }

                //Output line items


                if ($pdfItem['parentType'] != 'bundle' && $pdfItem['type'] != 'bundle') {
                    $tbl.='<tr>';
                        $tbl.='<td width="'.(3*$units).'">'.str_replace("&amp;quot;", "&quot;", $pdfItem['productDetails']['Name']).'</td>';
                        $tbl.='<td width="'.(2*$units).'">'.$pdfItem['productDetails']['Sku'].'</td>';
                        $tbl.='<td width="'.(1.25*$units).'" align="center">'.$pdf->OutputPrice($pdfItem['price'],$pdfItem['basePrice'],$displayBoth,$order).'</td>';
                        $tbl.='<td width="'.(1.25*$units).'" align="center">'.$pdfItem['qty'].'</td>';
                        $tbl.='<td width="'.(1.25*$units).'" align="center">'.$pdf->OutputPrice($pdfItem['taxAmount'],$pdfItem['baseTaxAmount'],$displayBoth,$order).'</td>';
                        $tbl.='<td width="'.(1.25*$units).'" align="right">'.$pdf->OutputPrice($pdfItem['rowTotal'],$pdfItem['baseRowTotal'],$displayBoth,$order).'</td>';
                    $tbl.='</tr>';

                } else {    //Deal with Bundles
                    //check if the subitems of the bundle have separate prices
                    $currentParentId =$pdfItem['itemId'];
                    $subItemsSum = 0;
                    if (isset($pdfBundleItems[$currentParentId])){
                        foreach ($pdfBundleItems[$currentParentId] as $bundleItem){
                            $subItemsSum += $bundleItem['price'];
                        }
                    }
                    //don't display bundle price if subitems have prices
                    if( $subItemsSum > 0){
                        $tbl.='<tr>';
                            $tbl.='<td width="'.(3*$units).'">'.$pdfItem['productDetails']['Name'].'</td>';
                            $tbl.='<td width="'.(7*$units).'">'.$pdfItem['productDetails']['Sku'].'</td>';
                        $tbl.='</tr>';
                        //Display subitems
                        foreach ($pdfBundleItems[$currentParentId] as $bundleItem){
                            $tbl.='<tr>';
                                $tbl.='<td width="'.(3*$units).'">&nbsp;&nbsp;&nbsp;&nbsp;'.str_replace("&amp;quot;", "&quot;", $bundleItem['productDetails']['Name']).'</td>';
                                $tbl.='<td width="'.(2*$units).'">'.$bundleItem['productDetails']['Sku'].'</td>';
                                $tbl.='<td width="'.(1.25*$units).'" align="center">'.$pdf->OutputPrice($bundleItem['price'],$bundleItem['basePrice'],$displayBoth,$order).'</td>';
                                $tbl.='<td width="'.(1.25*$units).'" align="center">'.$bundleItem['qty'].'</td>';
                                $tbl.='<td width="'.(1.25*$units).'" align="center">'.$pdf->OutputPrice($bundleItem['taxAmount'],$bundleItem['baseTaxAmount'],$displayBoth,$order).'</td>';
                                $tbl.='<td width="'.(1.25*$units).'" align="right">'.$pdf->OutputPrice($bundleItem['rowTotal'],$bundleItem['baseRowTotal'],$displayBoth,$order).'</td>';
                            $tbl.='</tr>';
                        }
                    }else {


			if(isset($pdfBundleItems[$currentParentId]))
			{
			    foreach ($pdfBundleItems[$currentParentId] as $bundleItem)
			    {
				$pdfItem['productDetails']['Name'] .= "<br/>&nbsp;&nbsp;&nbsp;&nbsp;".$bundleItem['qty']." x " .$bundleItem['productDetails']['Name'];
			    }
			}

                        $tbl.='<tr>';
                            $tbl.='<td width="'.(3*$units).'">'.$pdfItem['productDetails']['Name'].'</td>';
                            $tbl.='<td width="'.(2*$units).'">'.$pdfItem['productDetails']['Sku'].'</td>';
                            $tbl.='<td width="'.(1.25*$units).'" align="center">'.$pdf->OutputPrice($pdfItem['price'],$pdfItem['basePrice'],$displayBoth,$order).'</td>';

			    if(isset($bundleItem['qty']))
				$tbl.='<td width="'.(1.25*$units).'" align="center">'.$bundleItem['qty'].'</td>';

			    $tbl.='<td width="'.(1.25*$units).'" align="center">'.$pdf->OutputPrice($pdfItem['taxAmount'],$pdfItem['baseTaxAmount'],$displayBoth,$order).'</td>';
                            $tbl.='<td width="'.(1.25*$units).'" align="right">'.$pdf->OutputPrice($pdfItem['rowTotal'],$pdfItem['baseRowTotal'],$displayBoth,$order).'</td>';
                        $tbl.='</tr>';
                    }
                }
                $tbl.='<tcpdf method="Line2" params=""/>';
            }
            $tbl.='</table>';
            $pdf->writeHTML($tbl, true, false, false, false, '');
            $pdf->SetFont($orderHelper->getPdfFont(), '', $orderHelper->getPdfFontsize());

            //reset Margins in case there was a page break
            $pdf->setMargins($orderHelper->getPdfMargins('sides'),$orderHelper->getPdfMargins('top'));

            // Output totals
            $pdf->OutputTotals($orderHelper, $order,$order);

            // Output Order Gift Message
            $pdf->OutputGiftMessage($orderHelper, $order);

            // Output Comments
            $pdf->OutputComment($orderHelper,$order);

            //Custom Blurb underneath
            $pdf->Ln(2);
            $pdf->writeHTMLCell(0, 0, null, null,$orderHelper->getPdfOrderCustom(), null,1);
            if ($order->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
            $pdf->setPdfAnyOutput(true);
        }

        // reset pointer to the last page
        if ($pdf->getNumPages()) {
            $pdf->lastPage();
        }

        //output PDF document
        if(!$suppressOutput){
			if($pdf->getPdfAnyOutput()){
				$pdf->Output('order_'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', 'I');
				exit;
			}else{
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('There are no printable documents related to selected orders'));
			}
        }

        $this->_afterGetPdf();

        return $pdf;
    }

   /**
    * Creates Picking List PDF using the tcpdf library from array of orderIds
    * @param array $invoices, $orderIds
    * @access public
    */
    public function getPicking($ordersGiven = array(),$orderIds = array(), $pdf = null, $suppressOutput = false)
    {

        //check if there is anything to print
		if(empty($pdf) && empty($ordersGiven) && empty($orderIds)){
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('There are no printable documents related to selected orders'));
			return false;
		}

        //we will be working through an array of orderIds later - fill it up if only $ordersGiven is available
        if(!empty($ordersGiven)){
            foreach ($ordersGiven as $orderGiven) {
                    $orderIds[] = $orderGiven->getId();
            }
        }

        $this->_beforeGetPdf();

        $storeId = $order = Mage::getModel('sales/order')->load($orderIds[0])->getStoreId();

        //work with a new pdf or add to existing one
        if(empty($pdf)){
            $pdf = $this->getPdfObject($storeId);
        }

        /** @var Icommerce_PdfCustomiser_Helper_Order $orderHelper */
        $orderHelper = Mage::helper('pdfcustomiser/order');
        $orderHelper->setStoreId($storeId);
        $pdf->SetStandard($orderHelper);
        $pdf->setHeaderData('', null, 'Picking List', time());
        $pdf->setPrintHeader(true);

        $pdf->AddPage();


        // Output heading for Items
        switch(Mage::getStoreConfig('sales_pdf/all/allpagesize',$storeId)){
            case 'A4':
                $units = (595 - 2.83*2*$orderHelper->getPdfMargins('sides'))/10;
                break;
            case 'LETTER':
                $units = (612.00 - 2.83*2*(float)$orderHelper->getPdfMargins('sides'))/10;
                break;
        }

        foreach ($orderIds as $orderId) {
            //load data
            $tbl ='<table border="0" cellpadding="2" cellspacing="0">';
            $order = Mage::getModel('sales/order')->load($orderId);

            if ($order->getStoreId()) {
                Mage::app()->getLocale()->emulate($order->getStoreId());
            }

            $order->load($orderId);
            $storeId = $order->getStoreId();
            if ($order->getStoreId()) {
                Mage::app()->getLocale()->emulate($order->getStoreId());
            }

            // Prepare Line Items
            $bundleItems = array();
            $pdfBundleItems = array();
            $pdf->prepareLineItems($orderHelper,$order->getAllItems(),$bundleItems,$pdfBundleItems);

            $orderNumber = Mage::helper('pdfcustomiser')->__('Billing Address only'). $order->getIncrementId()."\n";

            $tbl.='<tr><td colspan="3" width="'.(10*$units).'"><strong>'.$orderNumber.'</strong> '.Mage::helper('core')->formatDate($order->getCreatedAt(), 'medium', false).'</td></tr>';

            //Output Line Items
            foreach ($bundleItems as $bundleItem){

                //we generallly don't want to display subitems of configurable products etc
                if($bundleItem['parentId']){
                    continue;
                }

                //Output line items
                if ($bundleItem['parentType'] != 'bundle' && $bundleItem['type'] != 'bundle') {
                    $tbl.='<tr>';
                    $tbl.='<td width="'.(5*$units).'">'.$bundleItem['productDetails']['Name'].'</td>';
                    $tbl.='<td width="'.(2.5*$units).'">'.$bundleItem['productDetails']['Sku'].'</td>';
                    $tbl.='<td width="'.(2.5*$units).'" align="center">'.$bundleItem['qty'].'</td>';
                    $tbl.='</tr>';

                } else {    //Deal with Bundles
                    //check if the subitems of the bundle have separate prices
                    $currentParentId =$bundleItem['itemId'];
                    $subItemsSum = 0;
                    foreach ($pdfBundleItems[$currentParentId] as $bundleItem){
                        $subItemsSum += $bundleItem['price'];
                    }
                    //don't display bundle price if subitems have prices
                    if( $subItemsSum > 0){
                        $tbl.='<tr>';
                        $tbl.='<td width="'.(5*$units).'">'.$bundleItem['productDetails']['Name'].'</td>';
                        $tbl.='<td colspan="2" width="'.(5*$units).'">'.$bundleItem['productDetails']['Sku'].'</td>';
                        $tbl.='</tr>';
                        //Display subitems
                        foreach ($pdfBundleItems[$currentParentId] as $bundleItem){
                            $tbl.='<tr>';
                            $tbl.='<td width="'.(5*$units).'">&nbsp;&nbsp;&nbsp;&nbsp;'.$bundleItem['productDetails']['Name'].'</td>';
                            $tbl.='<td width="'.(2.5*$units).'">'.$bundleItem['productDetails']['Sku'].'</td>';
                            $tbl.='<td width="'.(2.5*$units).'" align="center">'.$bundleItem['qty'].'</td>';
                            $tbl.='</tr>';
                        }
                    }else {
                        foreach ($pdfBundleItems[$currentParentId] as $bundleItem){
                            $bundleItem['productDetails']['Name'] .= "<br/>&nbsp;&nbsp;&nbsp;&nbsp;".$bundleItem['qty']." x " .$bundleItem['productDetails']['Name'];
                        }
                        $tbl.='<tr>';
                        $tbl.='<td width="'.(5*$units).'">'.$bundleItem['productDetails']['Name'].'</td>';
                        $tbl.='<td width="'.(2.5*$units).'">'.$bundleItem['productDetails']['Sku'].'</td>';
                        $tbl.='<td width="'.(2.5*$units).'" align="center">'.$bundleItem['qty'].'</td>';
                        $tbl.='</tr>';
                    }
                }
            }
            $tbl.='<tr><td colspan="3" width="'.(10*$units).'"><br/><strong>'.Mage::helper('shipping')->__('Weight:').'</strong> '.$order->getWeight().'<br/>'
                        .'<strong>'.Mage::helper('pdfcustomiser')->__('Shipping & Handling:').':</strong> '. $order->formatPriceTxt($order->getShippingAmount()).'<br/>'
                        .'<strong>'.Mage::helper('pdfcustomiser')->__('Grand Total:').':</strong> '. $order->formatPriceTxt($order->getSubtotal()).'<br/>';
            $tbl.='<hr style="width:5px;"/></td></tr>';

            if ($order->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
            $tbl.='</table>';
            $pdf->writeHTML($tbl, true, false, false, false, '');
            $pdf->setPdfAnyOutput(true);
        }



        // reset pointer to the last page
        if ($pdf->getNumPages()) {
            $pdf->lastPage();
        }

        //output PDF document
        if(!$suppressOutput){
			if($pdf->getPdfAnyOutput()){
				$pdf->Output('order_'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', 'I');
				exit;
			}else{
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('There are no printable documents related to selected orders'));
			}
        }

        $this->_afterGetPdf();

        return $pdf;
    }

    protected function _processOrderPdfHeader($pdf, $order, $orderHelper)
    {
        $pdf->printHeader($orderHelper, $orderHelper->getPdfOrderTitle());

        #$orderNumbersEtc = Mage::helper('pdfcustomiser')->__('List of shipments ready for download: %s','<a href="'.Mage::getStoreConfig('web/secure/base_url',Mage::app()->getStore()->getId()).'var/export/'.$session->getCsvFilename().'">'.$session->getCsvFilename().'</a>');
        #$orderNumbersEtc = Mage::helper('pdfcustomiser')->__('Ordernr: '). $order->getIncrementId()."\n";

        $orderNumbersEtc  = Mage::helper('pdfcustomiser')->__('Ordernr: ').$order->getIncrementId()."\n";
        $orderNumbersEtc .= Mage::helper('pdfcustomiser')->__('Date').': '.Mage::helper('core')->formatDate($order->getCreatedAt(), 'medium', false)."\n";
        $pdf->MultiCell($pdf->getPageWidth() / 2 - $orderHelper->getPdfMargins('sides'), 0, $orderNumbersEtc, 0, 'L', 0, 0);
        $pdf->MultiCell($pdf->getPageWidth() / 2 - $orderHelper->getPdfMargins('sides'), $pdf->getLastH(), $orderHelper->getPdfOwnerAddresss(), 0, 'L', 0, 1);
        $pdf->Ln(5);

        //add billing and shipping addresses
        $pdf->OutputCustomerAddresses($orderHelper, $order, $orderHelper->getPdfOrderAddresses());

        // Output Shipping and Payment
        $pdf->OutputPaymentAndShipping($orderHelper, $order,$order);
    }
}

/**
 * Class Icommerce_PdfCustomiser_Order
 * @deprecated since version 1.2.32, please use instead Mage::helper('pdfcustomiser/order');
 */
class Icommerce_PdfCustomiser_Order extends Icommerce_PdfCustomiser_Helper_Order
{
}
