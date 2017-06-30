<?php
class Windsorcircle_Export_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction(){

        $this->setMemoryLimit();

        // Set the timestamp for the files in the registry
        Mage::register('windsor_file_timestamp', date('YmdHis'));

        // Removing log file
        $filename = Mage::getBaseDir('log') . DS . 'windsorcircle.log';
        if(file_exists($filename)) {
            unlink($filename);
        }

        Mage::log('Checking Parameters', null, 'windsorcircle.log');

        // Get params from URL
        $params = new Varien_Object($this->getRequest()->getParams());

        // Required params must be set!
        if(empty($params['authToken']) || empty($params['startDate']) || empty($params['endDate']) || empty($params['authDate']))
        {
            throw new Exception('Must provide all parameters (authToken, startDate, endDate, AuthDate).');
        }

        $files = array();

        // Check the authDate to make sure it is within one minute of request
        Mage::getModel('windsorcircle_export/date')->checkDate($params['authDate']);

        // Check to make sure that the authToken sent in the URL is valid
        Mage::getModel('windsorcircle_export/openssl')->valid($params['authToken'], $params['authDate']);

        Mage::log('All Parameters Checked OK', null, 'windsorcircle.log');

        // If version parameter is passed then return current module version
        if (isset($params['Version'])) {
            echo (string) Mage::helper('windsorcircle_export')->getExtensionVersion();
            return;
        }

        switch ($params['dataType']) {
            case 'ASC':
                $formatModel = Mage::getModel('windsorcircle_export/format');
                $orderModel = Mage::getModel('windsorcircle_export/order');

                // Get Order Data and Order Details Data
                $orders = $orderModel->getOrders($params['startDate'], $params['endDate']);

                // Format Order Data and Order Details Data
                $files[] = $formatModel->formatOrderData($orders[0]);
                $files[] = $formatModel->formatOrderDetailsData($orders[1]);

                // Get Abandoned Shopping Cart Order Data and Order Details Data
                if (!empty($params['ascStartDate']) && !empty($params['ascEndDate'])) {
                    $ascOrders = $orderModel->getAscOrders($params['ascStartDate'], $params['ascEndDate']);
                } else {
                    $ascOrders = $orderModel->getAscOrders($params['startDate'], $params['endDate']);
                }

                // Format Abandoned Shopping Cart Order Data and Order Details Data
                $files[] = $formatModel->formatOrderData($ascOrders[0], '_ASC_');
                $files[] = $formatModel->formatOrderDetailsData($ascOrders[1], '_ASC_');

                // Get product data if updated
                if ($productFile = $formatModel->getProductDataIfUpdated()) {
                    $files[] = $productFile;
                }
                break;
            case 'Orders':
                Mage::log('Getting Orders Data', null, 'windsorcircle.log');

                // Get Order Data and Order Details Data
                $orders = Mage::getModel('windsorcircle_export/order')->getOrders($params['startDate'], $params['endDate']);

                // Format Order Data and Order Details Data
                $files[] = Mage::getModel('windsorcircle_export/format')->formatOrderData($orders[0]);
                $files[] = Mage::getModel('windsorcircle_export/format')->formatOrderDetailsData($orders[1]);

                Mage::log('All Orders recieved', null, 'windsorcircle.log');
                break;
            case 'OrdersPlus':
                $formatModel = Mage::getModel('windsorcircle_export/format');

                // Get product data if updated
                if ($productFile = $formatModel->getProductDataIfUpdated()) {
                    $files[] = $productFile;
                }

                // Get Order Data and Order Details Data
                $orders = Mage::getModel('windsorcircle_export/order')->getOrders($params['startDate'], $params['endDate']);

                // Format Order Data and Order Details Data
                $files[] = $formatModel->formatOrderData($orders[0]);
                $files[] = $formatModel->formatOrderDetailsData($orders[1]);
                break;
            case 'ProductsRebuild':
                $cmd = PHP_BINDIR . '/php -f ' . Mage::getModuleDir('controllers', 'Windsorcircle_Export') . DS . 'Background.php';
                $cmd .= !empty($params['dataType']) ? ' dataType=' . escapeshellarg($params['dataType']) : '';
                $cmd .= !empty($params['startDate']) ? ' startDate=' . escapeshellarg($params['startDate']) : '';
                $cmd .= !empty($params['endDate']) ? ' endDate=' . escapeshellarg($params['endDate']) : '';
                $cmd .= !empty($params['ascStartDate'])? ' ascStartDate=' . escapeshellarg($params['ascStartDate']) : '';
                $cmd .= !empty($params['ascEndDate'])? ' ascEndDate=' . escapeshellarg($params['ascEndDate']) : '';

                if (substr(php_uname(), 0, 7) == "Windows") {
                    if (!pclose(popen("start /B ". $cmd, "r"))) {
                        echo 'Windows popen/pclose is not available.';
                        $this->removeProductsFile();
                        echo ' Products file removed.';
                        return;
                    }
                } else {
                    if (exec('echo EXEC') == 'EXEC') {
                        exec($cmd . " > /dev/null &");
                    } else {
                        echo 'Exec is not available.';
                        $this->removeProductsFile();
                        echo ' Products file removed.';
                        return;
                    }
                }
                echo 'Request sent Successfully!';
                return;
                break;
            case 'Products':
                Mage::log('Getting Products Data', null, 'windsorcircle.log');

                // Get Products data
                $files[] = Mage::getModel('windsorcircle_export/format')->advancedFormatProductData();

                Mage::log('All Products Gathered', null, 'windsorcircle.log');
                break;
            default:
                Mage::log('Getting Orders Data', null, 'windsorcircle.log');

                // Get Order Data and Order Details Data
                $orders = Mage::getModel('windsorcircle_export/order')->getOrders($params['startDate'], $params['endDate']);

                // Format Order Data and Order Details Data
                $files[] = Mage::getModel('windsorcircle_export/format')->formatOrderData($orders[0]);
                $files[] = Mage::getModel('windsorcircle_export/format')->formatOrderDetailsData($orders[1]);

                Mage::log('All Orders recieved', null, 'windsorcircle.log');

                Mage::log('Getting Products Data', null, 'windsorcircle.log');

                // Get Products data
                $files[] = Mage::getModel('windsorcircle_export/format')->advancedFormatProductData();

                Mage::log('All Products Gathered', null, 'windsorcircle.log');

                Mage::log('Sending Files to FTP Server', null, 'windsorcircle.log');
                break;
        }

        if (!empty($files)) {
            // Attempt to send files via FTP (FTP or SFTP)
            Mage::getModel('windsorcircle_export/ftp')->sendFiles($files);

            Mage::log('Files Sent', null, 'windsorcircle.log');

            // Remove all files from tmp directory after script is complete
            $mask = Mage::getBaseDir('tmp') . DS . Mage::getStoreConfig('windsorcircle_export_options/messages/client_name') . '_*';
            array_map('unlink', glob($mask));

            echo 'Files successfully sent';
        } else {
            echo 'No Files to send';
        }
    }

    /**
     * Set memory limit - defaults to 512M
     */
    private function setMemoryLimit()
    {
        $memoryLimitValue = Mage::getStoreConfig('windsorcircle_export_options/messages/memory_limit');
        if ($memoryLimitValue && is_numeric($memoryLimitValue)) {
            ini_set('memory_limit', "{$memoryLimitValue}M");
        } else {
            ini_set('memory_limit','512M');
        }
    }

    private function removeProductsFile() {
        $lastExportFolder = Mage::getBaseDir('media') . DS . 'windsorcircle_export';
        array_map('unlink', array($lastExportFolder . DS . 'lastexport.txt', $lastExportFolder . DS . 'updated.txt'));
    }
}
