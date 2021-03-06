<?php
/**
 * MageWorx
 * MageWorx SeoMarkup Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoMarkup
 * @copyright  Copyright (c) 2017 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SeoMarkup_Helper_Html_Page extends MageWorx_SeoMarkup_Helper_Html
{
    public function getSocialPageInfo($head)
    {
        $html  = '';

        if ($this->_helper->isHomePage() && $this->_helperConfig->isWebsiteOpenGraphEnabled()) {
            $html .= $this->_getOpenGraphPageInfo($head, true);
        } elseif ($this->_helperConfig->isPageOpenGraphEnabled()) {
            $html .= $this->_getOpenGraphPageInfo($head);
        }

        if ($this->_helper->isHomePage() &&
            $this->_helperConfig->isWebsiteTwitterEnabled() &&
            $this->_helperConfig->getWebsiteTwitterUsername()
        ) {
            $html .= $this->_getTwitterPageInfo($head, true);
        } elseif ($this->_helperConfig->isPageTwitterEnabled() && $this->_helperConfig->getPageTwitterUsername()) {
            $html .= $this->_getTwitterPageInfo($head);
        }

        return $html;
    }

    protected function _getOpenGraphPageInfo($head, $isWebsite = false)
    {
        $imageUrl = $this->getFacebookLogoUrl();

        if ($isWebsite) {
            $type     = 'website';
        } else {
            $type     = 'article';
        }

        $title = $head->getMetaTitle() ? htmlspecialchars($head->getMetaTitle()) : htmlspecialchars($head->getTitle());
        $description = htmlspecialchars($head->getDescription());
        $siteName = $this->_helperConfig->getWebSiteName();

        list($urlRaw) = explode('?', Mage::helper('core/url')->getCurrentUrl());
        $url = rtrim($urlRaw, '/');

        $html = "\n<meta property=\"og:type\" content=\"" . $type . "\"/>\n";
        $html .= "<meta property=\"og:title\" content=\"" . $title . "\"/>\n";
        $html .= "<meta property=\"og:description\" content=\"" . $description . "\"/>\n";
        $html .= "<meta property=\"og:url\" content=\"" . $url . "\"/>\n";
        if ($siteName) {
            $html .= "<meta property=\"og:site_name\" content=\"" . $siteName . "\"/>\n";
        }

        if($imageUrl) {
            $html .= "<meta property=\"og:image\" content=\"" . $imageUrl . "\"/>\n";
            $sizes = $this->getImageSizes($imageUrl);

            if (!empty($sizes)) {
                $html .= "<meta property=\"og:image:width\" content=\"" . $sizes['width'] . "\"/>\n";
                $html .= "<meta property=\"og:image:height\" content=\"" . $sizes['height'] . "\"/>\n";
            }
        }

        if ($appId = $this->_helperConfig->getFacebookAppId()) {
            $html .= "<meta property=\"fb:app_id\" content=\"" . $appId . "\"/>\n";
        }

        return $html;
    }

    protected function _getTwitterPageInfo($head, $isWebsite = false)
    {
        if ($isWebsite) {
            $type            = 'summary_large_image';
            $twitterUsername = $this->_helperConfig->getWebsiteTwitterUsername();
            $imageUrl        = $this->_getTwitterLogoUrl();
        } else {
            $type            = 'summary';
            $twitterUsername = $this->_helperConfig->getPageTwitterUsername();
            $imageUrl        = '';
        }

        $title = $head->getMetaTitle() ? htmlspecialchars($head->getMetaTitle()) : htmlspecialchars($head->getTitle());
        $description = htmlspecialchars($head->getDescription());
        
        $html = "<meta property=\"twitter:card\" content=\"" . $type . "\"/>\n";
        $html .= "<meta property=\"twitter:site\" content=\"" . $twitterUsername . "\"/>\n";
        $html .= "<meta property=\"twitter:title\" content=\"" . $title . "\"/>\n";
        $html .= "<meta property=\"twitter:description\" content=\"" . $description . "\"/>\n";

        if($imageUrl) {
            $html .= "<meta property=\"twitter:image\" content=\"" . $imageUrl . "\"/>\n";
        }

        return $html;
    }

    /**
     * Retrieve path to Facebook Website Logo
     *
     * @return string
     */
    protected function _getTwitterLogoUrl()
    {
        $folderName = MageWorx_SeoMarkup_Model_System_Config_Backend_LogoTwitter::UPLOAD_DIR;
        $storeConfig = $this->_helperConfig->getTwitterLogoFile();
        $faviconFile = Mage::getBaseUrl('media') . $folderName . '/' . $storeConfig;
        $absolutePath = Mage::getBaseDir('media') . '/' . $folderName . '/' . $storeConfig;

        if(!is_null($storeConfig) && $this->_helper->isFile($absolutePath)) {
            return $faviconFile;
        }

        return false;
    }
}
