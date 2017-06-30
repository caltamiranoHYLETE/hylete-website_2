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
 * @category    Icommerce
 * @package     Icommerce_SlideshowManager
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 * @author      Allan Paiste <allan.paiste@vaimo.com>
 * @comment     Correct way of getting files to media directory. Otherwise folder has wrong permissions/owner.
 */

$installer = $this;
$installer->startSetup();

/**
 * Logic adapted from Mage_Catalog_Model_Product_Attribute_Backend_Media::_moveImageFromTmp
 */
$baseDir = Mage::getBaseDir('media');
$setupFilePath = $baseDir . DS .  'slideshowmanager';
$directoryIterator = new RecursiveDirectoryIterator($setupFilePath);
$ioObject = new Varien_Io_File();
foreach (new RecursiveIteratorIterator($directoryIterator) as $sourcePath => $_fileInfo) {
    if (is_dir($sourcePath)) {
        continue;
    }

    $relativePath = substr($sourcePath, strlen($setupFilePath));
    $destFile = $baseDir . $relativePath;
    $destDirectory = dirname($destFile);
    try {
        $ioObject->open(array('path' => $destDirectory));
    } catch (Exception $e) {
        $ioObject->mkdir($destDirectory, 0777, true);
        $ioObject->open(array('path' => $destDirectory));
    }

    $ioObject->cp($sourcePath, $destFile);
}

$installer->endSetup();