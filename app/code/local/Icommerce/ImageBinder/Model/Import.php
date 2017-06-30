<?php
/**
 * Copyright (c) 2009-2012 Vaimo AB
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
 * @package     Icommerce_ImageBinder
 * @copyright   Copyright (c) 2009-2012 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 * @author      Allan Paiste <allan.paiste@vaimo.com>
 */

class Icommerce_ImageBinder_Model_Import
{
    protected $_count = 0;
    protected $_read = null;
    protected $_write = null;
    protected $_galleryTable = null;
    protected $_galleryValueTable = null;
    protected $_galleryAttributeId = null;
    protected $_extensions = array('jpg', 'jpeg', 'gif', 'png');
    protected $_images = array();
    protected $_copiedFiles = array();
    protected $_durations = array();

    protected function _log($message)
    {
        Mage::log($message, null, 'imagebinder.log', true);
        echo $message . "\n";
        flush();
        @ob_flush();
    }

    protected function _getConfigValue($code)
    {
        return Mage::getStoreConfig('imagebinder/settings/' . $code);
    }

    /**
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getRead()
    {
        if (!$this->_read) {
            $this->_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        }

        return $this->_read;
    }

    /**
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getWrite()
    {
        if (!$this->_write) {
            $this->_write = Mage::getSingleton('core/resource')->getConnection('core_write');
        }

        return $this->_write;
    }

    protected function _getGalleryTable()
    {
        if (!$this->_galleryTable) {
            $this->_galleryTable = Mage::getSingleton('core/resource')->getTableName('catalog/product_attribute_media_gallery');
        }

        return $this->_galleryTable;
    }

    protected function _getGalleryValueTable()
    {
        if (!$this->_galleryValueTable) {
            $this->_galleryValueTable = Mage::getSingleton('core/resource')->getTableName('catalog/product_attribute_media_gallery_value');
        }

        return $this->_galleryValueTable;
    }

    protected function _getGalleryAttributeId()
    {
        if (!$this->_galleryAttributeId) {
            $this->_galleryAttributeId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'media_gallery')->getId();
        }

        return $this->_galleryAttributeId;
    }

    /**
     * Retrive media config
     *
     * @return Mage_Catalog_Model_Product_Media_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('catalog/product_media_config');
    }

    public function getImportPath()
    {
        $path = $this->_getConfigValue('import_path');

        if (substr($path, 0, 1) != DS) {
            $path = DS . $path;
        }

        if (substr($path, -1, 1) != DS) {
            $path .= DS;
        }

        return Mage::getBaseDir() . $path;
    }

    protected function _findProducts(&$fileData)
    {
        $matchMode = $this->_getConfigValue('attribute_match_mode');
        switch ($matchMode) {
            case Icommerce_ImageBinder_Model_Adminhtml_Source_Matchmode::MATCHMODE_EXACT:
                $condition = array('eq' => $fileData['attribute_value']);
                break;
            case Icommerce_ImageBinder_Model_Adminhtml_Source_Matchmode::MATCHMODE_CONTAINS:
                $condition = array('like' => '%' . $fileData['attribute_value'] . '%');
                break;
            case Icommerce_ImageBinder_Model_Adminhtml_Source_Matchmode::MATCHMODE_STARTSWITH:
                $condition = array('like' => $fileData['attribute_value'] . '%');
                break;
            case Icommerce_ImageBinder_Model_Adminhtml_Source_Matchmode::MATCHMODE_ENDSWITH:
                $condition = array('like' => '%' . $fileData['attribute_value']);
                break;
            default:
                $condition = '';
                break;
        }

        $productIds = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToFilter($fileData['attribute_code'], $condition)
            ->getAllIds();

        Mage::dispatchEvent('imagebinder_find_products', array('file_data' => &$fileData, 'product_ids' => &$productIds));

        return $productIds;
    }

    protected function _readFiles()
    {
        $startTime = microtime(true);
        $path = $this->getImportPath();
        $this->_log(Mage::helper('imagebinder')->__('Reading files: %s', $path));

        if (!@chdir($path)) {
            throw new Exception(Mage::helper('imagebinder')->__('Path does not exist'));
        }

        $regex = $this->_getConfigValue('regex');
        $attributeCode = $this->_getConfigValue('attribute_code');
        $attributeField = (int)$this->_getConfigValue('attribute_field');
        $sortOrderField = (int)$this->_getConfigValue('sortorder_field');
        $imageTypeField = (int)$this->_getConfigValue('imagetype_field');
        $checkFileAge = (int)$this->_getConfigValue('check_file_age');

        foreach (glob('*') as $filename) {
            if (!is_file($filename)) {
                continue;
            }

            if (!in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $this->_extensions)) {
                continue;
            }

            $subject = pathinfo($filename, PATHINFO_FILENAME);
            $matches = array();

            if (!preg_match('/' . $regex . '/', $subject, $matches)) {
                continue;
            }

            $this->_log($filename);

            $age = time() - filemtime($filename);

            if ($checkFileAge == Icommerce_ImageBinder_Model_Adminhtml_Source_Check::CHECK_AGE_SKIP && $age < 60) {
                $this->_log('Skipping file due to age check');
                continue;
            }

            if ($checkFileAge == Icommerce_ImageBinder_Model_Adminhtml_Source_Check::CHECK_AGE_ABORT && $age < 60) {
                Mage::throwException(Mage::helper('imagebinder')->__('Aborting bind due to age check'));
            }

            $fileData = array(
                'filename'          => $filename,
                'attribute_code'    => $attributeCode,
                'attribute_value'   => (isset($matches[$attributeField]) ? $matches[$attributeField] : ''),
                'label'             => null,
                'position'          => (isset($matches[$sortOrderField]) ? (int)$matches[$sortOrderField] : 0),
                'image'             => false,
            );

            if (isset($matches[$imageTypeField])) {
                switch (strtolower($matches[$imageTypeField])) {
                    case 'st':
                        $isSmallImage = true;
                        $isThumbnail = true;
                        break;
                    case 's':
                        $isSmallImage = true;
                        $isThumbnail = false;
                        break;
                    case 't':
                        $isSmallImage = false;
                        $isThumbnail = true;
                        break;
                    default:
                        $isSmallImage = false;
                        $isThumbnail = false;
                        break;
                }
            } else {
                $isSmallImage = false;
                $isThumbnail = false;
            }

            $fileData['small_image'] = $isSmallImage;
            $fileData['thumbnail'] = $isThumbnail;

            foreach ($this->_findProducts($fileData) as $productId) {
                $this->_images[$productId][] = array(
                    'filename'      => $fileData['filename'],
                    'label'         => $fileData['label'],
                    'position'      => $fileData['position'],
                    'image'         => $fileData['image'],
                    'small_image'   => $fileData['small_image'],
                    'thumbnail'     => $fileData['thumbnail'],
                );
            }
        }

        $this->_durations['Reading files'] = microtime(true) - $startTime;
    }

    protected function _deleteRecursive($path)
    {
        foreach (glob($path) as $file) {
            if (is_dir($file)) {
                $this->_deleteRecursive($file . '/*');
            } else if (in_array(basename($file), $this->_copiedFiles)) {
//                $this->_log($file);
                unlink($file);
            }
        }
    }

    protected function _bindImages($removeExistingImages, $operationId)
    {
        $startTime = microtime(true);
        $this->_log(Mage::helper('imagebinder')->__('Adding images to gallery'));

        $progressMin = 0;
        $progressMax = count($this->_images);
        $progressPos = 0;

        foreach ($this->_images as $productId => $images) {
            $files = array();

            if ($removeExistingImages) {
                $this->_getWrite()->delete($this->_getGalleryTable(), 'entity_id = ' . $productId);
            }

            $images = $this->_checkImages($productId, $images);

            foreach ($images as $image) {
                if (isset($image['existing']) && $image['existing']) {
                    continue;
                }

                $this->_addImageToGallery($productId, $image);

                $files[] = $image['filename'];
                $this->_count++;
            }

            $this->_log(Mage::helper('imagebinder')->__('%s: %s', $productId, implode(', ', $files)));
            Mage::dispatchEvent('imagebinder_bind_product_after', array('product_id' => $productId, 'images' => $images));

            if ($operationId) {
                Mage::helper('scheduler')->setOperationProgress($operationId, $progressMin, $progressMax, ++$progressPos);
            }
        }

        $this->_log('');
        $this->_durations['Binding images'] = microtime(true) - $startTime;
    }

    protected function _addImageToGallery($productId, $image)
    {
        $file = realpath($image['filename']);

        if (!$file || !file_exists($file)) {
            Mage::throwException(Mage::helper('catalog')->__('Image does not exist.'));
        }

        $pathinfo = pathinfo($file);

        if (!isset($pathinfo['extension']) || !in_array(strtolower($pathinfo['extension']), $this->_extensions)) {
            Mage::throwException(Mage::helper('catalog')->__('Invalid image file type.'));
        }

        $fileName = Varien_File_Uploader::getCorrectFileName($image['filename']);
        $dispretionPath = Varien_File_Uploader::getDispretionPath($fileName);
        $fileName       = $dispretionPath . DS . $fileName;

        if (!in_array(basename($fileName), $this->_copiedFiles)) {
            $ioAdapter = new Varien_Io_File();
            $ioAdapter->setAllowCreateFolders(true);
            $mediaFilePath = $this->_getConfig()->getMediaPath($fileName);
            $destinationDirectory = dirname($mediaFilePath);

            try {
                //Check the result of each method (open, cp, chmod) because these can suppress errors.
                if (!$ioAdapter->open(array('path' => $destinationDirectory))) {
                    Mage::throwException(Mage::helper('catalog')->__('Could not open destination directory. [%s]', $destinationDirectory));
                }
                if (!$ioAdapter->cp($file, $mediaFilePath)) {
                    Mage::throwException(Mage::helper('catalog')->__('Could not copy to destination. [%s] -> [%s]', $file, $mediaFilePath));
                }
                if (!$ioAdapter->chmod($mediaFilePath, 0777)) {
                    Mage::throwException(Mage::helper('catalog')->__('Could not change destination file permissions. [%s]', $mediaFilePath));
                }
            } catch (Exception $e) {
                Mage::throwException(Mage::helper('catalog')->__('Failed to move file: %s', $e->getMessage()));
            }

            $this->_copiedFiles[] = basename($fileName);
        }

        // check if this image is already in database, if so, then just update label and position
        $sql = $this->_getRead()
            ->select()
            ->from($this->_getGalleryTable(), 'value_id')
            ->where('attribute_id = ?', $this->_getGalleryAttributeId())
            ->where('entity_id = ?', $productId)
            ->where('value = ?', $fileName);

        if ($valueId = $this->_getRead()->fetchOne($sql)) {
            $bind = array(
                'label' => $image['label'],
                'position' => $image['position'],
            );

            $where = array(
                'value_id = ?' => $valueId,
                'store_id = ?' => 0,
            );

            $this->_getWrite()->update($this->_getGalleryValueTable(), $bind, $where);
        } else {
            $bind = array(
                'attribute_id' => $this->_getGalleryAttributeId(),
                'entity_id' => $productId,
                'value' => $fileName
            );

            $this->_getWrite()->insert($this->_getGalleryTable(), $bind);

            $bind = array(
                'value_id' => $this->_getWrite()->lastInsertId($this->_getGalleryTable(), 'value_id'),
                'store_id' => 0,
                'label' => $image['label'],
                'position' => $image['position'],
                'disabled' => 0,
            );

            $this->_getWrite()->insert($this->_getGalleryValueTable(), $bind);
        }

        /** @var Mage_Catalog_Model_Resource_Product_Action $updater */
        $updater = Mage::getResourceSingleton('catalog/product_action');

        if ($image['image']) {
            $updater->updateAttributes(array($productId), array('image' => $fileName), 0);
        }

        if ($image['small_image']) {
            $updater->updateAttributes(array($productId), array('small_image' => $fileName), 0);
        }

        if ($image['thumbnail']) {
            $updater->updateAttributes(array($productId), array('thumbnail' => $fileName), 0);
        }
    }

    protected function _comparePosition($a, $b)
    {
        if ($a['position'] > $b['position']) {
            return 1;
        } elseif ($a['position'] < $b['position']) {
            return -1;
        } else {
            return 0;
        }
    }

    protected function _isInImages($filename, array $images)
    {
        foreach ($images as $image) {
            if ($image['filename'] == $filename) {
                return true;
            }
        }

        return false;
    }

    protected function _checkImages($productId, $images)
    {
        if (!$images) {
            return $images;
        }

        // get existing images for product
        $select = $this->_getRead()
            ->select()
            ->from(array('g' => $this->_getGalleryTable()), 'value')
            ->join(array('v' => $this->_getGalleryValueTable()), 'v.value_id = g.value_id AND v.store_id = 0', array('label', 'position'))
            ->where('g.entity_id = ?', $productId);

        $baseImage = Mage::getResourceSingleton('catalog/product')->getAttributeRawValue($productId, 'image', 0);
        $smallImage = Mage::getResourceSingleton('catalog/product')->getAttributeRawValue($productId, 'small_image', 0);
        $thumbnail = Mage::getResourceSingleton('catalog/product')->getAttributeRawValue($productId, 'thumbnail', 0);

        foreach ($this->_getRead()->fetchAll($select) as $row) {
            if ($this->_isInImages(basename($row['value']), $images)) {
                continue;
            }
            $images[] = array(
                'filename'      => basename($row['value']),
                'label'         => $row['label'],
                'position'      => (int)$row['position'],
                'image'         => $row['value'] == $baseImage,
                'small_image'   => $row['value'] == $smallImage,
                'thumbnail'     => $row['value'] == $thumbnail,
                'existing'      => true,
            );
        }

        usort($images, array($this, '_comparePosition'));
        $isImageFound = false;
        $isSmallImageFound = false;
        $isThumbnailFound = false;

        foreach ($images as &$image) {
            if ($image['image']) {
                if ($isThumbnailFound) {
                    $image['image'] = false;
                } else {
                    $isImageFound = true;
                }
            }

            if ($image['small_image']) {
                if ($isSmallImageFound) {
                    $image['small_image'] = false;
                } else {
                    $isSmallImageFound = true;
                }
            }

            if ($image['thumbnail']) {
                if ($isThumbnailFound) {
                    $image['thumbnail'] = false;
                } else {
                    $isThumbnailFound = true;
                }
            }
        }

        if (!$isImageFound) {
            $images[0]['image'] = true;
        }

        if (!$isSmallImageFound) {
            $images[0]['small_image'] = true;
        }

        if (!$isThumbnailFound) {
            $images[0]['thumbnail'] = true;
        }

        return $images;
    }

    protected function _moveSuccessfulFiles()
    {
        $startTime = microtime(true);
        $folder = '_imported';
        $this->_log(Mage::helper('imagebinder')->__('Moving successfully bound images'));

        if (!is_dir($folder)) {
            if (!mkdir($folder)) {
                throw new Exception(Mage::helper('imagebinder')->__('Could not create folder for imported files'));
            }
        }

        foreach ($this->_images as $images) {
            foreach ($images as $image) {
                rename($image['filename'], $folder . DS . $image['filename']);
            }
        }

        $this->_log('');
        $this->_durations['Moving files'] = microtime(true) - $startTime;
    }

    protected function _deleteSuccessfulFiles()
    {
        $startTime = microtime(true);
        $this->_log(Mage::helper('imagebinder')->__('Deleting successfully bound images'));

        foreach ($this->_images as $images) {
            foreach ($images as $image) {
                unlink($image['filename']);
            }
        }

        $this->_log('');
        $this->_durations['Deleting files'] = microtime(true) - $startTime;
    }

    public function bindAll($operationId = 0)
    {
        $this->_log(Mage::helper('imagebinder')->__('Image bind started'));
        $this->_log('');
        $this->_readFiles();
        $this->_log('');

        $this->_bindImages($this->_getConfigValue('remove_before_import'), $operationId);

        switch ($this->_getConfigValue('move_failsuccess_path')) {
            case Icommerce_ImageBinder_Model_Adminhtml_Source_Treat::TREAT_IMPORTED_MOVE:
                $this->_moveSuccessfulFiles();
                break;
            case Icommerce_ImageBinder_Model_Adminhtml_Source_Treat::TREAT_IMPORTED_DELETE:
                $this->_deleteSuccessfulFiles();
                break;
        }

        $this->_log(Mage::helper('imagebinder')->__('%d image(s) bound', $this->_count));
        $this->_log('');

        if ($this->_getConfigValue('flush_images_cache')) {
            $startTime = microtime(true);
            $this->_deleteRecursive(Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath() . DS . 'cache/*');
            $this->_durations['Flushing cache'] = microtime(true) - $startTime;
        }

        if (count($this->_durations)) {
            $this->_log('Durations');
            foreach ($this->_durations as $key => $value) {
                $this->_log($key . ': ' . $value);
            }
        }

        return $this->_count;
    }
}