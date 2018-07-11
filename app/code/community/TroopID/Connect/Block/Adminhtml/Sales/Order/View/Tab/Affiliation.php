<?php

class TroopID_Connect_Block_Adminhtml_Sales_Order_View_Tab_Affiliation extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate("troopid/connect/affiliation.phtml");
    }

    public function getTabLabel() {
        return $this->__("ID.me Affiliation");
    }

    public function getTabTitle() {
        return $this->__("ID.me Affiliation");
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

    public function getOrder() {
        return Mage::registry("current_order");
    }

    public function getUUID() {
         return $this->getOrder()->getTroopidUid();
    }

    public function getScope() {
        return $this->getOrder()->getTroopidScope();
    }

    public function getAffiliation() {
        $helper = Mage::helper("troopid_connect");
        $group  = $this->getOrder()->getTroopidAffiliation();
        $scope  = $this->getOrder()->getTroopidScope();

        return $helper->formatAffiliation($scope, $group);
    }
}
