<?php

class Hylete_Adminhtml_Block_Catalog_Parent extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		//We need to get the parent sku
		$childId =  $row->getData("entity_id");

		$parent_ids = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($childId);

		$val = '';
		if($parent_ids) {
			if(count($parent_ids) == 0) {
				return '';
			} else{
				foreach ($parent_ids as $p) {
					$url = Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit/index/id',array('id'=>$p));
					$prod = Mage::getModel('catalog/product')->load($p);
					$sku = $prod->getSku();
					$status = $prod->getStatus();

					$statusCode = "";
					if($status == 1) {
						$statusCode = "<div style=\"width: 10px;height: 10px;background: green;border-radius: 50%;display: inline-block;margin-left: 4px;\"></div>";
					} else if ($status == 2) {
						$statusCode = "<div style=\"width: 10px;height: 10px;background: red;border-radius: 50%;display: inline-block;margin-left: 4px;\"></div>";
					}

					$val = ' <a href='.$url.'>'.$sku.'</a>'.$statusCode;
				}
			}
		}

		return $val;

	}
}