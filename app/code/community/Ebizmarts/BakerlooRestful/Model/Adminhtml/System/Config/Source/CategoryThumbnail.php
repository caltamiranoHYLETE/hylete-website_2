<?php

class Ebizmarts_BakerlooRestful_Model_Adminhtml_System_Config_Source_CategoryThumbnail {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => 'crop',
                'label'   => Mage::helper('bakerloo_restful')->__('Crop')
            ),
            array('value' => 'resize',
                'label'   => Mage::helper('bakerloo_restful')->__('Resize')
            ),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {
        return array();
    }

}