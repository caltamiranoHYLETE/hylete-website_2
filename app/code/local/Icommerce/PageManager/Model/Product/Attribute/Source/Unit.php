<?php
class Icommerce_PageManager_Model_Product_Attribute_Source_Unit extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $pages = Mage::getModel('pagemanager/page')->getPages();
            $this->_options = array();
            $this->_options[] = array(
                    'value' => '',
                    'label' => '',
            );
            foreach($pages as $page){
                if($page["status"] == 1){
                    $this->_options[] = array(
                        'value' => $page["id"],
                        'label' => $page["name"],
                    );
                }
            }
        }
        return $this->_options;
    }
}