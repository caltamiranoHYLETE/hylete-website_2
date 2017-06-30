<?php
/** Trigger file created by Hannes Karlsson */

require_once 'app/Mage.php';

header('Content-Type: text/plain; charset=utf-8');

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}

Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);

// Only for urls
// Don't remove this
$_SERVER['SCRIPT_NAME']     = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

try {
    /** @var Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $prodCollection */
    $prodCollection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('status')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('blog_publish_date')
            ->addAttributeToSelect('blog_publish_datetime')
            ->addAttributeToSelect('blog_unpublish_datetime')
            ->addAttributeToFilter('type_id', array('eq' => 'blog'));
    //->addAttributeToFilter('blog_publish_date', array('neq' => ''))
    //->addAttributeToFilter('status', array('neq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED))
    ;

    if ($prodCollection->getSize() == 0) {
        echo Icommerce_Utils::getTriggerResultXml(Icommerce_Utils::TRIGGER_STATUS_NOTHING_TO_DO, 'Disabled posts were not found to be enabled.'), "\n";
        exit(0);
    }

    $now = Mage::getModel('core/date')->timestamp(time());

    $enabled = 0;
    echo strtoupper('Enabled posts') . "\n\n";
    foreach ($prodCollection as $product) {
        $blogPubishDatetime = $product->getBlogPublishDatetime();
        $blogPubishDate = empty($blogPubishDatetime) ? $product->getBlogPublishDate() : $blogPubishDatetime;

        if ($blogPubishDate != null) {
            $publishAt = strtotime($blogPubishDate);
            if ($now > $publishAt) {
                $productStatus = $product->getStatus();
                if ($productStatus == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                    $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                    $product->save();
                    echo Icommerce_Utils::getTriggerLine(Icommerce_Utils::TRIGGER_STATUS_NOTHING_TO_DO, 'Enabled ' . $product->getName()), "\n";
                    $enabled++;
                }
            }
        }
    }
    if ($enabled == 0) {
        echo Icommerce_Utils::getTriggerResultXml(Icommerce_Utils::TRIGGER_STATUS_SUCCEEDED, 'Nothing to enable currently'), "\n";
    } else {
        echo Icommerce_Utils::getTriggerResultXml(Icommerce_Utils::TRIGGER_STATUS_SUCCEEDED, sprintf('Enabled %d post(s).', $enabled)), "\n";
    }

    // disabled posts
    $disabled = 0;
    echo  "\n\n" . strtoupper('Disabled posts') . "\n\n";
    foreach ($prodCollection as $product) {
        $blogUnpubishDatetime = $product->getBlogUnpublishDatetime();

        if ($blogUnpubishDatetime != null) {
            $unpublishAt = strtotime($blogUnpubishDatetime);
            if ($now > $unpublishAt) {
                $productStatus = $product->getStatus();
                if ($productStatus == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                    $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                    $product->save();
                    echo Icommerce_Utils::getTriggerLine(Icommerce_Utils::TRIGGER_STATUS_NOTHING_TO_DO, 'Disabled ' . $product->getName()), "\n";
                    $disabled++;
                }
            }
        }
    }
    if ($disabled == 0) {
        echo Icommerce_Utils::getTriggerResultXml(Icommerce_Utils::TRIGGER_STATUS_SUCCEEDED, 'Nothing to disable currently'), "\n";
    } else {
        echo Icommerce_Utils::getTriggerResultXml(Icommerce_Utils::TRIGGER_STATUS_SUCCEEDED, sprintf('Disabled %d post(s).', $disabled)), "\n";
    }
} catch (Exception $e) {
    echo Icommerce_Utils::getTriggerResultXml(Icommerce_Utils::TRIGGER_STATUS_FAILED, 'Exception: ' . $e->getMessage()), "\n";
    echo $e->getMessage(), "\n\n";
    echo $e->getTraceAsString();
}
