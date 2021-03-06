<?php
/**
 * MageWorx
 * MageWorx SeoMarkup Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoMarkup
 * @copyright  Copyright (c) 2017 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SeoMarkup_Helper_Html_Product extends MageWorx_SeoMarkup_Helper_Html
{
    public function getSocialProductInfo($product)
    {        
        $html = '';

        if ($this->_helperConfig->isProductOpenGraphEnabled()) {
            $siteName     = $this->_helperConfig->getWebSiteName();

            $url          = $this->_helper->getProductCanonicalUrl($product);
            $descr        = $this->_helper->getDescriptionValue($product);
            $title        = htmlspecialchars($product->getName());
            $color        = $this->_helper->getColorValue($product);
            $categoryName = htmlspecialchars($this->_helper->getCategoryValue($product));

            $brand        = $this->_helper->getBrandValue($product);
            if (!$brand) {
                $brand = $this->_helper->getManufacturerValue($product);
            }

            $weightString = $this->_helper->getWeightValue($product);
            $weightSep    = strpos($weightString, ' ');
            if ($weightSep !== false) {
                $weightValue  = substr($weightString, 0, $weightSep);
                $weightUnits  = substr($weightString, $weightSep + 1);
            }

            $availability = $this->_getConvertedAvailability($product);
            $condition    = $this->_getConvertedCondition($product);

            $prices = Mage::helper('mageworx_seomarkup/price')->getPricesByProductType($product->getTypeId());

            if (!empty($prices) && is_array($prices)) {
                $price = $prices[0];
            }

            $store = Mage::app()->getStore();

            $currency = strtoupper($store->getCurrentCurrencyCode());
            $html  = "\n";
            $html .= "<meta property=\"og:type\" content=\"product\"/>\n";
            $html .= "<meta property=\"og:title\" content=\"" . $title . "\"/>\n";
            $html .= "<meta property=\"og:description\" content=\"" . $descr . "\"/>\n";
            $html .= "<meta property=\"og:url\" content=\"" . $url . "\"/>\n";

            if (!empty($price)) {
                $html .= "<meta property=\"product:price:amount\" content=\"" . $store->convertPrice($price) . "\"/>\n";

                if ($currency) {
                    $html .= "<meta property=\"product:price:currency\" content=\"" . $currency . "\"/>\n";
                }
            }

            $img = Mage::helper('catalog/image')->init($product, 'image');
            $html .= "<meta property=\"og:image\" content=\"" . $img . "\"/>\n";
            $sizes = $this->getImageSizes($img);

            if (!empty($sizes)) {
                $html .= "<meta property=\"og:image:width\" content=\"" . $sizes['width'] . "\"/>\n";
                $html .= "<meta property=\"og:image:height\" content=\"" . $sizes['height'] . "\"/>\n";
            }

            if ($appId = $this->_helperConfig->getFacebookAppId()) {
                $html .= "<meta property=\"fb:app_id\" content=\"" . $appId . "\"/>\n";
            }

            if ($color) {
                $html .= "<meta property=\"product:color\" content=\"" . $color . "\"/>\n";
            }

            if ($brand) {
                $html .= "<meta property=\"product:brand\" content=\"" . $brand . "\"/>\n";
            }

            if ($siteName) {
                $html .= "<meta property=\"og:site_name\" content=\"" . $siteName . "\"/>\n";
            }

            if (!empty($weightValue) && !empty($weightUnits)) {
                $html .= "<meta property=\"product:weight:value\" content=\"" . $weightValue . "\"/>\n";
                $html .= "<meta property=\"product:weight:units\" content=\"" . $weightUnits . "\"/>\n";
            }

            if ($categoryName) {
                $html .= "<meta property=\"product:category\" content=\"" . $categoryName . "\"/>\n";
            }

            if ($availability) {
                $html .= "<meta property=\"product:availability\" content=\"" . $availability . "\"/>\n";
            }

            if ($condition) {
                $html .= "<meta property=\"product:condition\" content=\"" . $condition . "\"/>\n";
            }
        }

        if ($this->_helperConfig->isProductTwitterEnabled()) {
            $store = Mage::app()->getStore();

            $twitterUsername = $this->_helperConfig->getProductTwitterUsername();
            if ($twitterUsername) {
                $html = $html ? $html : "\n";
                $html .= "<meta property=\"twitter:site\" content=\"" . $twitterUsername . "\"/>\n";
                $html .= "<meta property=\"twitter:creator\" content=\"" . $twitterUsername . "\"/>\n";
                $html .= "<meta property=\"twitter:card\" content=\"product\"/>\n";
                $html .= "<meta property=\"twitter:title\" content=\"" . $title . "\"/>\n";
                $html .= "<meta property=\"twitter:description\" content=\"" . $descr . "\"/>\n";
                $html .= "<meta property=\"twitter:url\" content=\"" . $url . "\"/>\n";

                if (!empty($price)) {
                    $html .= "<meta property=\"twitter:label1\" content=\"Price\"/>\n";
                    $html .= "<meta property=\"twitter:data1\" content=\"" . $store->formatPrice($price, false) . "\"/>\n";
                }

                $html .= "<meta property=\"twitter:label2\" content=\"Availability\"/>\n";
                $html .= "<meta property=\"twitter:data2\" content=\"" . $availability . "\"/>\n";
            }
        }

        return $html;
    }

    protected function _getConvertedCondition($product)
    {
        $condition = $this->_helper->getConditionValue($product);
        if ($condition) {
            $ogEnum = array(
                'NewCondition'         => 'new',
                'UsedCondition'        => 'used',
                'RefurbishedCondition' => 'refurbished',
                'DamagedCondition'     => 'used'
            );
            if (!empty($ogEnum[$condition])) {
                return $ogEnum[$condition];
            }
        }

        return false;
    }

    protected function _getConvertedAvailability($product)
    {
        $availability = $this->_helper->getAvailability($product);
        switch ($availability) {
            case MageWorx_SeoMarkup_Helper_Data::IN_STOCK:
                $availability = 'instock';
                break;
            case MageWorx_SeoMarkup_Helper_Data::OUT_OF_STOCK:
                $availability = 'oos';
                break;
            default:
                $availability = false;
        }

        return $availability;
    }
}
