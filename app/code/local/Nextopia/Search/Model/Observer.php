<?php
/**
 * Created by PhpStorm.
 * User: shasan
 * Date: 07/04/16
 * Time: 2:52 PM
 */

class Nextopia_Search_Model_Observer extends Mage_Core_Model_Observer
{
    public function catchSearch($observer)
    {
        $helper = Mage::helper("nsearch");
        if($helper->isEnabled()) {
            $observer->getControllerAction()->getResponse()->setRedirect(
                $helper->getResultUrl($helper->getQueryText())
            )->sendResponse();
        }
    }
}