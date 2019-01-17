<?php
/**
 * MageWorx
 * MageWorx SeoMarkup Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoMarkup
 * @copyright  Copyright (c) 2017 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SeoMarkup_Helper_Html_Category extends MageWorx_SeoMarkup_Helper_Html
{
    public function getSocialCategoryInfo($head)
    {
        $html  = '';

        if ($this->_helperConfig->isCategoryOpenGraphEnabled()) {
            $html .= $this->_getOpenGraphCategoryInfo($head);
        }

        return $html;
    }

    protected function _getOpenGraphCategoryInfo($head)
    {
        $type  = 'product.group';
        $title = $head->getMetaTitle() ? htmlspecialchars($head->getMetaTitle()) : htmlspecialchars($head->getTitle());
        $description = htmlspecialchars($head->getDescription());
        $siteName    = $this->_helperConfig->getWebSiteName();

        list($urlRaw) = explode('?', Mage::helper('core/url')->getCurrentUrl());
        $url = rtrim($urlRaw, '/');

        $html  = "\n<meta property=\"og:type\" content=\"" . $type . "\"/>\n";
        $html .= "<meta property=\"og:title\" content=\"" . $title . "\"/>\n";
        $html .= "<meta property=\"og:description\" content=\"" . $description . "\"/>\n";
        $html .= "<meta property=\"og:url\" content=\"" . $url . "\"/>\n";
        if ($siteName) {
            $html .= "<meta property=\"og:site_name\" content=\"" . $siteName . "\"/>\n";
        }

        $categoryImage = Mage::getModel('catalog/layer')->getCurrentCategory()->getImageUrl();

        if(!$categoryImage) {
            $categoryImage = $this->getFacebookLogoUrl();
        }

        if($categoryImage) {
            $html .= "<meta property=\"og:image\" content=\"" . $categoryImage . "\"/>\n";
            $sizes = $this->getImageSizes($categoryImage);

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
}