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

class Vaimo_Cms_Model_Editor_Raptor extends Vaimo_Cms_Model_Abstract
{
    /**
     * @var Vaimo_Cms_Model_FileManager
     */
    protected $_fileManager;

    public function __construct(array $args = array())
    {
        parent::__construct($args);

        $this->_fileManager = isset($args['fileManager']) ?
            $args['fileManager'] : $this->getFactory()->getModel('vaimo_cms/fileManager');
    }

    public function getStoragePath($filename, $thumbnail = false)
    {
        return $this->_fileManager->getStoragePath($filename, $thumbnail);
    }

    /**
     * Get a list of files.
     *
     * Requested by the file manager when it wants to list files.
     *
     * @param $path
     * @param $start
     * @param $limit
     * @param $searchString
     * @return array
     */
    public function getFiles($path, $start, $limit, $searchString)
    {
        $filesCollection = $this->_fileManager->getFilesCollection($path);

        $files = array();
        foreach ($filesCollection as $item) {
            $name = $item->getName();
            $filename = $item->getFilename();

            if (!empty($searchString) && stristr($name, $searchString) === false) {
                continue;
            }

            $fileSize = $this->_fileManager->getFileSize($filename);

            $file = array(
                'name' => $name,
                'type' => pathinfo($filename, PATHINFO_EXTENSION),
                'size' => $fileSize,
                'mtime' => $item->getMtime(),
                'tags' => array()
            );

            if ($this->_fileManager->isImage($filename)) {
                $file['tags'] = array('Image');
            }

            $files[] = $file;
        }

        $total = $filesCollection->getSize();
        $filteredTotal = count($files);

        return array(
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'filteredTotal' => $filteredTotal,
            'tags' => array(
                'Image'
            ),
            // Disable directories functionality because cant get it to work
            // "Subdirectory support is still experimental.", https://www.raptor-editor.com/documentation/tutorials/file-manager
            'directories' => array(),
            'files' => array_slice($files, $start, $limit)
        );
    }

    /**
     * Upload a file.
     *
     * Used when a user uploads a file in the file manager.
     *
     * @param $type
     * @return bool|void
     */
    public function uploadFile($type)
    {
        return $this->_fileManager->uploadFile($type);
    }

    /**
     * Save an image.
     *
     * Used when an image is saved either by editing a file in the file manager or in the image editor.
     *
     * @param $image
     * @return string
     */
    public function saveImage($image)
    {
        return $this->_fileManager->saveImage($image) ? 'true': 'false';
    }

    /**
     * Delete a file.
     *
     * Used when a file is deleted in Raptor File Manager.
     *
     * @param $file
     * @return string
     */
    public function deleteFile($file)
    {
        return $this->_fileManager->deleteFile($file) ? 'true': 'false';
    }
}