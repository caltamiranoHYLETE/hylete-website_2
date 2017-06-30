<?php
class Icommerce_Slideshow_Block_View extends Icommerce_SlideshowManager_Block_Slideshow
{
    /**
    * Class constructor.
    */
    public function __construct()
    {

    }
      
    /** Output settings list to block */
    public function getSettings()
    {

        $html = '<ul id="settings">';
        $html .= '<li id="showcase-show-caption">' . Mage::getStoreConfig('slideshow/settings/show_caption') . '</li>';
        $html .= '<li id="showcase-interval">' . Mage::getStoreConfig('slideshow/settings/interval'). '</li>';
        $html .= '<li id="showcase-autostart">' . Mage::getStoreConfig('slideshow/settings/autostart'). '</li>';
        $html .= '<li id="showcase-transition">' . Mage::getStoreConfig('slideshow/settings/transition') . '</li>';
        $html .= '<li id="showcase-continuous">' . Mage::getStoreConfig('slideshow/settings/continuous') . '</li>';
        $html .= '</ul>';

        return $html;
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array_merge(
            parent::getCacheKeyInfo(),
            array(
                'slideshow_id' => $this->getSlideshowId(),
            )
        );
    }
}
