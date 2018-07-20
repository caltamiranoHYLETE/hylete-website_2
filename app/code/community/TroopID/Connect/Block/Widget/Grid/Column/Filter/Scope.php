<?php

class TroopID_Connect_Block_Widget_Grid_Column_Filter_Scope extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select  {

    protected $options = array();

    protected function _getOptions() {
        if (empty($this->options)) {

            $scopes = array(
                array(
                    "value" => "",
                    "label" => ""
                )
            );

            $groups = Mage::helper("troopid_connect")->getAffiliations();

            foreach ($groups as $group) {
                $scopes[] = array(
                    "value" => $group["scope"],
                    "label" => $group["name"]
                );
            }

            $this->options = $scopes;
        }

        return $this->options;
    }
}