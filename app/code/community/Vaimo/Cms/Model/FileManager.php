<?php
/**
 * Copyright (c) 2009-2017 Vaimo Group
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
 * @package     Vaimo_Cms
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Vaimo_Cms_Model_FileManager extends Vaimo_Cms_Model_Abstract
{
    /**
     * @var Mage_Cms_Helper_Wysiwyg_Images
     */
    protected $_imagesHelper;

    /**
     * @var Varien_Io_File
     */
    protected $_io;

    /**
     * @var Mage_Cms_Model_Wysiwyg_Images_Storage
     */
    protected $_storage;

    /**
     * Base directory where all the uploaded files will be placed
     */
    const STORAGE_DIR = 'CMS';

    public function __construct(array $args = array())
    {
        parent::__construct($args);

        $this->_imagesHelper = isset($args['imagesHelper']) ?
            $args['imagesHelper'] : $this->getFactory()->getHelper('cms/wysiwyg_images');

        $this->_io = isset($args['io']) ?
            $args['io'] : new Varien_Io_File();
    }

    public function isImage($filename)
    {
        return $this->_imagesHelper->getStorage()->isImage($filename);
    }

    public function getFileSize($filename)
    {
        return filesize($filename);
    }

    public function getStorageUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) .
            Mage_Cms_Model_Wysiwyg_Config::IMAGE_DIRECTORY . '/' . Vaimo_Cms_Model_FileManager::STORAGE_DIR . '/';
    }

    /**
     * Get path to files where they are stored.
     * Used when dealing with images in the editor.
     *
     * @param null $filename
     * @param bool $thumbnail
     * @return string
     */
    public function getStoragePath($filename = null, $thumbnail = false)
    {
        $path = $this->_imagesHelper->getStorageRoot() . Vaimo_Cms_Model_FileManager::STORAGE_DIR . DS;

        if ($filename !== null) {
            $filename = basename($filename);
            $filename = preg_replace('/\?(.*)/', '', $filename);
            $path .= $filename;
        }

        if ($thumbnail) {
            $path = $this->_getStorage()->getThumbsPath($path) . DS;
            $path .= $filename;
        }

        return $path;
    }

    public function _getStorage()
    {
        if (!$this->_storage) {
            $this->_storage = $this->_imagesHelper->getStorage();
        }

        return $this->_storage;
    }

    public function storagePathExists()
    {
        return file_exists($this->getStoragePath());
    }

    public function getFilesCollection($path)
    {
        $storagePath = $this->getStoragePath($path);
        $storage = $this->_imagesHelper->getStorage();

        if (!$this->_io->fileExists($storagePath, false)) {
            $this->_io->mkdir($storagePath);
        }

        return $storage->getFilesCollection($storagePath);
     }

    /**
     * Copied from Mage_Cms_Model_Wysiwyg_Images_Storage->uploadFile because we need to set the $fileId of
     * Mage_Core_Model_File_Uploader to file instead of image.
     *
     * @param $type
     * @return bool|void
     */
    public function uploadFile($type)
    {
        $targetPath = $this->getStoragePath();
        $storage = $this->_imagesHelper->getStorage();

        $uploader = new Mage_Core_Model_File_Uploader($type);
        if ($allowed = $storage->getAllowedExtensions($type)) {
            $uploader->setAllowedExtensions($allowed);
        }
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $result = $uploader->save($targetPath);

        if (!$result) {
            Mage::throwException(
                $this->getFactory()->getHelper('cms')->__('Cannot upload file.')
            );
        }

        $storage->resizeFile($targetPath . DS . $uploader->getUploadedFileName(), true);

        return $result;
    }

    public function saveImage($image)
    {
        $filename = $this->getStoragePath($image->getPath());

        file_put_contents($filename, $image->getData());
        $this->_imagesHelper->getStorage()->resizeFile($filename, true);

        return true;
    }

    public function deleteFile($file)
    {
        $targetFile = $this->getStoragePath($file);
        $this->_imagesHelper->getStorage()->deleteFile($targetFile);

        return true;
    }
}