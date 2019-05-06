<?php
/**
 * MageWorx
 * MageWorx SeoMarkup Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoMarkup
 * @copyright  Copyright (c) 2018 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SeoMarkup_Helper_Json_Page extends MageWorx_SeoMarkup_Helper_Abstract
{
    /**
     * @return array
     */
    public function getJsonPageData()
    {
        $speakableData = array();
        if ($this->_helperConfig->isPageGaEnabled()) {
            $speakableData['@context']  = 'http://schema.org/';
            $speakableData['@type']     = 'WebPage';
            $speakable                  = array();
            $speakable['@type']         = 'SpeakableSpecification';
            $speakable['cssSelector']   = explode(',', $this->_helperConfig->getPageGaCssSelectors());
            $speakable['xpath']         = array('/html/head/title');
            $speakableData['speakable'] = $speakable;
        }

        return $speakableData;
    }
}
