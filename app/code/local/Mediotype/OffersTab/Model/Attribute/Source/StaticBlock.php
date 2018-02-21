<?php

/**
 * Created by PhpStorm.
 * User: mattsherer
 * Date: 2/12/18
 * Time: 4:29 PM
 */
class Mediotype_OffersTab_Model_Attribute_Source_StaticBlock extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $_options;

    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = Mage::getResourceModel('cms/block_collection')
                ->load()
                ->toOptionArray();
            array_unshift($this->_options, array('value' => '', 'label' => Mage::helper('catalog')->__('-- Please Select --')));
        }

        return $this->_options;
    }
}