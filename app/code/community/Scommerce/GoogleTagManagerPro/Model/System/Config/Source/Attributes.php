<?php
/**
 * 
 * @package 	Scommerce_GoogleTagManagerPro
 * @category 	Scommerce
 * @author	 	Scommerce Mage <core@scommerce-mage.co.uk>
 * 
 */
Class Scommerce_GoogleTagManagerPro_Model_System_Config_Source_Attributes
{
	
	/**
	 * Method returns an array of attribute options and values
	 * @return array
	 */
	public function toOptionArray(){
		$options 	= array(array('value'=> '', 'label'=> Mage::helper('scommerce_googletagmanagerpro')->__('-- Please Select --')));
		$options = array_merge($options,$this->getProdAttributes());
		return $options;
	}
	
	/**
	 * Returning list of all product attributes
	 * @return array
	 */
	private function getProdAttributes(){
		$attributes = array();
		$collection = Mage::getResourceModel('eav/entity_attribute_collection')
			->setEntityTypeFilter(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId());
		if($collection->getSize()){
			foreach($collection as $attribute){
				$attributes[] = array('value'=>$attribute->getAttributeCode(),'label'=>$attribute->getName());
			}
		}
		$attributes[] = array('value'=>'entity_id','label'=>'entity_id');
		return $attributes;
	}
	
}
