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
 * @category    Icommerce
 * @package     Icommerce_EmailAttachments
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 */
class Icommerce_EmailAttachments_Helper_Data extends Mage_Core_Helper_Abstract
{
    const EMAIL_ATTACHMENTS_DIRECTORY = 'email_attachments';
    const EMAIL_ATTACHMENTS_TEMP = 'temp';

    const XPATH_KEEP_ATTACHMENTS_IN_FILESYSTEM = 'sales_email/order/keep_attachments_in_filesystem';
    const XPATH_BASE_ATTACHMENTS_DIRECTORY = 'sales_email/order/base_attachments_directory';

    protected $_attachmentParamsSequence = array(
        'body', 'mime_type', 'disposition',
        'encoding', 'filename',
    );

    protected $_ioModel = null;
    protected $_baseDirectory = null;

    protected $_cwd = null;

    /**
     * Retrieve path of base directory to store email attachments
     *
     * @return string
     */
    protected function _getBaseDirectory()
    {
        if (is_null($this->_baseDirectory)) {
            $this->_baseDirectory = Mage::getBaseDir(Mage::getStoreConfig(self::XPATH_BASE_ATTACHMENTS_DIRECTORY));
        }
        return $this->_baseDirectory;
    }

    /**
     * Get Magento filesystem client
     *
     * @return Varien_Io_File
     */
    protected function _getFilesystemClient()
    {
        if (is_null($this->_ioModel)) {
            $this->_ioModel = new Varien_Io_File();
        }
        return $this->_ioModel;
    }

    /**
     * Read content of the file
     *
     * @param string $fileName
     * @param string $path
     * @return string
     */
    protected function _getFileContent($fileName, $path)
    {
        $content = '';
        $io = $this->_getFilesystemClient();
        $io->open();
        $io->cd($path);

        $filePath = $path . DS . $fileName;
        if (!$io->fileExists($filePath)) {
            Mage::throwException($this->__("File doesn't exist: %s", $filePath));
        }

        $io->streamOpen($fileName, 'r');
        $isFileLocked = $io->streamLock(true);
        if ($isFileLocked) {
            while (false !== ($line = $io->streamRead())) {
                $content .= $line;
            }
        } else {
            Mage::throwException($this->__("Couldn't get the lock for file: %s", $fileName));
        }

        $io->streamClose();
        return $content;
    }

    /**
     * Prepare of input parameters for Zend_Mail::createAttachment
     *
     * @param array $attachmentDetails
     * @param Mage_Core_Model_Email_Queue $queue
     * @throws Mage_Core_Exception
     *
     * @return boolean
     */
    protected function _prepareAttachmentParams($attachmentDetails, $queue)
    {
        if (!isset($attachmentDetails['body']) && !empty($attachmentDetails['filename'])) {
            if ($queue && $queue instanceof Mage_Core_Model_Email_Queue) {
                try {
                    $fileContent = $this->_extractAttachmentContentFromFile(
                        $attachmentDetails['filename'], $queue->getId()
                    );
                    if (!empty($fileContent)) {
                        $attachmentDetails['body'] = $fileContent;
                    }
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }

        return $this->_validateAttachmentDetails($attachmentDetails);
    }

    /**
     * Validate of input parameters before using them for Zend_Mail::createAttachment
     *
     * @param array $attachmentDetails
     *
     * @return array()
     */
    protected function _validateAttachmentDetails($attachmentDetails)
    {
        $paramsSequence = $this->getAttachmentParamSequence();
        $missedParamsCount = 0;
        $preparedParams = array();
        foreach ($paramsSequence as $param) {
            if (!empty($attachmentDetails[$param])) {
                $preparedParams[$param] = $attachmentDetails[$param];
            } else {
                $missedParamsCount++;
            }
        }
        if (!$missedParamsCount || ($missedParamsCount == 1 && !isset($preparedParams['filename']))) {
            return $preparedParams;
        } else {
            return array();
        }
    }

    /**
     * Extracts content of the email attachment, which is stored in filesystem
     *
     * @param string $filename
     * @param string|int $subdirPath
     * @throws Mage_Core_Exception
     *
     * @return string
     */
    protected function _extractAttachmentContentFromFile($filename, $subdirPath)
    {
        $attachmentsPath = $this->getEmailAttachmentsPath($subdirPath);
        $content = $this->_getFileContent($filename, $attachmentsPath);

        return $content;
    }

    /**
     * Delete a directory recursively
     *
     * @param string $path
     * @param bool $recursively
     *
     * @return Icommerce_EmailAttachments_Helper_Data
     */
    protected function _removeDirectory($path, $recursively = true)
    {
        $io = $this->_getFilesystemClient();
        $io->rmdir($path, $recursively);
        return $this;
    }

    /**
     * Retrieve path to temporary directory
     *
     * @param string|null $childDirectory
     *
     * @return string
     */
    protected function _getTempSubdirectoryRelativePath($childDirectory = null)
    {
        $path = self::EMAIL_ATTACHMENTS_TEMP;
        if (!empty($childDirectory)) {
            $path .= DS . $childDirectory;
        }
        return $path;
    }

    /**
     * Copy files from source directory to destination directory not recursively
     * (folders are not included)
     *
     * @param string $sourceDirectory
     * @param string $destinationDirectory
     *
     * @return Icommerce_EmailAttachments_Helper_Data
     */
    protected function _copyFilesOnly($sourceDirectory, $destinationDirectory)
    {
        $io = $this->_getFilesystemClient();
        $io->open();
        $io->checkAndCreateFolder($destinationDirectory);
        $io->cd($sourceDirectory);
        foreach (scandir($sourceDirectory) as $item) {
            if (!strcmp($item, '.') || !strcmp($item, '..')) {
                continue;
            }
            $sourceFile = $sourceDirectory . DS . $item;
            if (is_dir($sourceFile)) {
                continue;
            }
            $destinationFile = $destinationDirectory . DS . $item;
            $io->cp($sourceFile, $destinationFile);
        }

        return $this;
    }

    /**
     * Prepare of attachment related data for Mage_Core_Model_Email_Queue before save
     *
     * @param array $attachmentDetails
     * @param Mage_Core_Model_Email_Queue $queue
     *
     * @return boolean
     */
    public function _prepareAttachmentDataForQueue($attachmentDetails, $queue)
    {
        $isFileSystemUsed = false;

        if (!isset($attachmentDetails['filename'])) {
            $attachmentDetails['filename'] = md5(implode('__', $attachmentDetails));
        }

        $uniquePath = $queue->getUniquePath();
        if ($uniquePath) {
            $subdirPath = $this->_getTempSubdirectoryRelativePath($uniquePath);
            try {
                $result = $this->saveEmailAttachmentInFilesystem(
                    $attachmentDetails['filename'], $attachmentDetails['body'], $subdirPath
                );

                if ($result) {
                    $isFileSystemUsed = true;
                    unset($attachmentDetails['body']);
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        $queue->setIsFileSystemUsed($isFileSystemUsed);
        return $attachmentDetails;
    }

    /**
     * Retrieve path to attachments directory
     *
     * @param string|null $subdirPath
     *
     * @return string
     */
    public function getEmailAttachmentsPath($subdirPath = null)
    {
        $path = $this->_getBaseDirectory() . DS . self::EMAIL_ATTACHMENTS_DIRECTORY;
        if (!empty($subdirPath)) {
            $path .= DS . $subdirPath;
        }
        return $path;
    }

    /**
     * Save email attachment into filesystem
     *
     * @param string $filename
     * @param string $content
     * @param string $subDirPath
     * @thows Mage_Core_Exception
     *
     * @return boolean
     */
    public function saveEmailAttachmentInFilesystem($filename, $content, $subDirPath)
    {
        $attachmentsPath = $this->getEmailAttachmentsPath($subDirPath);
        $this->_storeCwd();
        $io = $this->_getFilesystemClient();
        $io->checkAndCreateFolder($attachmentsPath);
        $io->cd($attachmentsPath);
        $filePath = $attachmentsPath . DS . $filename;

        if (!$io->fileExists($filename) || $io->rm($filename)) {
            $io->streamOpen($filename);
            $io->streamLock(true);
            $result = $io->streamWrite($content);
            $io->streamUnlock();
            $io->streamClose();
            $this->_restoreCwd();
            if ($result !== false) {
                return true;
            }

            Mage::throwException(Mage::helper('core')->__('Unable to save file: %s', $filePath));
        }
        $this->_restoreCwd();
        return false;
    }

    /**
     * Retrieve system configuration option if either email attachments
     * can be stored in filesystem or in the database
     *
     * @return boolean
     */
    public function canKeepAttachmentsInFileSystem()
    {
        return Mage::getStoreConfigFlag(self::XPATH_KEEP_ATTACHMENTS_IN_FILESYSTEM);
    }

    /**
     * Retrieve correct sequence of names of parameters for Zend_Mail::createAttachment
     *
     * @return array()
     */
    public function getAttachmentParamSequence()
    {
        return $this->_attachmentParamsSequence;
    }

    /**
     * Create email attachment via Zend_Mail
     *
     * @return boolean
     */
    public function createAttachment($mailer, $attachmentDetails, $queue = null)
    {
        $attachmentParams = $this->_prepareAttachmentParams($attachmentDetails, $queue);
        if (empty($attachmentParams)) {
            return false;
        }
        call_user_func_array(array($mailer, 'createAttachment'), array_values($attachmentParams));
        return true;
    }

    /**
     * Remove attachments from filesystem by list of IDs
     * @param null|int|string|array() $ids
     *
     * @return Icommerce_EmailAttachments_Helper_Data
     */
    public function removeAttachments($ids = null)
    {
        if (empty($ids)) {
            return $this;
        }
        if (!is_array($ids)) {
            return $this->removeAttachmentById($ids);
        }
        foreach ($ids as $id) {
            $this->removeAttachmentById($id);
        }
        return $this;
    }

    /**
     * Remove attachment from filesystem by ID
     * @param null|int|string $id
     *
     * @return Icommerce_EmailAttachments_Helper_Data
     */
    public function removeAttachmentById($id = null)
    {
        if (empty($id)) {
            return $this;
        }
        $this->_removeDirectory($this->getEmailAttachmentsPath($id));
        return $this;
    }

    /**
     * Sanitize filename
     * @param string $filename
     *
     * @return string
     */
    public function sanitizeFilename($filename)
    {
        if (empty($filename)) {
            return $filename;
        }
        $filename = $this->stripTags($filename);
        $filename = preg_replace('/[\r\n\t\"\*\/\:\<\>\?\'\|\#\`\=\+\%\@\$\!\&\(\)\{\}\\\]+/', '', $filename);
        $filename = str_replace(' ', '-', $filename);
        return $filename;
    }

    /**
     * Prepare temporary directory to process attachments of current queue
     * @param Mage_Core_Model_Email_Queue $queue
     *
     * @return Icommerce_EmailAttachments_Helper_Data
     */
    public function prepareTemporaryDirectory($queue)
    {
        if ($this->canKeepAttachmentsInFileSystem()) {
            $prefix = implode('_', array(
                $queue->getEntityType(),
                $queue->getEntityId(),
                $queue->getEventType(),
                '',
            ));
            $uniquePath = Mage::helper('core')->uniqHash($prefix);
            $tempDirectoryPath = $this->getEmailAttachmentsPath($this->_getTempSubdirectoryRelativePath($uniquePath));
            try {
                $io = $this->_getFilesystemClient();
                $io->checkAndCreateFolder($tempDirectoryPath);
                $queue->setUniquePath($uniquePath);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this;
    }

    /**
     * Delete temporary directory after processing of current queue
     *
     * @param Mage_Core_Model_Email_Queue $queue
     *
     * @return Icommerce_EmailAttachments_Helper_Data
     */
    public function removeTemporaryDirectory($queue)
    {
        $uniquePath = $queue->getUniquePath();
        if ($uniquePath) {
            $tempDirectoryPath = $this->getEmailAttachmentsPath($this->_getTempSubdirectoryRelativePath($uniquePath));
            $this->_removeDirectory($tempDirectoryPath);
        }
        return $this;
    }

    /**
     * Set data related to email attachments into queue model
     *
     * @param Mage_Core_Model_Email_Queue $queue
     *
     * @return Icommerce_EmailAttachments_Helper_Data
     */
    public function setAttachmentsInQueue($queue = null)
    {
        if ($queue && ($queue instanceof Mage_Core_Model_Email_Queue) && $queue->getAttachments()) {
            $attachments = $attachmentsBackup = $queue->getAttachments();
            $hasAttachmentsInFileSystem = false;
            foreach ($attachments as $key => $value) {
                $attachmentDetails = $this->_prepareAttachmentDataForQueue($value, $queue);
                if ($queue->getIsFileSystemUsed()) {
                    $hasAttachmentsInFileSystem = true;
                    $attachments[$key] = $attachmentDetails;
                }
            }
            $queue->setIsFileSystemUsed($hasAttachmentsInFileSystem);
            if ($hasAttachmentsInFileSystem) {
                $queue->addData(array(
                    'attachments'           => $attachments,
                    'attachments_processed' => $attachments,
                    'attachments_backup'    => $attachmentsBackup,
                ));
            }
        }

        return $this;
    }

    /**
     * Copy email attachments from temporary directory
     * to directory related to current queue message
     *
     * @param Mage_Core_Model_Email_Queue $queue
     *
     * @return Icommerce_EmailAttachments_Helper_Data
     */
    public function copyEmailAttachments($queue)
    {
        $uniquePath = $queue->getUniquePath();
        if ($uniquePath && $queue->getIsFileSystemUsed()) {
            $tempDirectoryPath = $this->getEmailAttachmentsPath($this->_getTempSubdirectoryRelativePath($uniquePath));
            $messageUniquePathId = Mage::helper('core')->uniqHash($uniquePath);
            $messageUniquePath = $this->getEmailAttachmentsPath($messageUniquePathId);

            try {
                $this->_copyFilesOnly($tempDirectoryPath, $messageUniquePath);
                $queue->setUniquePathId($messageUniquePathId);
            } catch (Exception $e) {
                Mage::logException($e);
                $this->removeAttachmentById($messageUniquePath);
                $queue->getIsFileSystemUsed(false);
                $attachmentsBackup = $queue->getAttachmentsBackup();
                if ($attachmentsBackup) {
                    $queue->setAttachments($attachmentsBackup);
                }
            }
        }

        return $this;
    }

    /**
     * Rename of email attachment directory according to ID of the queue message
     *
     * @param Mage_Core_Model_Email_Queue $queue
     *
     * @return Icommerce_EmailAttachments_Helper_Data
     */
    public function renameEmailAttachmentsDirectory($queue)
    {
        $messageUniquePathId = $queue->getUniquePathId();

        if ($messageUniquePathId && $queue->getIsFileSystemUsed()) {
            try {
                $sourceDirectoryPath = $this->getEmailAttachmentsPath($messageUniquePathId);
                $destinationDirectoryPath = $this->getEmailAttachmentsPath($queue->getId());
                $io = $this->_getFilesystemClient();
                $io->open();
                $io->cd($sourceDirectoryPath);
                $io->mv($sourceDirectoryPath, $destinationDirectoryPath);
                $io->cd($destinationDirectoryPath);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        $queue->unsUniquePathId();
        return $this;
    }

    /**
     * Store current working directory
     */
    protected function _storeCwd()
    {
        $this->_cwd = getcwd();
    }

    /**
     * Restore previous current working directory, if we have one
     */
    protected function _restoreCwd()
    {
        if ($this->_cwd) {
            @chdir($this->_cwd);
        }
    }
}
