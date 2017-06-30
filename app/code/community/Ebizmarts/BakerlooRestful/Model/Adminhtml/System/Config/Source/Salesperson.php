<?php

class Ebizmarts_BakerlooRestful_Model_Adminhtml_System_Config_Source_Salesperson {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {

        $result = array(
            array('value' => '', 'label' => '')
        );

        $persons = Mage::getResourceModel('admin/user_collection');

        foreach($persons as $_person) {
            array_push($result, array('value' => $_person->getUsername(), 'label' => $_person->getName()));
        }

        return $result;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {

        $result = array();

        foreach($this->toOptionArray() as $option) {
            $result[$option['value']] = $option['label'];
        }

        return $result;
    }

}