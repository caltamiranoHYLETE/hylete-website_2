<?php
/**
 * Copyright (c) 2009-2013 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
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
 * @package     Vaimo_Module
 * @file        process.php
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Vitali Rassolov <vitali.rassolov@vaimo.com>
 */

header('Content-Type: text/plain; charset=utf-8');
ini_set('memory_limit','1024M');

chdir ('../..');
require 'app/Mage.php';

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}

$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

try {
    /** @var Vaimo_ProductAlertExtended_Model_Observer $import */
    $import = Mage::getModel('productalertextended/observer');
    $import->process();

    if ($import->getSuccessCount() == 0 && $import->getFailureCount() == 0) {
        echo Icommerce_Utils::getTriggerResultXml(
            Icommerce_Utils::TRIGGER_STATUS_NOTHING_TO_DO,
            ''
        );
    } elseif ($import->getSuccessCount() > 0 && $import->getFailureCount() > 0) {
        echo Icommerce_Utils::getTriggerResultXml(
            Icommerce_Utils::TRIGGER_STATUS_EXCEPTIONS,
            $import->getFailureMessages()
        );
    } elseif ($import->getSuccessCount() > 0) {
        echo Icommerce_Utils::getTriggerResultXml(
            Icommerce_Utils::TRIGGER_STATUS_SUCCEEDED,
            Mage::helper('productalertextended')->__('Alerts proceeded: (%d)', $import->getSuccessCount())
        );
    } else {
        echo Icommerce_Utils::getTriggerResultXml(
            Icommerce_Utils::TRIGGER_STATUS_FAILED,
            Mage::helper('productalertextended')->__('Alerts failed: (%d)', $import->getFailureCount())
        );
    }

} catch (Exception $e) {
    echo Icommerce_Utils::getTriggerResultXml(Icommerce_Utils::TRIGGER_STATUS_FAILED, $e->getMessage());
}