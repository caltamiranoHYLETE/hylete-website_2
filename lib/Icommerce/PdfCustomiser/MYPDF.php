<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Icommerce_PdfCustomiser
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Vaimo AB Team
 */

if (!class_exists('TCPDF', false)) {
    require_once (Mage::getBaseDir('lib') . DS . 'tcpdf' . DS . 'tcpdf.php');
}

/**
 * Class Icommerce_PdfCustomiser_MYPDF
 */
class Icommerce_PdfCustomiser_MYPDF extends TCPDF
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        $orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false,
        $pdfa = false
    ) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $this->_addCustomFonts();
    }

    /**
     * keep track if we have output
     *
     * @access protected
     */
    protected $_PdfAnyOutput = false;
    protected static $_store_id = 0;

    /**
     * do we have output?
     *
     * @return  bool
     * @access public
     */
    public function getPdfAnyOutput()
    {
        return $this->_PdfAnyOutput;
    }

    /**
     * set _PdfAnyOutput
     *
     * @return  void
     * @access public
     */
    public function setPdfAnyOutput($flag)
    {
        $this->_PdfAnyOutput = $flag;
    }

    /**
     * retrieve line items
     *
     * @param
     *
     * @return void
     * @access public
     */
    public function prepareLineItems($helper, $items, &$pdfItems, &$pdfBundleItems)
    {
        foreach ($items as $item) {
            //check if we are printing an order - doesn't have method getOrderItem
            if (method_exists($item, 'getOrderItem')) {
                //we generallly don't want to display subitems of configurable products etc but we do for bundled
                $type = $item
                    ->getOrderItem()
                    ->getProductType();
                $itemId = $item
                    ->getOrderItem()
                    ->getItemId();
                $parentType = 'none';
                $parentItemId = $item
                    ->getOrderItem()
                    ->getParentItemId();

                if ($parentItemId) {
                    $parentType = Mage::getModel('sales/order_item')
                        ->load($parentItemId)
                        ->getProductType();
                }

                //Get item Details
                $pdfTemp['itemId'] = $itemId;
                $pdfTemp['productId'] = $item
                    ->getOrderItem()
                    ->getProductId();
                $pdfTemp['type'] = $type;
                $pdfTemp['parentType'] = $parentType;
                $pdfTemp['parentItemId'] = $parentItemId;
                $pdfTemp['productDetails'] = $this->getItemNameAndSku($item);
                $pdfTemp['productOptions'] = $item->getProductOptions();
                $pdfTemp['price'] = $item->getPrice();
                $pdfTemp['discountAmount'] = $item->getDiscountAmount();
                $pdfTemp['qty'] = $helper->getPdfQtyAsInt() ? (int)$item->getQty() : $item->getQty();
                $pdfTemp['taxAmount'] = $item->getTaxAmount();
                $pdfTemp['rowTotal'] = $item->getRowTotal();
                $pdfTemp['rowTotalInclTax'] = $item->getRowTotalInclTax();
                $pdfTemp['basePrice'] = $item->getBasePrice();
                $pdfTemp['baseDiscountAmount'] = $item->getBaseDiscountAmount();
                $pdfTemp['baseTaxAmount'] = $item->getBaseTaxAmount();
                $pdfTemp['baseRowTotal'] = $item->getBaseRowTotal();
            } else {
                //we generallly don't want to display subitems of configurable products etc but we do for bundled
                $type = $item->getProductType();
                $itemId = $item->getItemId();
                $parentType = 'none';
                $parentItemId = $item->getParentItemId();

                if ($parentItemId) {
                    $parentType = Mage::getModel('sales/order_item')
                        ->load($parentItemId)
                        ->getProductType();
                }

                //Get item Details
                $pdfTemp['itemId'] = $itemId;
                $pdfTemp['productId'] = $item->getProductId();
                $pdfTemp['type'] = $type;
                $pdfTemp['parentType'] = $parentType;
                $pdfTemp['parentItemId'] = $parentItemId;
                $pdfTemp['productDetails'] = $this->getItemNameAndSku($item);
                $pdfTemp['productOptions'] = $item->getProductOptions();
                $pdfTemp['price'] = $item->getPrice();
                $pdfTemp['discountAmount'] = $item->getDiscountAmount();
                $pdfTemp['qty'] = $helper->getPdfQtyAsInt() ? (int)$item->getQtyOrdered() : $item->getQtyOrdered();
                $pdfTemp['taxAmount'] = $item->getTaxAmount();
                $pdfTemp['rowTotal'] = $item->getRowTotal();
                $pdfTemp['basePrice'] = $item->getBasePrice();
                $pdfTemp['baseDiscountAmount'] = $item->getBaseDiscountAmount();
                $pdfTemp['baseTaxAmount'] = $item->getBaseTaxAmount();
                $pdfTemp['baseRowTotal'] = $item->getBaseRowTotal();

            }

            //collect bundle and PC  subitems separately
            if (in_array($parentType, array('bundle', 'productconfigurator'))) {
                $pdfBundleItems[$parentItemId][] = $pdfTemp;
            } else {
                $pdfItems[$itemId] = $pdfTemp;
            }
        }
    }

    /*
     * Page header
     * return float height of logo
     */

    public function printHeader($helper, $title)
    {
        $maxLogoHeight = $helper->getMaxLogoHeight();
        //add title

        // Place Logo
        if ($helper->getPdfLogo()) {
            //Figure out if logo is too wide - half the page width minus margins
            $maxWidth = ($helper->getPageWidth() / 2) - $helper->getPdfMargins('sides');
            if ($helper->getPdfLogoDimensions('w') > $maxWidth) {
                $logoWidth = $maxWidth;
            } else {
                $logoWidth = $helper->getPdfLogoDimensions('w');
            }
            $this->Image(
                $helper->getPdfLogo(), $helper->getPdfMargins('sides'), $helper->getPdfMargins('top'), $logoWidth,
                $maxLogoHeight, null, null, null, null, null, null, null, null, null, true
            );
        }

        $right_image = null;
        preg_match('/{{([^}]+)}}/', $title, $matches);

        if (!empty($matches) && !empty($matches[1])) {
            $right_image = Mage::getBaseDir('media') . '/' . $matches[1];
        }
        // Place Image
        if (!empty($right_image) && file_exists($right_image)) {
            //Figure out if logo is too wide - half the page width minus margins
            $maxWidth = ($helper->getPageWidth() / 2) - $helper->getPdfMargins('sides');
            if ($helper->getPdfLogoDimensions('w') > $maxWidth) {
                $logoWidth = $maxWidth;
            } else {
                $logoWidth = $helper->getPdfLogoDimensions('w');
            }
            $this->Image(
                $right_image, $helper->getPageWidth() / 2, $helper->getPdfMargins('top'), $logoWidth, $maxLogoHeight,
                null, null, null, null, null, null, null, null, null, true
            );
        } else {
            $this->SetX($helper->getPageWidth() / 2);
            $this->SetFont($helper->getPdfFont(), 'B', $helper->getPdfFontsize('large'));
            $this->Cell(
                $helper->getPageWidth() / 2 - $helper->getPdfMargins('sides'), 0, $title, 0, 2, 'L', null, null, 1
            );
            $this->SetFont($helper->getPdfFont(), '', $helper->getPdfFontsize());
        }
        // Line break
        $this->SetY($helper->getPdfMargins('top') + min($helper->getPdfLogoDimensions('h-scaled'), $maxLogoHeight));
        $this->Ln(6);
    }

    /**
     * set some standards for all pdf pages
     *
     * @param $helper Icommerce_PdfCustomiser_Helper_Invoice
     */
    public function SetStandard($helper)
    {
        // set document information
        $this->SetCreator('Magento');

        //set margins
        $this->SetMargins($helper->getPdfMargins('sides'), $helper->getPdfMargins('top'));

        // set header and footer
        $this->setPrintFooter(true);
        $this->setPrintHeader(false);

        $this->setHeaderMargin(10);

        // set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set auto page breaks
        $this->SetAutoPageBreak(true, $helper->getPdfMargins('bottom'));

        //set image scale factor 1 pixel = 1mm
        $this->setImageScale(1);

        // set font
        $this->SetFont($helper->getPdfFont(), '', $helper->getPdfFontsize());

        // set fillcolor black
        $this->SetFillColor(0);

        self::$_store_id = $helper->getStoreId(); # I put it in sessions so i can use it later

        // see if we need to sign
        $a = $helper->getStoreId();
        if (Mage::getStoreConfig('sales_pdf/all/allsign', $helper->getStoreId())) {
            $certificate = Mage::helper('core')
                ->decrypt(Mage::getStoreConfig('sales_pdf/all/allsigncertificate', $helper->getStoreId()));
            $certpassword = Mage::helper('core')
                ->decrypt(Mage::getStoreConfig('sales_pdf/all/allsignpassword', $helper->getStoreId()));

            // set document signature
            $this->setSignature($certificate, $certificate, $certpassword, '', 2, null);
        }
    }

    public function Header()
    {

        //add title
        $headerData = $this->getHeaderData();

        $this->Cell(0, 0, $headerData['title'], 0, 2, 'L', null, null, 1);

        // Line break
        $this->Ln(5);
    }

    public function Line2($space = 1)
    {
        $this->SetY($this->GetY() + $space);
        $margins = $this->getMargins();
        $this->Line($margins['left'], $this->GetY(), $this->getPageWidth() - $margins['right'], $this->GetY());
        $this->SetY($this->GetY() + $space);
    }

    /*
    * returns specific item option list as html string
    */
    private function listItemOptionItems($optList, $terminateWithLinebreak)
    {
        $htmlOptList = "";
        if (is_array($optList)) {
            /** @var $helper Mage_Core_Helper_String */
            $helper = Mage::helper('core/string');
            foreach ($optList as $option) {
                $htmlOptList .= "<br/>&nbsp;&nbsp;" . $helper->escapeHtml($option['label']) . ": "
                    . $helper->escapeHtml($option['value']);
            }

            if ($terminateWithLinebreak) {
                $htmlOptList .= "<br/>";
            }
        }

        return $htmlOptList;
    }

    /*
     *  get product name and Sku, take into consideration configurable products and product options
     */
    public function getItemNameAndSku($item)
    {
        $return = array();

        // Characters < > & will break the pdf generation so we need to replace them
        /** @var $helper Mage_Core_Helper_String */
        $helper = Mage::helper('core/string');
        $return['Name'] = $helper->escapeHtml($item->getName());
        $return['Sku'] = $helper->escapeHtml($item->getSku());

        //check if we are printing an order - doesn't have method getOrderItem. If it does then fetch item form order
        $optItem = $item;

        if (method_exists($item, 'getOrderItem')) {
            $optItem = $item->getOrderItem();
        }

        if ($options = $optItem->getProductOptions()) {
            if (!empty($options['options'])) {
                $return['Name'] .= $this->listItemOptionItems($options['options'], true);
            }
            if (!empty($options['additional_options'])) {
                $return['Name'] .= $this->listItemOptionItems($options['additional_options'], true);
            }
            if (!empty($options['attributes_info'])) {
                $return['Name'] .= $this->listItemOptionItems($options['attributes_info'], false);
            }

            if ($optItem->getProductOptionByCode('simple_sku')) {
                $return['Sku'] = $optItem->getProductOptionByCode('simple_sku');
            }
        }

        /*
        //Uncomment this block: delete /* and * / and enter your attribute code below
        $attributeCode ='attribute_code_from_Magento_backend';
        $productAttribute = Mage::getModel('catalog/product')->load($item->getProductId())->getData($attributeCode);
        if(!empty($productAttribute)){
            $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeCode);
            $return['Name'] .= "<br/><br/>".$attribute->getFrontendLabel().": ".$productAttribute;
        }
         */
        return $return;
    }

    /**
     * output customer addresses
     *
     * @param $helper Icommerce_PdfCustomiser_Helper_Invoice
     * @param $order
     * @param $which
     */
    public function OutputCustomerAddresses($helper, $order, $which)
    {
        $format = Mage::getStoreConfig('sales_pdf/all/alladdressformat', $helper->getStoreId());
        $shipping = $order->getShippingAddress();
        $billing = $order->getBillingAddress();

        $billingAddress = $billing->getData('firstname') . " " . $billing->getData('lastname') . "<br />";

        $billingAddress .= ($billing->getData('telephone') != '' ? $billing->getData('telephone') . "<br/>" : '');

        if ($billing->getData('care_of') && $billing->getData('care_of') != "") {
            $billingAddress .= "c/o " . $billing->getData('care_of') . "<br />";
        }

        $billingAddress .= ($billing->getData('company') != '' ? $billing->getData('company') . "<br/>" : '') .
            $billing->getData('street') . "<br />" .
            $billing->getData('postcode') . " " . $billing->getData('city') . "<br />" .
            Mage::getModel('directory/country')
                ->load($billing->getData('country_id'))
                ->getName();

        $shippingAddress = $this->renderShippingAddress($shipping);

        if ($order->getCustomerTaxvat()) {

            $billingAddress .= "<br />" . Mage::helper('sales')->__('TAX/VAT Number') . ": "
                . $order->getCustomerTaxvat();
        } else {
            $billingAddress = $billingAddress;
        }

        switch ($which) {
            case 'both':
                if (get_class($helper) == 'Icommerce_PdfCustomiser_Helper_Shipment') {
                    $this->SetX($helper->getPdfMargins('sides'));
                    $this->Cell(
                        $this->getPageWidth() / 2 - $helper->getPdfMargins('sides'), 0,
                        $this->_getShippingAddressTitle($order), 0, 0, 'L'
                    );
                    if (!$order->getIsVirtual()) {
                        $this->Cell(0, 0, $this->_getBillingAddressTitle($order), 0, 1, 'L');
                    } else {
                        $this->Cell(0, $this->getLastH(), '', 0, 1, 'L');
                    }
                    $this->SetX($helper->getPdfMargins('sides'));
                    $this->writeHTMLCell(
                        $this->getPageWidth() / 2 - $helper->getPdfMargins('sides'), 0, null, null, $shippingAddress
                        /*$order->getShippingAddress()->format($format)*/, null, 0
                    );
                    if (!$order->getIsVirtual()) {
                        $this->writeHTMLCell(0, $this->getLastH(), null, null, $billingAddress, null, 1);
                    } else {
                        $this->Cell(0, $this->getLastH(), '', 0, 1, 'L');
                    }
                    break;
                } else {
                    $this->SetX($helper->getPdfMargins('sides'));
                    $this->Cell(
                        $this->getPageWidth() / 2 - $helper->getPdfMargins('sides'), 0,
                        $this->_getBillingAddressTitle($order), 0, 0, 'L'
                    );
                    if (!$order->getIsVirtual()) {
                        $this->Cell(
                            0, 0, $this->_getShippingAddressTitle($order), 0, 1, 'L'
                        );
                    } else {
                        $this->Cell(0, $this->getLastH(), '', 0, 1, 'L');
                    }
                    $this->SetX($helper->getPdfMargins('sides'));
                    $this->writeHTMLCell(
                        $this->getPageWidth() / 2 - $helper->getPdfMargins('sides'), 0, null, null, $billingAddress,
                        null, 0
                    );
                    if (!$order->getIsVirtual()) {
                        $this->writeHTMLCell(
                            0, $this->getLastH(), null, null, $shippingAddress
                            /*$order->getShippingAddress()->format($format)*/, null, 1
                        );
                    } else {
                        $this->Cell(0, $this->getLastH(), '', 0, 1, 'L');
                    }
                    break;
                }

            case 'billing':
                $this->SetX($helper->getPdfMargins('sides'));
                $this->writeHTMLCell(0, 0, null, null, $billingAddress, null, 1);
                break;
            case 'shipping':
                $this->SetX($helper->getPdfMargins('sides'));
                if (!$order->getIsVirtual()) {
                    $this->writeHTMLCell(
                        0, 0, null, null, $order
                            ->getShippingAddress()
                            ->format($format), null, 1
                    );
                }
                break;
            default:
                $this->SetX($helper->getPdfMargins('sides'));
                $this->writeHTMLCell(0, 0, null, null, $billingAddress, null, 1);
        }
        $this->Ln(10);
    }

    /**
     * output payment and shipping blocks
     *
     * @param $helper Icommerce_PdfCustomiser_Helper_Invoice
     * @param $order
     */
    public function OutputPaymentAndShipping($helper, $order)
    {
        $info = Mage::helper('payment')->getInfoBlock($order->getPayment());
        $info->getMethod()->setStore($order->getStoreId());
        $paymentInfo = $info->setIsSecureMode(true)->toHtml();

        $this->SetFont($helper->getPdfFont(), 'B', $helper->getPdfFontsize());
        $this->Cell(
            0.5 * ($this->getPageWidth() - 2 * $helper->getPdfMargins('sides')), 0,
            Mage::helper('sales')->__('Payment Method'), 0, 0, 'L'
        );
        if (!$order->getIsVirtual()) {
            $this->Cell(0, 0, Mage::helper('sales')->__('Shipping Method'), 0, 1, 'L');
        } else {
            $this->Cell(0, 0, '', 0, 1, 'L');
        }

        $this->SetFont($helper->getPdfFont(), '', $helper->getPdfFontsize());
        $this->writeHTMLCell(
            0.5 * ($this->getPageWidth() - 2 * $helper->getPdfMargins('sides')), 0, null, null, $paymentInfo, null, 0
        );

        if (!$order->getIsVirtual()) {
            $trackingInfo = "";
            $tracks = $order->getTracksCollection();
            if (count($tracks)) {
                $trackingInfo = "\n";
                foreach ($tracks as $track) {
                    if (!strstr($track->getTitle(), "<a href=")) {
                        $trackingInfo .= "\n" . $track->getTitle() . ": " . $track->getNumber();
                    } else {
                        $trackingInfo .= "\n" . $track->getCarrierCode() . ": " . $track->getNumber();
                    }
                }
            }
            $this->MultiCell(0, $this->getLastH(), preg_replace('#<br\s*/?>#i', "\n", $order->getShippingDescription()) . $trackingInfo, 0, 'L', 0, 1);
        } else {
            $this->Cell(0, $this->getLastH(), '', 0, 1, 'L');
        }
        $this->Ln(10);
    }

    /**
     * output totals for invoice and creditmemo
     *
     * @param $helper Icommerce_PdfCustomiser_Helper_Invoice
     * @param $order
     * @param $item
     */
    public function OutputTotals($helper, $order, $item)
    {

        //Display both currencies if flag is set and order is in a different currency
        $displayBoth = $helper->getDisplayBoth() && $order->isCurrencyDifferent();

        $widthTextTotals = $displayBoth ?
            $this->getPageWidth() - 2 * $helper->getPdfMargins('sides') - 4.5 * $helper->getPdfFontsize()
            : $this->getPageWidth() - 2 * $helper->getPdfMargins('sides') - 2.5 * $helper->getPdfFontsize();
        $this->MultiCell(
            $widthTextTotals, 0, Mage::helper('sales')
                ->__('Order Subtotal:'), 0, 'R', 0, 0
        );
        $this->OutputTotalPrice($item->getSubtotal(), $item->getBaseSubtotal(), $displayBoth, $order);

        if ((float)$item->getDiscountAmount() != 0) {
            $this->MultiCell(
                $widthTextTotals, 0, Mage::helper('sales')
                    ->__('Discount:'), 0, 'R', 0, 0
            );
            $this->OutputTotalPrice($item->getDiscountAmount(), $item->getBaseDiscountAmount(), $displayBoth, $order);
        }

        if ((float)$item->getTaxAmount() > 0) {
            if (Mage::helper('tax')
                ->displayFullSummary()
            ) {
                $filteredTaxrates = array();
                //need to filter out doubled up taxrates on edited/reordered items -> Magento bug
                foreach ($order->getFullTaxInfo() as $taxrate) {
                    foreach ($taxrate['rates'] as $rate) {
                        $taxId = $rate['code'];
                        $filteredTaxrates[$taxId] = array('id'         => $rate['code'], 'percent' => $rate['percent'],
                                                          'amount'     => $taxrate['amount'],
                                                          'baseAmount' => $taxrate['base_amount']);
                    }
                }
                foreach ($filteredTaxrates as $filteredTaxrate) {
                    $this->MultiCell(
                        $widthTextTotals, 0, $filteredTaxrate['id'] . " [" . $filteredTaxrate['percent'] . "%]" . ":",
                        0, 'R', 0, 0
                    );
                    $this->OutputTotalPrice(
                        $filteredTaxrate['amount'], $filteredTaxrate['baseAmount'], $displayBoth, $order
                    );
                }
            } else {
                $this->MultiCell(
                    $widthTextTotals, 0, Mage::helper('sales')
                        ->__('Tax') . ":", 0, 'R', 0, 0
                );
                $this->OutputTotalPrice($item->getTaxAmount(), $item->getBaseTaxAmount(), $displayBoth, $order);
            }
        }

        if ((float)$item->getShippingAmount() > 0) {
            //Check configuration values so the shipping cost will be displayed as it is on the order totals block.
            switch (Mage::getStoreConfig(Mage_Tax_Model_Config::XML_PATH_DISPLAY_SALES_SHIPPING)) {
                case Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX:
                    //Show shipping amount with tax included
                    $this->MultiCell(
                        $widthTextTotals, 0, Mage::helper('sales')
                            ->__('Shipping & Handling:'), 0, 'R', 0, 0
                    );
                    $this->OutputTotalPrice(
                        $item->getShippingAmount() + $item->getShippingTaxAmount(),
                        $item->getBaseShippingAmount() + $item->getBaseShippingTaxAmount(), $displayBoth, $order
                    );
                    break;

                case Mage_Tax_Model_Config::DISPLAY_TYPE_EXCLUDING_TAX:
                    //Show shipping amount without tax
                    $this->MultiCell(
                        $widthTextTotals, 0, Mage::helper('sales')
                            ->__('Shipping & Handling:'), 0, 'R', 0, 0
                    );
                    $this->OutputTotalPrice(
                        $item->getShippingAmount(), $item->getBaseShippingAmount(), $displayBoth, $order
                    );
                    break;

                case Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH:
                    //Show both amounts (excluding and including tax). Two rows added to output
                    $this->MultiCell(
                        $widthTextTotals, 0, Mage::helper('sales')
                            ->__('Shipping & Handling (Excl.Tax):'), 0, 'R', 0, 0
                    );
                    $this->OutputTotalPrice(
                        $item->getShippingAmount(), $item->getBaseShippingAmount(), $displayBoth, $order
                    );
                    $this->MultiCell(
                        $widthTextTotals, 0, Mage::helper('sales')
                            ->__('Shipping & Handling (Incl.Tax):'), 0, 'R', 0, 0
                    );
                    $this->OutputTotalPrice(
                        $item->getShippingAmount() + $item->getShippingTaxAmount(),
                        $item->getBaseShippingAmount() + $item->getBaseShippingTaxAmount(), $displayBoth, $order
                    );
                    break;
            }
        }

        if ($item->getAdjustmentPositive()) {
            $this->MultiCell(
                $widthTextTotals, 0, Mage::helper('sales')
                    ->__('Adjustment Refund:'), 0, 'R', 0, 0
            );
            $this->OutputTotalPrice(
                $item->getAdjustmentPositive(), $item->getBaseAdjustmentPositive(), $displayBoth, $order
            );
        }

        if ((float)$item->getAdjustmentNegative()) {
            $this->MultiCell(
                $widthTextTotals, 0, Mage::helper('sales')
                    ->__('Adjustment Fee:'), 0, 'R', 0, 0
            );
            $this->OutputTotalPrice(
                $item->getAdjustmentNegative(), $item->getBaseAdjustmentNegative(), $displayBoth, $order
            );
        }

        //Addition for Klarna Faktura invoice fee
        $additionalInformation = $order->getPayment()->getAdditionalInformation();
        if (isset($additionalInformation['invoice_fee']) && (float)$additionalInformation['invoice_fee']) {
            $this->MultiCell($widthTextTotals, 0, Mage::helper('sales')->__('Invoice Fee (w/o tax):'), 0, 'R', 0, 0);
            $this->OutputTotalPrice(
                $additionalInformation['invoice_fee'], $additionalInformation['invoice_fee'], $displayBoth, $order
            );
        }

        //Total separated with line plus bolded
        $this->Ln(5);
        $this->Cell($this->getPageWidth() / 2 - $helper->getPdfMargins('sides'), 5, '', 0, 0, 'C');
        $this->Cell(0, 5, '', 'T', 1, 'C');
        $this->SetFont($helper->getPdfFont(), 'B', $helper->getPdfFontsize());
        $this->MultiCell(
            $widthTextTotals, 0, Mage::helper('sales')
                ->__('Grand Total:'), 0, 'R', 0, 0
        );
        $this->OutputTotalPrice($item->getGrandTotal(), $item->getBaseGrandTotal(), $displayBoth, $order);
        $this->SetFont($helper->getPdfFont(), '', $helper->getPdfFontsize());
    }

    /**
     * output Gift Message for Order / Should work for Item but seems to be a bug in Magento (getGiftMessageId = null)
     *
     * @param $helper Icommerce_PdfCustomiser_Helper_Invoice
     * @param $order
     */
    public function OutputGiftMessage($helper, $order)
    {

        if ($order->getGiftMessageId()
            && $giftMessage = Mage::helper('giftmessage/message')->getGiftMessage($order->getGiftMessageId())
        ) {
            $this->SetFont($helper->getPdfFont(), 'B', $helper->getPdfFontsize());
            $this->Cell(
                0, 0, Mage::helper('giftmessage')
                    ->__('Gift Message'), 0, 1, 'L', null, null, 1
            );
            $this->SetFont($helper->getPdfFont(), '', $helper->getPdfFontsize());

            $message
                =
                "<b>" . Mage::helper('giftmessage')->__('From:') . "</b> " . htmlspecialchars($giftMessage->getSender())
                . "<br/>";
            $message .= "<b>" . Mage::helper('giftmessage')->__('To:') . "</b> " . htmlspecialchars(
                    $giftMessage->getRecipient()
                ) . "<br/>";
            $message .= "<b>" . Mage::helper('giftmessage')->__('Message:') . "</b> " . htmlspecialchars(
                    $giftMessage->getMessage()
                ) . "<br/>";
            $this->writeHTMLCell(0, 0, null, null, $message, null, 1);
        }
    }

    /**
     * output Comments on item - complete comment history
     *
     * @param $helper Icommerce_PdfCustomiser_Helper_Invoice
     * @param $item
     */
    public function OutputComment($helper, $item)
    {
        if ($helper->getPrintComments()) {
            $comments = '';
            if (get_class($item) == 'Icommerce_EmailAttachments_Model_Order') {
                foreach ($item->getAllStatusHistory() as $history) {
                    $comments .= Mage::helper('core')
                            ->formatDate($history->getCreatedAt(), 'medium') . " | " . $history->getStatusLabel() . "  "
                        . $history->getComment() . "\n";
                }
            } else {
                if ($item->getCommentsCollection()) {
                    foreach ($item->getCommentsCollection() as $comment) {
                        $comments .= Mage::helper('core')
                                ->formatDate($comment->getCreatedAt(), 'medium') . " | " . $comment->getComment()
                            . "\n";
                    }
                }
            }
            if (!empty($comments)) {
                $this->SetFont($helper->getPdfFont(), 'B', $helper->getPdfFontsize());
                $this->Cell(0, 0, Mage::helper('sales')->__('Comments'), 0, 1, 'L', null, null, 1);
                $this->SetFont($helper->getPdfFont(), '', $helper->getPdfFontsize());
                $this->MultiCell(0, 0, $comments, 0, 'L', 0, 1);
            }
        }
    }

    /*
     *  output prices for invoice and creditmemo
     */
    public function OutputPrice($price, $basePrice, $displayBoth, $order)
    {

        return $displayBoth ? (strip_tags($order->formatBasePrice($basePrice)) . '<br/>' . strip_tags(
                $order->formatPrice($price)
            )) : $order->formatPriceTxt($price);
    }

    /*
     *  output total prices for invoice and creditmemo
     */
    public function OutputTotalPrice($price, $basePrice, $displayBoth, $order)
    {
        if ($displayBoth) {
            $this->MultiCell(
                2.25 * $this->getFontSizePt(), 0, strip_tags($order->formatBasePrice($basePrice)), 0, 'R', 0, 0
            );
        }
        $this->MultiCell(0, 0, $order->formatPriceTxt($price), 0, 'R', 0, 1);
    }

    public function write1DBarcode(
        $code, $type, $x = '', $y = '', $w = '', $h = '', $xres = 0.4, $style = '', $align = 'T'
    ) {
        if ($style == '') {
            $style = array(
                    'position' => 'S',
                    'border' => false,
                    'padding' => 1,
                    'fgcolor' => array(0, 0, 0),
                    'bgcolor' => false,
                    'text' => true,
                    'font' => 'helvetica',
                    'fontsize' => 8,
                    'stretchtext' => 4
            );
        }
        parent::write1DBarcode($code, $type, $x, $y, $w, $h, $xres, $style, $align);
    }

    public function returnFooter($footer_nr)
    {
        $footer = Mage::getStoreConfig('sales_pdf/invoice/invoice_footer_' . $footer_nr, self::$_store_id);
        if (null === $footer) {
            return null;
        }
        $footer = str_replace("\n", '<br />', $footer);

        return $footer;
    }

    public function Footer()
    {

        /** @var $invoiceHelper Icommerce_PdfCustomiser_Model_Invoice */
        $invoiceHelper = Mage::getModel('pdfcustomiser/invoice');
        $cur_y = $this->GetY();
        $ormargins = $this->getOriginalMargins();
        $this->SetTextColor(0, 0, 0);

        //set style for cell border
        $line_width = 0.85 / $this->getScaleFactor();
        $this->SetLineStyle(
            array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))
        );
        //print document barcode
        $barcode = $this->getBarcode();
        if (!empty($barcode)) {
            $this->Ln($line_width);
            $barcode_width = round(($this->getPageWidth() - $ormargins['left'] - $ormargins['right']) / 3);
            $this->write1DBarcode(
                $barcode, 'C128B', $this->GetX(), $cur_y + $line_width, $barcode_width,
                (($this->getFooterMargin() / 3) - $line_width), 0.3, '', ''
            );
        }
        $this->SetY($cur_y);

        $footer_columns = array();
        $col_step = 25;
        $col_x_add = 0;
        $col_y = 275;
        $border_box = 200;
        $was_col_content = false;
        for ($i = 1; $i <= 4; $i++ && $col_step += 50) {
            $col_content = $this->returnFooter($i);
            if (empty($col_content)) {
                continue;
            }
            $was_col_content = true;

            $footer_columns[$i] = array(
                'x'       => $col_step,
                'content' => $col_content,
            );
        }

        if ($was_col_content) {
            $box_fontsize = (int )Mage::getStoreConfig(
                'sales_pdf/invoice/invoice_footer_box_fontsize', self::$_store_id
            );
            $box_fontsize = max($box_fontsize, 8);
            $box_fontsize = min($box_fontsize, 20);
            $this->SetX($ormargins['left']);
            $this->SetFont($invoiceHelper->getPdfFont(), '', $box_fontsize);
            $this->Ln(4);
            if (!isset($footer_columns[4])) {
                $col_x_add = 12;
            }
            foreach ($footer_columns as $nth => $col) {
                $col_width = $col['x'];
                if ($nth > 1 && $col_x_add > 0) {
                    $col_width += $col_x_add + (($nth - 2) * $col_x_add);
                }
                $this->writeHTMLCell(0, 0, $col_width, $col_y, $col['content'], 0, 1);
            }
            $box_height = Mage::getStoreConfig('sales_pdf/invoice/invoice_footer_box_height', self::$_store_id);
            if (null === $box_height || $box_height === '') {
                $box_height = 20;
            }
            if ($box_height > 0) {
                $box_bordersingle = (int )Mage::getStoreConfig(
                    'sales_pdf/invoice/invoice_footer_box_bordersingle', self::$_store_id
                );
                if ($box_bordersingle) {
                    $this->writeHTMLCell($border_box, (int )$box_height, null, $col_y - 2, '<hr />', 0, null, null, 1);
                } else {
                    $this->writeHTMLCell($border_box, (int )$box_height, null, $col_y - 2, '', 1, null, null, 1);
                }
            }
        }
    }

    protected function _addCustomFonts()
    {
        /** @var Icommerce_PdfCustomiser_Model_Fonts_Manager $fontsManager */
        $fontsManager = Mage::getSingleton('pdfcustomiser/fonts_manager');
        foreach ($fontsManager->getPdfCustomFonts() as $fontName => $fontFilePath) {
            TCPDF_FONTS::addTTFfont($fontFilePath, 'TrueTypeUnicode', '', 32);
        }
    }

    /**
     * @param $shipping
     *
     * @return string
     */
    public function renderShippingAddress($shipping)
    {
        $shippingAddress = '';
        if ($shipping instanceof Mage_Sales_Model_Order_Address) {
            $shippingAddress = $shipping->getData('firstname') . " " . $shipping->getData('lastname') . "<br />";

            $shippingAddress .= ($shipping->getData('telephone') != '' ? $shipping->getData('telephone') . "<br/>" : '');

            if ($shipping->getData('care_of') && $shipping->getData('care_of') != "") {
                $shippingAddress .= "c/o " . $shipping->getData('care_of') . "<br />";
            }

            $shippingAddress .= ($shipping->getData('company') != '' ? $shipping->getData('company') . "<br/>" : '') .
                $shipping->getData('street') . "<br />" .
                $shipping->getData('postcode') . " " .
                $shipping->getData('city') . "<br />" .
                Mage::getModel('directory/country')
                    ->load($shipping->getData('country_id'))
                    ->getName();
        }
        return $shippingAddress;
    }

    protected function _getAddressTitle($addressType, $title, $order = null)
    {
        $titleTransport = new Varien_Object(array('title' => $title));
        Mage::dispatchEvent('icommerce_pdfcustomiser_pdf_' . $addressType .'_address_title_before', array('order' => $order, 'transport' => $titleTransport));
        return $titleTransport->getTitle();
    }

    protected function _getBillingAddressTitle($order = null, $title = null)
    {
        return $this->_getAddressTitle('billing', $title, $order) ?: Mage::helper('sales')->__('SOLD TO:');
    }

    protected function _getShippingAddressTitle($order = null, $title = null)
    {
        return $this->_getAddressTitle('shipping', $title, $order) ?: Mage::helper('sales')->__('SHIP TO:');
    }
}
