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

class Icommerce_PdfCustomiser_Helper_Pdf extends Mage_Core_Helper_Abstract
{
    const XPATH_MAX_LOGO_HEIGHT = 'sales_pdf/all/all_max_logo_height';

    public function __construct($storeId = '1')
    {
        $this->setStoreId($storeId);
    }

    /**
     * storeId
     * @access protected
     */
    protected $_storeId;

    /**
     * get storeId
     * @return  int
     * @access public
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * set storeId
     * @return  void
     * @access public
     */
    public function setStoreId($id)
    {
        $this->_storeId = $id;
    }

    /**
     * store owner address
     * @access protected
     */
    protected $_PdfOwnerAddress;

    /**
     * store owner address
     * @return  string | false
     * @access public
     */
    public function getPdfOwnerAddresss()
    {
        $this->_PdfOwnerAddress = Mage::getStoreConfig('sales_pdf/all/allowneraddress', $this->getStoreId());
        if (empty($this->_PdfOwnerAddress)) {
            return false;
        }
        return $this->_PdfOwnerAddress;
    }

    /**
     * get store flag to display base and order currency
     * @return  bool
     * @access public
     */
    public function getDisplayBoth()
    {
        return Mage::getStoreConfig('sales_pdf/all/displayboth', $this->getStoreId());
    }

    /**
     * font for pdf - courier, times, helvetica
     * not embedded
     * @return  string
     * @access public
     */
    public function getPdfFont()
    {
        return Mage::getStoreConfig('sales_pdf/all/allfont', $this->getStoreId());
    }

    /**
     * fontsize
     * @access protected
     */
    protected $_PdfFontsize;

    /**
     * getfontsize
     * @param (otpional) $size  normal | large | small
     * @return  int
     * @access public
     */
    public function getPdfFontsize($size = 'normal')
    {
        $this->_PdfFontsize = (int)Mage::getStoreConfig('sales_pdf/all/allfontsize', $this->getStoreId());
        switch ($size) {
            case 'normal':
                return $this->_PdfFontsize;
                break;
            case 'large':
                return $this->_PdfFontsize * 1.33;
                break;
            case 'small':
                return $this->_PdfFontsize * ($this->_PdfFontsize < 12 ? 1 : 0.8);
                break;
            default:
                return $this->_PdfFontsize;
        }
    }


    /**
     * font for pdf - courier, times, helvetica
     * not embedded
     * @return  string
     * @access public
     */
    public function getPdfQtyAsInt()
    {
        return Mage::getStoreConfig('sales_pdf/all/allqtyasint', $this->getStoreId());
    }

    /**
     * path to print logo
     * @access protected
     */
    protected $_PdfLogo;

    /**
     * get path for print logo
     * @return string path information for logo
     * @access public
     */
    public function getPdfLogo()
    {
        if (Mage::getStoreConfig('sales_pdf/all/alllogo', $this->getStoreId())) {
            $this->_PdfLogo = BP . DS . 'media' . DS . 'pdf-printouts' . DS . Mage::getStoreConfig('sales_pdf/all/alllogo', $this->getStoreId());
            if (!is_file($this->_PdfLogo)) {
                $this->_PdfLogo = false;
            }
        } else {
            $this->_PdfLogo = false;
        }
        return $this->_PdfLogo;
    }

    /**
     * Logo Dimensions
     * @access protected
     */
    protected $_PdfLogoDimensions = array();

    /**
     * get Logo Dimensions
     * @param  (optional) $which identify the dimension to return  all | w | h
     * @return array |  int | bool
     * @access public
     */
    public function getPdfLogoDimensions($which = 'all')
    {
        if (!$this->getPdfLogo()) {
            return false;
        }

        list($width, $height, $type, $attr) = getimagesize($this->getPdfLogo());
        $this->_PdfLogoDimensions['width'] = $width;
        $this->_PdfLogoDimensions['height'] = $height;

        switch ($which) {
            case 'w':
                return $this->_PdfLogoDimensions['width'];
                break;
            case 'h-scaled':
                //calculate if image will be scaled apply factor to height
                $maxWidth = ($this->getPageWidth() / 2) - $this->getPdfMargins('sides');
                if ($this->getPdfLogoDimensions('w') > $maxWidth) {
                    $scaleFactor = $maxWidth / $this->getPdfLogoDimensions('w');
                } else {
                    $scaleFactor = 1;
                }
                return $scaleFactor * $this->_PdfLogoDimensions['height'];
                break;
            case 'h':
                return $this->_PdfLogoDimensions['height'];
                break;
            case 'all':
            default:
                return $this->_PdfLogoDimensions;
        }
    }

    /**
     * Page Margins
     * @access protected
     */
    protected $_PdfMargins = array();

    /**
     * get Margins
     * @param  (optional) $which identify the dimension to return  all | top | bottom | sides
     * @return array |  int
     * @access public
     */
    public function getPdfMargins($which = 'all')
    {
        $this->_PdfMargins['top']    = Mage::getStoreConfig('sales_pdf/all/allmargintop', $this->getStoreId());
        $this->_PdfMargins['bottom'] = Mage::getStoreConfig('sales_pdf/all/allmarginbottom', $this->getStoreId());
        $this->_PdfMargins['sides']  = Mage::getStoreConfig('sales_pdf/all/allmarginsides', $this->getStoreId());

        switch ($which) {
            case 'top':
                return $this->_PdfMargins['top'];
                break;
            case 'bottom':
                return $this->_PdfMargins['bottom'];
                break;
            case 'sides':
                return $this->_PdfMargins['sides'];
                break;
            case 'all':
            default:
                return $this->_PdfMargins;
        }
    }


    /**
     * get getPageWidth
     * @param  void
     * @return float
     * @access public
     */
    public function getPageWidth()
    {
        $pageSize = Mage::getStoreConfig('sales_pdf/all/allpagesize', $this->getStoreId());

        switch ($pageSize) {
            case 'A4':
                return 21.000155556 * 10;
                break;
            case 'letter':
                return 21.59 * 10;
                break;
            default:
                return 21.000155556 * 10;
        }
    }

    /**
     * return if we want to print comments and statusses
     * @param  void
     * @return bool
     * @access public
     */
    public function getPrintComments()
    {
        return Mage::getStoreConfig('sales_pdf/all/allprintcomments', $this->getStoreId());
    }

    /**
     * @return int
     */
    public function getMaxLogoHeight()
    {
        return (int)Mage::getStoreConfig(self::XPATH_MAX_LOGO_HEIGHT, $this->getStoreId());
    }
}