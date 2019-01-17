<?php
/**
 * MageWorx
 * MageWorx SeoMarkup Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoMarkup
 * @copyright  Copyright (c) 2017 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SeoMarkup_Helper_Html extends MageWorx_SeoMarkup_Helper_Abstract
{
    /**
     * @param $imageUrl
     * @return array
     */
    public function getImageSizes($imageUrl) {
        $sizes = array();
            $dirImg = Mage::getBaseDir().str_replace("/",DS,strstr($imageUrl,'/media'));
            if (file_exists($dirImg)) {
                $imageObj = new Varien_Image($dirImg);
                $sizes['width'] =  $imageObj->getOriginalWidth();
                $sizes['height'] =  $imageObj->getOriginalHeight();
            }

        return $sizes;
    }

    /**
     * Retrieve path to Facebook Website Logo
     *
     * @return string
     */
    public function getFacebookLogoUrl()
    {
        $folderName = MageWorx_SeoMarkup_Model_System_Config_Backend_LogoFacebook::UPLOAD_DIR;
        $storeConfig = $this->_helperConfig->getFacebookLogoFile();
        $faviconFile = Mage::getBaseUrl('media') . $folderName . '/' . $storeConfig;
        $absolutePath = Mage::getBaseDir('media') . '/' . $folderName . '/' . $storeConfig;


        if(!is_null($storeConfig) &&  $this->_helper->isFile($absolutePath)) {
            return $faviconFile;
        }

        return false;
    }
}