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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Icommerce_SlideshowManager
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 */

class Icommerce_SlideshowManager_Helper_Data extends Mage_Core_Helper_Abstract
{

    // FIXME why such limit - 0.5MB ?
    const MAX_ALLOWED_FILE_SIZE = 512000; // bytes
    const TARGET_PATH = 'upload/slideshow/items/';

    /** @var array[] */
    protected $_configValueHolder = array();

    protected $_allowedFileTypes = array('image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png', 'image/gif');
    protected $_allowedFileExtensions = array('jpg', 'jpeg', 'png', 'gif');

    protected $_statuses = array('0' => 'Not Active', '1' => 'Active');

    protected $_withAndHeightType = array('px' => 'px', '%' => '%');

    protected $_align = array('left' => 'left', 'center' => 'center', 'right' => 'right');

    protected $_titlePosition = array('top' => 'Top', 'middle' => 'Middle');

    // FIXME what color is "gant-blue" ? If project specific then it's in wrong place
    protected $_color = array('dark' => 'Dark', 'light' => 'Light', 'gant-blue' => 'Gant-Blue');

    protected $_yesNo = array('yes' => 'Yes', 'no' => 'No');

    public function getStatuses()
    {
        return $this->_statuses;
    }

    public function getYesNo()
    {
        return $this->_yesNo;
    }

    public function getAlign()
    {
        return $this->_align;
    }

    public function getTitlePosition()
    {
        return $this->_titlePosition;
    }

    public function getColor()
    {
        return $this->_color;
    }

    public function getWithAndHeightType()
    {
        return $this->_withAndHeightType;
    }

    public function getMaxAllowedFileSize()
    {
        return self::MAX_ALLOWED_FILE_SIZE;
    }

    public function getAbsoluteTargetPath()
    {
        $absoluteTargetPath = Mage::getBaseDir() .'/media/'. self::TARGET_PATH;

        $parts = explode('/', $absoluteTargetPath);
        $absoluteTargetPath = implode(DS, $parts);

        if (is_dir($absoluteTargetPath) === false) {
            $response = mkdir($absoluteTargetPath, 0777, true);
        }

        if (is_dir($absoluteTargetPath) === false) {
            throw new Exception( $this->__('Could not create the folder and set the rights for %s.', $absoluteTargetPath ) );
        }

        if (is_writable($absoluteTargetPath) === false) {
            $response = chmod($absoluteTargetPath, 0777);
        }

        if (is_writable($absoluteTargetPath) === false) {
            throw new Exception( $this->__('Could not set write permissions on folder %s.', $absoluteTargetPath ) );
        }

        return $absoluteTargetPath;
    }

    public function getTargetPath($withMediaFolder = false)
    {

        $targetPath = self::TARGET_PATH;

        if ($withMediaFolder) {
            $targetPath = 'media/' . $targetPath;
        }

        return $targetPath;
    }

    public function getBackgroundImageUrl($item)
    {
        return str_replace('/index.php', '', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $this->getTargetPath() . $item['backgroundimage']);
    }

    public function getBackgroundImageTabletUrl($item)
    {
        $image = $item['backgroundimage_tablet'];
        if ($image == null) {
            return '';
        }
        return str_replace('/index.php', '', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $this->getTargetPath() . $image);
    }

    public function getBackgroundImageMobileUrl($item)
    {
        $image = $item['backgroundimage_mobile'];
        if ($image == null) {
            return '';
        }
        return str_replace('/index.php', '', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $this->getTargetPath() . $image);
    }


    public function getImageUrl($item)
    {
        return str_replace('/index.php', '', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $this->getTargetPath() . $item['filename']);
    }

    public function isFileTypeAllowed($fileType)
    {
        return in_array($fileType, $this->_allowedFileTypes);
    }

    public function isFileExtensionAllowed($fileExtension)
    {
        return in_array($fileExtension, $this->_allowedFileExtensions);
    }


    /**
     * Activate HTML blocks or not
     *
     * @param   none
     * @return  bool
     */
    public function isHtmlActive()
    {
        return (bool )$this->_getConfigValue('html_active');
    }

    /**
     * Activate Easy blocks or not
     *
     * @param   none
     * @return  bool
     */
    public function isEasyActive()
    {
        return (bool )$this->_getConfigValue('easy_active');
    }

    /**
     * Activate layered HTML blocks or not
     *
     * @param   none
     * @return  bool
     */
    public function isLayeredHtmlActive()
    {
        return (bool )$this->_getConfigValue('layered_html_active');
    }

    /**
     * Tells you whether the product type is active or not.
     *
     * @return bool
     */
    public function isProductActive()
    {
        return (bool )$this->_getConfigValue('product_active');
    }

    /**
     * Activate responsive imagetext or not
     *
     * @param   none
     * @return  bool
     */
    public function isResponsiveImagetext()
    {
        return (bool )$this->_getConfigValue('responsive_imagetext');
    }

    /**
     * Cache locally 'slideshowmanager' configuration values
     *
     * @param $name
     * @param string $section
     * @return null|string
     */
    protected function _getConfigValue($name, $section = 'settings')
    {
        if (!isset($this->_configValueHolder[$section])) {
            $this->_configValueHolder[$section] = (array )Mage::getStoreConfig('slideshowmanager/' . $section);
        }
        if (!isset($this->_configValueHolder[$section][$name])) {
            return null;
        }

        return $this->_configValueHolder[$section][$name];
    }

    public function getUniqFilename($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $filename = date('Y-m-d-h-m-s', Mage::getModel('core/date')->timestamp(time()));
        $filename .= rand();
        $filename .= '.' . $extension;

        return $filename;
    }
}
