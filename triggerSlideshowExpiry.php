<?php
/** Trigger file created by Hannes Karlsson */

require_once 'app/Mage.php';

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}

// Only for urls
// Don't remove this
$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$slideshowCollection = Mage::getModel('slideshowmanager/slideshow')->getSlideshows();

$slideshowDisabled = 0;
$slideshowEnabled  = 1;
$dbWrite = Icommerce_Db::getDbWrite();

$enabledSlideshows = array();
$disabledSlideshows = array();

foreach($slideshowCollection as $slideshow) {

    $validFrom = $slideshow['valid_from'];
    $validTo = $slideshow['valid_to'];

    // Might have to check for empty but not null values
    if($validTo != null && $validFrom != null) {

        $now = strtotime("now");
        $validFrom = strtotime($validFrom);
        $validTo = strtotime($validTo);
        $slideshowStatus = $slideshow['status'];

        if($now > $validFrom && $now < $validTo) {
            if($slideshowStatus == $slideshowDisabled) {
                $slideshow['status'] = $slideshowEnabled;
                $where = $dbWrite->quoteInto('id=?', $slideshow['id']);
                $result = $dbWrite->update( 'icommerce_slideshow', $slideshow, $where);
                $enabledSlideshows[] = $slideshow['id'];
            }
        } else if($slideshowStatus == $slideshowEnabled) {
            $slideshow['status'] = $slideshowDisabled;
            $where = $dbWrite->quoteInto('id=?', $slideshow['id']);
            $result = $dbWrite->update( 'icommerce_slideshow', $slideshow, $where);
            $disabledSlideshows[] = $slideshow['id'];
        }

    }

}

echo '<html><head><title>Enable/Disabled valid/invalid slideshows</title></head><body>';

echo '<h2>Id of slideshows that has been enabled</h2>';
     foreach($enabledSlideshows as $id) {
         echo $id . '<br />';
     }

echo '<h2>Id of slideshows that has been disabled</h2>';
    foreach($disabledSlideshows as $id) {
        echo $id . '<br />';
    }

echo '</body></html>';

die('<h3>Finished slideshow status update</h3>');