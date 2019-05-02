<?php
/**
 * MageWorx
 * MageWorx SeoMarkup Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoMarkup
 * @copyright  Copyright (c) 2018 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SeoMarkup_Model_Observer_Page
{
    public function createRichsnippetPageMarkup($observer)
    {
        if (!Mage::helper('mageworx_seomarkup')->isCmsPage()) {
            return;
        }

        if (!Mage::helper('mageworx_seomarkup/config')->isPageGaEnabled()) {
            return false;
        }

        if (Mage::app()->getRequest()->isXmlHttpRequest()) {
            return false;
        }

        $block = $observer->getBlock();
        if ($block->getNameInLayout() != 'root') {
            return false;
        }

        $jsonPageHelper = Mage::helper('mageworx_seomarkup/json_page');
        $pageRichsnippetData = $jsonPageHelper->getJsonPageData();

        if (!empty($pageRichsnippetData)) {
            $transport    = $observer->getTransport();
            $normalOutput = $observer->getTransport()->getHtml();
            $pageJson = '<script type="application/ld+json">' . json_encode($pageRichsnippetData) . '</script>';
            $modifyOutput = str_replace('</head>', "\n" . $pageJson . '</head>', $normalOutput);
            $transport->setHtml($modifyOutput);
        }

        return $this;
    }
}
