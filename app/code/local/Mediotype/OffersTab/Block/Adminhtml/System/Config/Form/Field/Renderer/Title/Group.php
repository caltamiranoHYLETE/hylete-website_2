<?php

/**
 * Tab title system configuration customer group column renderer.
 * @category  Class
 * @package   Mediotype_OffersTab
 * @author    Rick Buczynski <rick@mediotype.com>
 * @copyright 2018 Mediotype
 */

/**
 * Class declaration
 * @category Class_Type_Block
 * @package  Mediotype_OffersTab
 * @author   Rick Buczynski <rick@mediotype.com>
 */

class Mediotype_OffersTab_Block_Adminhtml_System_Config_Form_Field_Renderer_Title_Group extends Mage_Adminhtml_Block_Html_Select
{
    protected $_syncFieldsMap = array(
        'input_name' => 'name',
    );

    /**
     * Local constructor.
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_loadOptions();
    }

    /**
     * Load customer groups options data.
     * @return void
     */
    protected function _loadOptions()
    {
        if (empty($this->_options)) {
            $this->_options = array(array('value' => '', 'label' => $this->__('Select One')));
            $collection     = Mage::getResourceModel('customer/group_collection');

            foreach ($collection as $group) {
                $this->_options[] = array(
                    'value' => $group->getId(),
                    'label' => $group->getCustomerGroupCode(),
                );
            }
        }
    }
}
