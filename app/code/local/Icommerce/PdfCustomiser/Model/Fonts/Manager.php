<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Icommerce_PdfCustomiser
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 * @author      Vaimo AB Team
 */

/**
 * Responsible for operations with custom(module) fonts.
 *
 * Class Icommerce_PdfCustomiser_Model_Fonts_Manager
 */
class Icommerce_PdfCustomiser_Model_Fonts_Manager
{
    protected static $isInitiated = false;
    const XML_PATH_SALES_PDF_MODULE_FONTS_DIR = 'sales_pdf/fonts_dir';

    protected static $_fontsOptions;
    protected static $_customFonts;

    public function __construct()
    {
        if (!self::$isInitiated) {
            self::$_customFonts = array();
            self::$_fontsOptions = array();
            $this->_init();
        }
    }

    /**
     * @return array
     */
    public function getFontsOptions()
    {
        return self::$_fontsOptions;
    }

    /**
     * @return array
     */
    public function getPdfCustomFonts()
    {
        return self::$_customFonts;
    }

    /**
     * Prepare arrays with fonts.
     */
    protected function _init()
    {
        $this->_addDefaultFontsToOptions();
        $this->_addFontsFromModule();
        self::$isInitiated = true;
    }

    /**
     * Preparing fonts from the module, for future adding them to the TCPDF object.
     */
    protected function _addFontsFromModule()
    {
        $fontsDir = $this->_getCustomFontsDirPath();
        $fontFiles = $this->_getCustomFonts();

        foreach($fontFiles as $fontFileName) {
            $fontPath = $fontsDir . DS . $fontFileName;
            $fontName = $this->_getFontNameByPath($fontPath);

            self::$_customFonts[$fontName] = $fontPath;
            self::$_fontsOptions[] = array(
                'value' => $fontName,
                'label' => Mage::helper('pdfcustomiser')->__($fontFileName)
            );
        }
    }

    /**
     * Functionality borrowed from the method TCPDF_FONTS::addTTFfont()
     *
     * @param $fontPath
     *
     * @return mixed|string
     */
    protected function _getFontNameByPath($fontPath)
    {
        $fontPathParts = pathinfo($fontPath);
        if (!isset($fontPathParts['filename'])) {
            $fontPathParts['filename'] = substr(
                $fontPathParts['basename'], 0, -(strlen($fontPathParts['extension']) + 1)
            );
        }
        $fontName = strtolower($fontPathParts['filename']);
        $fontName = preg_replace('/[^a-z0-9_]/', '', $fontName);
        $search  = array('bold', 'oblique', 'italic', 'regular');
        $replace = array('b', 'i', 'i', '');
        $fontName = str_replace($search, $replace, $fontName);
        if (empty($fontName)) {
            // set generic name
            $fontName = 'tcpdffont';
        }
        return $fontName;
    }

    /**
     * @return array
     */
    protected function _getCustomFonts()
    {
        $fontsDir = $this->_getCustomFontsDirPath();
        $fontFiles = array();
        if (is_dir($fontsDir) && is_readable($fontsDir)) {
            $currentPath = getcwd();
            chdir($fontsDir);
            $fontFiles = glob('*.ttf');
            chdir($currentPath);
        }
        return $fontFiles;
    }

    /**
     * @return string
     */
    protected function _getCustomFontsDirPath()
    {
        $dirContainingModuleFontas = Mage::getStoreConfig('sales_pdf/fonts_dir');
        $fontsDir = Mage::getModuleDir('', 'Icommerce_PdfCustomiser') . DS . $dirContainingModuleFontas;
        $fontsDir = realpath($fontsDir);
        return $fontsDir;
    }

    /**
     * Adding default TCPDF fonts to the options array for the model Icommerce_PdfCustomiser_Model_System_Fonts
     * which is displaying them at the admin panel.
     */
    protected function _addDefaultFontsToOptions()
    {
        self::$_fontsOptions = array(
            array('value' => 'aealarabiya', 'label' => Mage::helper('pdfcustomiser')->__('AlArabiya')),
            array('value' => 'courier', 'label' => Mage::helper('pdfcustomiser')->__('Courier')),
            array('value' => 'dejavusans', 'label' => Mage::helper('pdfcustomiser')->__('DejaVuSans')),
            array('value' => 'dejavusanscondensed',
                  'label' => Mage::helper('pdfcustomiser')->__('DejaVuSansCondensed')),
            array('value' => 'dejavusansmono', 'label' => Mage::helper('pdfcustomiser')->__('DejaVuSansMono')),
            array('value' => 'dejavuserif', 'label' => Mage::helper('pdfcustomiser')->__('DejaVuSerif')),
            array('value' => 'dejavuserifcondensed',
                  'label' => Mage::helper('pdfcustomiser')->__('DejaVuSerifCondensed')),
            array('value' => 'freemono', 'label' => Mage::helper('pdfcustomiser')->__('FreeMono')),
            array('value' => 'freesans', 'label' => Mage::helper('pdfcustomiser')->__('FreeSans')),
            array('value' => 'freeserif', 'label' => Mage::helper('pdfcustomiser')->__('FreeSerif')),
            array('value' => 'aefurat', 'label' => Mage::helper('pdfcustomiser')->__('Furat')),
            array('value' => 'helvetica', 'label' => Mage::helper('pdfcustomiser')->__('Helvetica')),
            array('value' => 'symbol', 'label' => Mage::helper('pdfcustomiser')->__('Symbol')),
            array('value' => 'times', 'label' => Mage::helper('pdfcustomiser')->__('Times New Roman')),
            array('value' => 'zapfdingbats', 'label' => Mage::helper('pdfcustomiser')->__('ZapfDingbats')),
        );
    }
}
