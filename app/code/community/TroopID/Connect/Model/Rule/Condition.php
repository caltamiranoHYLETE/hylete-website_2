<?php
class TroopID_Connect_Model_Rule_Condition extends Mage_Rule_Model_Condition_Abstract {

    public function loadAttributeOptions() {

        $this->setAttributeOption(array(
            "troopid_affiliation" => Mage::helper("troopid_connect")->__("ID.me Verified Affiliation")
        ));

        return $this;
    }

    public function getInputType() {
        return "select";
    }

    public function getValueElementType() {
        return "select";
    }

    public function getValueSelectOptions() {
        if (!$this->hasData("value_select_options")) {

            $options = array();
            $affiliations = Mage::helper("troopid_connect")->getAffiliations();

            foreach ($affiliations as $affiliation) {
                $name   = $affiliation["name"];
                $groups = $affiliation["groups"];

                if (sizeof($groups) > 0)
                    $options[$name] = $name . " (including all subgroups)";
                else
                    $options[$name] = $name;

                foreach ($groups as $group) {
                    $options[$name . " - " . $group] = $name . " - " . $group;
                }
            }

            $this->setData("value_select_options", $options);
        }

        return $this->getData("value_select_options");
    }

    public function getAttributeElement() {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    public function validate(Varien_Object $object) {

        $helper = Mage::helper("troopid_connect");

        if (!$helper->isOperational())
            return false;

        $quote  = $object->getQuote();
        $scope  = $quote->getTroopidScope();
        $group  = $quote->getTroopidAffiliation();
        $name   = $helper->getAffiliationByScope($scope);
        $value  = $this->getValue();

        if ($value === $name)
            return true;

        if ($value === ($name . " - " . $group))
            return true;

        return false;
    }

}