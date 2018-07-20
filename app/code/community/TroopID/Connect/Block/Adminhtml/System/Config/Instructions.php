<?php

class TroopID_Connect_Block_Adminhtml_System_Config_Instructions extends TroopID_Connect_Block_Adminhtml_System_Config_Custom {

    protected function _getContentHtml($element) {
        $config = $element->getData("field_config")->asArray();

        $html = '<span class="title">In order to test the integration, follow the steps below:</span>';
        $html .= '<ul class="steps">';
        $html .= '<li>' . $this->__("Create a developer account at") . ' <a href="' . $config["developer_url"] . '" target="_blank">' . $config["developer_url"] . '</a></li>';
        $html .= '<li>' . $this->__("Register an application at") . ' <a href="' . $config["apps_url"] . '" target="_blank">' . $config["apps_url"] . '</a></li>';
        $html .= '<li>' . $this->__("Fill in <strong>Redirect URI</strong> with") . ' ' . $this->getCallbackUrl() . '</li>';
        $html .= '<li>' . $this->__("Fill in <strong>Base URI</strong> with") . ' ' .  $this->getBaseUrl() . '</li>';
        $html .= '<li>' . $this->__("Copy and paste your <strong>Client ID</strong> and <strong>Client Secret</strong> values from your application settings on ID.me") . '</li>';
        $html .= '<li>' . $this->__("That's it! You are ready to go.") . '</li>';
        $html .= '<li>' . $this->__("You can customize the buttons by overriding or extending the cart template.") . '</li>';
        $html .= '<li>' . $this->__("Custom buttons need to have a <strong>idme-connect-trigger</strong> class and a <strong>data-scope</strong> attribute that determines the affinity group you want to verify.") . '</li>';
        $html .= '</ul>';
        $html .= '<div class="heading"><span class="heading-intro"><a href="' . $config["learn_more_url"] . '" target="_blank">' . $this->__("Learn more about ID.me") . '</a></span></div>';
        $html .= '<div class="heading"><span class="heading-intro"><a href="' . $config["docs_url"] . '" target="_blank">' . $this->__("Read developer documentation") . '</a></span></div>';
        $html .= '</div>';

        return $html;
    }

    protected function getStore() {
        $store = Mage::app()->getRequest()->getParam("store");
        $site  = Mage::app()->getRequest()->getParam("website");
        $front = null;

        if (isset($store)) {
            $front = Mage::app()->getStore($store);
        } else if(isset($site)) {
            $front = Mage::app()->getWebsite($site)->getDefaultStore();
        } else {
            $front = Mage::app()->getStore();
        }

        return $front;
    }

    public function getCallbackUrl() {
        return Mage::getUrl("troopid/authorize/callback", array(
            "_type"     => Mage_Core_Model_Store::URL_TYPE_WEB,
            "_secure"   => $this->getRequest()->isSecure(),
            "_nosid"    => true
        ));
    }

    public function getBaseUrl() {
        return $this->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, $this->getRequest()->isSecure());
    }

}