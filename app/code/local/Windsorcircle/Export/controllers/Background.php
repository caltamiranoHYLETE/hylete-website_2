<?php

// Load Magento core
$mageFilename = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/Mage.php';
if (!file_exists($mageFilename)) {
	echo 'Magento file does not exist!';
	exit;
}

require_once $mageFilename;

umask(0);

Mage::app();

// Set memory limit from configuration otherwise default to 512M
$memoryLimitValue = Mage::getStoreConfig('windsorcircle_export_options/messages/memory_limit');
if ($memoryLimitValue && is_numeric($memoryLimitValue)) {
    ini_set('memory_limit', "{$memoryLimitValue}M");
} else {
    ini_set('memory_limit','512M');
}

// Set the timestamp for the files in the registry
Mage::register('windsor_file_timestamp', date('YmdHis'));

$parameters = array();
foreach ($argv as $parameter) {
    if (strstr($parameter, '=')) {
        list($key, $value) = explode('=', $parameter);
        $parameters[$key] = $value;
    } else {
        $parameters[] = $parameter;
    }
}

$files = array();

if(!empty($parameters['dataType'])) {
    switch ($parameters['dataType']) {
        case 'ProductsRebuild':
            Mage::log('Getting Products Data', null, 'windsorcircle.log');

            $lastExportFolder = Mage::getBaseDir('media') . DS . 'windsorcircle_export';
            unlink($lastExportFolder . DS . 'lastexport.txt');
            unlink($lastExportFolder . DS . 'updated.txt');
            $files[] = Mage::getModel('windsorcircle_export/format')->advancedFormatProductData();

            Mage::log('All Products Gathered', null, 'windsorcircle.log');
            break;
    }
}

 if(!empty($files)) {
    Mage::log('Sending Files to FTP Server', null, 'windsorcircle.log');

    // Attempt to send files via FTP (FTP or SFTP)
	Mage::getModel('windsorcircle_export/ftp')->sendFiles($files);

	Mage::log('Files Sent', null, 'windsorcircle.log');

	// Remove all files from tmp directory after script is complete
	$mask = Mage::getBaseDir('tmp') . DS . Mage::getStoreConfig('windsorcircle_export_options/messages/client_name') . '_*';
	array_map('unlink', glob($mask));
}
