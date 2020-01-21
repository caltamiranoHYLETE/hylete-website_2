<?php

class Mediotype_OffersTab_Model_OfferSaveAfterObserver
{

    public function offerSaveAfter($observer){

        $baseDir = Mage::getBaseDir('media');
        $offerDirectory = $baseDir . "/app/offers/";
        $offerModel = $observer->getMediotypeOfferstabOffer();
//        var_dump(get_class($observer->getMediotypeOfferstabOffer()));
        $offerCollection = $offerModel->getCollection();

        ini_set('xdebug.var_display_max_depth', '100');
        ini_set('xdebug.var_display_max_children', '2560');
        ini_set('xdebug.var_display_max_data', '10240');
        $offerCollection->addFieldToFilter('feed_status',1);

        $collectionResults = json_encode($offerCollection->getData());

        if (!is_dir($offerDirectory)) {
            // dir doesn't exist, make it
            $newDirectory =  mkdir($offerDirectory,0777,true);
           var_dump($newDirectory);
        }

        file_put_contents($offerDirectory."offers.json",$collectionResults);

    }
}
