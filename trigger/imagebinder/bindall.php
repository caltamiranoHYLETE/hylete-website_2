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

header('Content-Type: text/plain; charset=utf-8');
ini_set('memory_limit','512M');

// This is needed so that controller overloads and other possible require/includes would not fail (if they are not autoload-safe)
chdir ('../..');
require 'app/Mage.php';

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}

umask(0);

$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

try {
    $operationId = Mage::app()->getRequest()->getParam('operation_id', 0);

    /** @var $import Icommerce_ImageBinder_Model_Import */
    $import = Mage::getModel('imagebinder/import');
    $count = $import->bindAll($operationId);

    if ($count) {
        echo Icommerce_Utils::getTriggerResultXml(Icommerce_Utils::TRIGGER_STATUS_SUCCEEDED, Mage::helper('imagebinder')->__('%d image(s) bound', $count));
    } else {
        echo Icommerce_Utils::getTriggerResultXml(Icommerce_Utils::TRIGGER_STATUS_NOTHING_TO_DO, '');
    }
} catch (Exception $e) {
    echo Icommerce_Utils::getTriggerResultXml(Icommerce_Utils::TRIGGER_STATUS_FAILED, $e->getMessage());
}
