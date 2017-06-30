<?php

class BSS_Configurablegridview_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getStockEnabled(){
		return Mage::getStoreConfigFlag('configurablegridview/settings/enable_stock_avail');
	}

	public function getIsEnabled(){
		return Mage::getStoreConfigFlag('configurablegridview/settings/is_enabled');
	}

	public function getShowOutStock() {
		return Mage::getStoreConfigFlag('configurablegridview/settings/show_out_stock');
	}

	public function getShowPrice() {
		return Mage::getStoreConfigFlag('configurablegridview/settings/show_price');
	}

	public function getNumberStock() {
		return Mage::getStoreConfigFlag('configurablegridview/settings/show_number_stock');
	}

	public function getListAttributes($_product) {
		$associative_products = $_product->getTypeInstance()->getUsedProducts();
		$assc_product_data = array();
		$labels = array();
		$options = array();
		$store = Mage::app()->getStore();
		foreach ($associative_products as $assc_products) {

			$productAttributes = $_product->getTypeInstance(true)->getConfigurableAttributes($_product);

			if($assc_products->getStatus() == 1){
				if($this->getShowOutStock()) {
					$stock = number_format(Mage::getModel('cataloginventory/stock_item')->loadByProduct($assc_products)->getQty());

					$assc_product_data[$assc_products->getId()]['info'] = array('price' => 0, 'qty' => $stock, 'prod_id'=>$assc_products->getId());

					foreach ($productAttributes as $attribute) {

						$_attributePrice = $attribute->getPrices();

						$labels[$attribute->getLabel()] = $attribute->getLabel();

						$value = $assc_products->getResource()->getAttribute($attribute->getProductAttribute()->getAttributeCode())->getFrontend()->getValue($assc_products);
						$options[$value] = $value;
						$att_array = array('code' => $attribute->getProductAttribute()->getAttributeCode(), 'label' => $attribute->getLabel(), 'value' => $value, 'attribute_id' => $attribute->getAttributeId());

						foreach($_attributePrice as $optionVal){
							if($optionVal['label'] == $value){
								$att_array['option_id'] = $optionVal['value_index'];
								$att_array['pricing_value'] = $optionVal['pricing_value'];
								$att_array['is_percent'] = $optionVal['is_percent'];

							}
						}
						$assc_product_data[$assc_products->getId()]['attributes'][] = $att_array;
					}
				}else {
					if($assc_products->isSaleable()) {
						$stock = number_format(Mage::getModel('cataloginventory/stock_item')->loadByProduct($assc_products)->getQty());

						$assc_product_data[$assc_products->getId()]['info'] = array('price' => 0, 'qty' => $stock, 'prod_id'=>$assc_products->getId());

						foreach ($productAttributes as $attribute) {

							$_attributePrice = $attribute->getPrices();

							$labels[$attribute->getLabel()] = $attribute->getLabel();

							$value = $assc_products->getResource()->getAttribute($attribute->getProductAttribute()->getAttributeCode())->getFrontend()->getValue($assc_products);
							$options[$value] = $value;
							$att_array = array('code' => $attribute->getProductAttribute()->getAttributeCode(), 'label' => $attribute->getLabel(), 'value' => $value, 'attribute_id' => $attribute->getAttributeId());

							foreach($_attributePrice as $optionVal){
								if($optionVal['label'] == $value){
									$att_array['option_id'] = $optionVal['value_index'];
									$att_array['pricing_value'] = $optionVal['pricing_value'];
									$att_array['is_percent'] = $optionVal['is_percent'];

								}
							}
							$assc_product_data[$assc_products->getId()]['attributes'][] = $att_array;
						}
					}
				}
			}
		}
		$assc_product_data = $assc_product_data;
		$configurable_products = array('num_attributes' => count($_product->getTypeInstance(true)->getConfigurableAttributes($_product)), 'products' => $assc_product_data, 'labels' => $labels, 'options' => $options);
		return $configurable_products;
	}
}