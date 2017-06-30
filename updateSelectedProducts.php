<?php
error_reporting(E_ALL);

require 'app/Mage.php';

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}

// Only for urls
// Don't remove this
$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

// Intentionally disabled for future rewriting. Currentluy cache keys are md5 hashed, so we can't use them to regen. data
// Better idea would be to cache the attributes that were used to generate the collection and take all cache records from
// cache and refresh them through getting the data from them directly (a'la: MD5_KEY => array([cache_gen_keys], [cached_data]))
/*
// need to select any store for DB functions to work
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$default_store_id = Icommerce_Db::getValue('SELECT MIN(store_id) FROM core_store WHERE is_active=1 AND website_id!=0');

echo "<pre>\n";
echo "Updating SelectedProducts\n";

$block = new Icommerce_SelectedProducts_Block_Collection;

foreach ($rows as $row) {
    echo 'key [' . $row['key'] . '] ... ';
    try {
        $coll = $block->getRealCollectionByCacheKey($row['key'], false);

        if ($coll) {
            $block->cacheCollection($row['key'], $coll);
            echo "Updated\n";
        } else {
            echo "Invalid params\n";
        }
    } catch (Exception $e) {
        Mage::printException($e);
    }
}
echo "done.\n";
*/
