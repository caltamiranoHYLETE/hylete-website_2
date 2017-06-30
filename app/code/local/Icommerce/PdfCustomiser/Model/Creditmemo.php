<?php

class Icommerce_PdfCustomiser_Model_Creditmemo extends Icommerce_PdfCustomiser_Model_Abstract
{
    /**
    * Creates PDF using the tcpdf library from array of creditmemos or orderIds
    * @param array creditmemosGiven, $orderIds
    * @access public
    */
    public function getPdf($creditmemosGiven = array(),$orderIds = array(), $pdf = null, $suppressOutput = false, $outputFileName ='creditmemo_')
    {
		if(empty($pdf) && empty($creditmemosGiven) && empty($orderIds)){
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('There are no printable documents related to selected orders'));
			return false;
		}

        $this->order_ids_before = $orderIds;
        if(!empty($creditmemosGiven)){
            foreach ($creditmemosGiven as $creditmemoGiven) {
                $currentOrderId = $creditmemoGiven->getOrder()->getId();
                $this->order_ids_before[] = $currentOrderId;
                $creditmemoIds[$currentOrderId]=$creditmemoGiven->getId();
            }
        }

        //need to get the store id from the first order to initialise pdf
        $this->_current_order_store_id = $order = Mage::getModel('sales/order')->load($this->order_ids_before[0])->getStoreId();

        $this->_beforeGetPdf();

        //work with a new pdf or add to existing one
        if(empty($pdf)){
            $pdf = $this->getPdfObject($this->_current_order_store_id);
        }

        foreach ($this->order_ids_before as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if(!empty($creditmemosGiven)){
                $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                    ->addAttributeToSelect('*')
                    ->setOrderFilter($orderId)
                    ->addAttributeToFilter('entity_id', $creditmemoIds[$orderId])
                    ->load();
            }else{
                $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                    ->addAttributeToSelect('*')
                    ->setOrderFilter($orderId)
                    ->load();
            }
            if ($creditmemos->getSize() > 0) {
                foreach ($creditmemos as $creditmemo) {
                    /** @var Icommerce_PdfCustomiser_Helper_Creditmemo $creditmemoHelper */
                    $creditmemoHelper = Mage::helper('pdfcustomiser/creditmemo');
                    $creditmemo->load($creditmemo->getId());
                    $storeId = $creditmemo->getStoreId();
                    if ($creditmemo->getStoreId()) {
                        Mage::app()->getLocale()->emulate($creditmemo->getStoreId());
                    }


                    $creditmemoHelper->setStoreId($storeId);
                    // set standard pdf info
                    $pdf->SetStandard($creditmemoHelper);

                    // add a new page
                    $pdf->AddPage();
                    $pdf->printHeader($creditmemoHelper,$creditmemoHelper->getPdfCreditmemoTitle());

                    $creditmemoNumbersEtc = Mage::helper('sales')->__('Credit Memo # '). $creditmemo->getIncrementId()."\n";
                    if(Mage::getStoreConfig(self::XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID,$storeId)){
                        $creditmemoNumbersEtc .= Mage::helper('sales')->__('Order # ') . $order->getIncrementId()."\n";
                    }

                    $creditmemoNumbersEtc .= Mage::helper('catalog')->__('Date').': '.Mage::helper('core')->formatDate($creditmemo->getCreatedAt(), 'medium', false)."\n";
                    $pdf->MultiCell($pdf->getPageWidth() / 2 - $creditmemoHelper->getPdfMargins('sides'), 0, $creditmemoNumbersEtc, 0, 'L', 0, 0);
                    $pdf->MultiCell($pdf->getPageWidth() / 2 - $creditmemoHelper->getPdfMargins('sides'), $pdf->getLastH(), $creditmemoHelper->getPdfOwnerAddresss(), 0, 'L', 0, 1);
                    $pdf->Ln(5);

                    //add billing and shipping addresses
                    $pdf->OutputCustomerAddresses($creditmemoHelper, $order, $creditmemoHelper->getPdfCreditmemoAddresses());

                    //Display both currencies if flag is set and order is in a different currency
                    $displayBoth = $creditmemoHelper->getDisplayBoth() && $order->isCurrencyDifferent();

                    // Output Shipping and Payment
                    $pdf->OutputPaymentAndShipping($creditmemoHelper, $order,$creditmemo);

                    // Output heading for Items
                    switch(Mage::getStoreConfig('sales_pdf/all/allpagesize',$storeId)){
                        case 'A4':
                            $units = (595 - 2.83*2*$creditmemoHelper->getPdfMargins('sides'))/10;
                            break;
                        case 'LETTER':
                            $units = (612.00 - 2.83*2*(float)$creditmemoHelper->getPdfMargins('sides'))/10;
                            break;
                    }
                    $tbl ='<table border="0" cellpadding="2" cellspacing="0">';
                    $tbl.='<thead>';
                    $tbl.='<tr>';
                        $tbl.='<th width="'.(3*$units).'"><strong>'.Mage::helper('sales')->__('Product').'</strong></th>';
                        $tbl.='<th width="'.(1.5*$units).'"><strong>'.Mage::helper('sales')->__('SKU').'</strong></th>';
                        $tbl.='<th width="'.(1.1*$units).'" align="center"><strong>'.Mage::helper('sales')->__('Total(ex)').'</strong></th>';
                        $tbl.='<th width="'.(1.1*$units).'" align="center"><strong>'.Mage::helper('sales')->__('Discount').'</strong></th>';
                        $tbl.='<th width="'.(1.1*$units).'" align="center"><strong>'.Mage::helper('sales')->__('QTY').'</strong></th>';
                        $tbl.='<th width="'.(1.1*$units).'" align="center"><strong>'.Mage::helper('sales')->__('Tax').'</strong></th>';
                        $tbl.='<th width="'.(1.1*$units).'" align="right"><strong>'.Mage::helper('sales')->__('Total(inc)').'</strong></th>';
                    $tbl.='</tr>';
                    $tbl.='<tr><td width="'.(10*$units).'" colspan="6"><hr style="width:10px;"/></td></tr>';
                    $tbl.='</thead>';


                    // Prepare Line Items
                    $pdfItems = array();
                    $pdfBundleItems = array();
                    $pdf->prepareLineItems($creditmemoHelper,$creditmemo->getAllItems(),$pdfItems,$pdfBundleItems);

                    //Output Line Items
                    $pdf->SetFont($creditmemoHelper->getPdfFont(), '', $creditmemoHelper->getPdfFontsize('small'));
                    foreach ($pdfItems as $pdfItem){

                        //we generallly don't want to display subitems of configurable products etc
                        if($pdfItem['parentItemId']){
                                continue;
                        }

                        //Output line items
                        if ($pdfItem['parentType'] != 'bundle' && $pdfItem['type'] != 'bundle') {
                            $tbl.='<tr>';
                                $tbl.='<td width="'.(3*$units).'">'.$pdfItem['productDetails']['Name'].'</td>';
                                $tbl.='<td width="'.(1.5*$units).'">'.$pdfItem['productDetails']['Sku'].'</td>';
                                $tbl.='<td width="'.(1.1*$units).'" align="center">'.$pdf->OutputPrice($pdfItem['price'],$pdfItem['basePrice'],$displayBoth,$order).'</td>';
                                $tbl.='<td width="'.(1.1*$units).'" align="center">'.$pdf->OutputPrice($pdfItem['discountAmount'],$pdfItem['baseDiscountAmount'],$displayBoth,$order).'</td>';
                                $tbl.='<td width="'.(1.1*$units).'" align="center">'.$pdfItem['qty'].'</td>';
                                $tbl.='<td width="'.(1.1*$units).'" align="center">'.$pdf->OutputPrice($pdfItem['taxAmount'],$pdfItem['baseTaxAmount'],$displayBoth,$order).'</td>';
                                $tbl.='<td width="'.(1.1*$units).'" align="right">'.$pdf->OutputPrice($pdfItem['rowTotalInclTax'],$pdfItem['baseRowTotal'],$displayBoth,$order).'</td>';
                            $tbl.='</tr>';

                        } else {    //Deal with Bundles
                            //check if the subitems of the bundle have separate prices
                            $currentParentId =$pdfItem['itemId'];
                            $subItemsSum = 0;
                            foreach ($pdfBundleItems[$currentParentId] as $bundleItem){
                                $subItemsSum += $bundleItem['price'];
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
                                        $tbl.='<td width="'.(3*$units).'">&nbsp;&nbsp;&nbsp;&nbsp;'.$bundleItem['productDetails']['Name'].'</td>';
                                        $tbl.='<td width="'.(1.5*$units).'">'.$bundleItem['productDetails']['Sku'].'</td>';
                                        $tbl.='<td width="'.(1.1*$units).'" align="center">'.$pdf->OutputPrice($bundleItem['price'],$bundleItem['basePrice'],$displayBoth,$order).'</td>';
                                        $tbl.='<td width="'.(1.1*$units).'" align="center">'.$pdf->OutputPrice($bundleItem['discountAmount'],$bundleItem['baseDiscountAmount'],$displayBoth,$order).'</td>';
                                        $tbl.='<td width="'.(1.1*$units).'" align="center">'.$bundleItem['qty'].'</td>';
                                        $tbl.='<td width="'.(1.1*$units).'" align="center">'.$pdf->OutputPrice($bundleItem['taxAmount'],$bundleItem['baseTaxAmount'],$displayBoth,$order).'</td>';
                                        $tbl.='<td width="'.(1.1*$units).'" align="right">'.$pdf->OutputPrice($bundleItem['rowTotal'],$bundleItem['baseRowTotal'],$displayBoth,$order).'</td>';
                                    $tbl.='</tr>';
                                }
                            }else {
                                foreach ($pdfBundleItems[$currentParentId] as $bundleItem){
                                    $pdfItem['productDetails']['Name'] .= "<br/>&nbsp;&nbsp;&nbsp;&nbsp;".$bundleItem['qty']." x " .$bundleItem['productDetails']['Name'];
                                }
                                $tbl.='<tr>';
                                    $tbl.='<td width="'.(3*$units).'">'.$pdfItem['productDetails']['Name'].'</td>';
                                    $tbl.='<td width="'.(1.5*$units).'">'.$pdfItem['productDetails']['Sku'].'</td>';
                                    $tbl.='<td width="'.(1.1*$units).'" align="center">'.$pdf->OutputPrice($pdfItem['price'],$pdfItem['basePrice'],$displayBoth,$order).'</td>';
                                    $tbl.='<td width="'.(1.1*$units).'" align="center">'.$pdf->OutputPrice($pdfItem['discountAmount'],$pdfItem['baseDiscountAmount'],$displayBoth,$order).'</td>';
                                    $tbl.='<td width="'.(1.1*$units).'" align="center">'.$pdfItem['qty'].'</td>';
                                    $tbl.='<td width="'.(1.1*$units).'" align="center">'.$pdf->OutputPrice($pdfItem['taxAmount'],$pdfItem['baseTaxAmount'],$displayBoth,$order).'</td>';
                                    $tbl.='<td width="'.(1.1*$units).'" align="right">'.$pdf->OutputPrice($pdfItem['rowTotal'],$pdfItem['baseRowTotal'],$displayBoth,$order).'</td>';
                                $tbl.='</tr>';
                            }
                        }
                        $tbl.='<tcpdf method="Line2" params=""/>';
                    }
                    $tbl.='</table>';
                    $pdf->writeHTML($tbl, true, false, false, false, '');
                    $pdf->SetFont($creditmemoHelper->getPdfFont(), '', $creditmemoHelper->getPdfFontsize());

                    //reset Margins in case there was a page break
                     $pdf->setMargins($creditmemoHelper->getPdfMargins('sides'),$creditmemoHelper->getPdfMargins('top'));

                    // Output totals
                    $pdf->OutputTotals($creditmemoHelper, $order,$creditmemo);

                    // Output Comments
                    $pdf->OutputComment($creditmemoHelper,$creditmemo);

                    //Custom Blurb underneath
                    $pdf->Ln(2);
                    $pdf->writeHTMLCell(0, 0, null, null,$creditmemoHelper->getPdfCreditmemoCustom(), null,1);

                    if ($creditmemo->getStoreId()) {
                        Mage::app()->getLocale()->revert();
                    }
                    $pdf->setPdfAnyOutput(true);
                 }
            }
        }

        // reset pointer to the last page
        if ($pdf->getNumPages()) {
            $pdf->lastPage();
        }

        //output PDF document
        if(!$suppressOutput){
			if($pdf->getPdfAnyOutput()){
				$pdf->Output($outputFileName.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', 'I');
				exit;
			}else{
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('There are no printable documents related to selected orders'));
			}
        }

        $this->_afterGetPdf();
        return $pdf;
    }
}

/**
 * Class Icommerce_PdfCustomiser_Creditmemo
 * @deprecated since version 1.2.32, please use instead Mage::helper('pdfcustomiser/creditmemo');
 */
class Icommerce_PdfCustomiser_Creditmemo extends Icommerce_PdfCustomiser_Helper_Creditmemo
{
}
