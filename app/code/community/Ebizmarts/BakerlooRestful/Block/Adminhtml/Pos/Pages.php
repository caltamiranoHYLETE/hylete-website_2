<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Pages extends Mage_Adminhtml_Block_Template {

    /**
     * Return URL to generate new pages.
     *
     * @return string
     */
    public function getGenerateFilesUrl() {
        return $this->getUrl('adminhtml/pos_pages/new');
    }

    public function getGenerateTreeDataUrl() {
        return $this->getUrl('adminhtml/pos_pages/tree');
    }

    public function getGenerateCacheUrl() {
        return $this->getUrl('adminhtml/pos_pages/cache');
    }

}