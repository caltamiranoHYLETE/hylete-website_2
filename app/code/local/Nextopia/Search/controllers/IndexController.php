<?php

class Nextopia_Search_IndexController extends Mage_Core_Controller_Front_Action {
	
	public function indexAction() {
        $helper = Mage::helper("nsearch");
        if(!$helper->isEnabled() && !$helper->isDemo()) {
            $this->getResponse()->setRedirect(
                $helper->getResultUrl($helper->getQueryText())
            )->sendResponse();
        }

		$this->loadLayout();

        $nxt_template = Mage::getStoreConfig('nextopia_ajax_options/settings/selected_template');
        if (!is_null($nxt_template)) {
            $this->getLayout()->getBlock("root")->setTemplate($nxt_template);
        }

        $this->getLayout()->getBlock("nextopia_search_js")->setShowInDemo(!$helper->isEnabled() && $helper->isDemo());


        $keywords = $helper->getQueryText();

        if (strlen(trim($keywords))) {
            $this->getLayout()->getBlock('nextopia_body')->setTitle( $helper->getLabelSearchResultPage() . ' "' . htmlentities($keywords) . '"');
        }
        $this->renderLayout();
	}


	
}
