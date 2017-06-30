<?php
	class WindsorCircle_Export_Model_Products extends Mage_Core_Model_Abstract
	{
		protected $productData = array();

		protected $allowedCountries = array();
		
		protected $taxCollection = array();
		
		protected $categoryList = array();
		
		protected $breadcrumb = array();
		
		protected $completedBreadcrumbIds = array();
		
		protected $treeCollection = '';

		protected $attributeValues = array();

		protected function _construct(){
			$this->_init('windsorcircle_export/products');
		}
		
		protected function loadTreeCollection() {
			Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

			$this->treeCollection = Mage::getResourceSingleton('catalog/category_tree')
				->load();

			$collection = Mage::getSingleton('catalog/category')->getCollection()
				->addAttributeToSelect('name')
				->addAttributeToSelect('is_active');

			$this->treeCollection->addCollectionData($collection, true);
		}
		
		/**
		 * 
		 * Build Category Array by Node
		 * Recursive function for getting all children from a root node
		 * @param Varien_Data_Tree_Node $node
		 * $return array $result
		 */
		protected function nodeToArray(Varien_Data_Tree_Node $node, $storeId = false)
		{
			if($node->getIsActive()) {
				$this->categoryList[$storeId][$node->getLevel()][$node->getId()] = array('name' => $node->getName(), 'parent_id' => $node->getParentId());
			}
			
			foreach($node->getChildren() as $child) {
				$this->nodeToArray($child, $storeId);
			}
		}

		/**
		 * 
		 * Load Tree Array
		 * @return 
		 */
		protected function loadTree($parentId = false, $storeId = false) {

			if($parentId == false) {
				$parentId = 1;
			}

			if($this->treeCollection == '') {
				$this->loadTreeCollection();
			}

			$root = $this->treeCollection->getNodeById($parentId);
			
			if($root && $root->getId() == 1) {
				$root->setName(Mage::helper('catalog')->__('Root'));
			}

			if($root != null) {
				if($storeId == false) {
					$this->nodeToArray($root);
				} else {
					$this->nodeToArray($root, $storeId);
					krsort($this->categoryList[$storeId]);
				}
			}
		}

		/**
		 * 
		 * Get category list for product
		 * Recursive function for getting children
		 * @param array $productCategoryIds
		 * @param array $storeCategoryList
		 */
		protected function searchArray($productCategoryIds, $storeCategoryList, $levelFlag = false, $parentId = false) {
			$string = '';

			if($levelFlag == false) {
				reset($storeCategoryList);
				$levelFlag = key($storeCategoryList);
			}

			for($i = $levelFlag; $i > 1; $i--) {
				foreach($storeCategoryList[$i] as $id => $data) {
					if(in_array($id, $productCategoryIds)) {
						if($parentId != false) {
							if($id == $parentId) {
								if(($i - 1) != 0) {
									$this->completedBreadcrumbIds[$id] = $id;
									$string = $data['name'];
									$additionalString = $this->searchArray($productCategoryIds, $storeCategoryList, ($i - 1), $data['parent_id']);
									!empty($additionalString) ? $string = $additionalString . ' > ' . $string : '';
									return $string;
								}
							}
						} else {
							if(!in_array($id, $this->completedBreadcrumbIds)) {
								$string = $data['name'];
								$additionalString = $this->searchArray($productCategoryIds, $storeCategoryList, ($i - 1), $data['parent_id']);
								!empty($additionalString) ? $string = $additionalString . ' > ' . $string : '';
								$this->breadcrumb[] = $string;
							}
						}
					}
				}
			}
			return $string;
		}
		
		
		/**
		 * Get products partly from the catalog
		 * @return Mage_Core_Model_Product $product
		 */
		public function getProductsAdvanced($product, $handle){

			Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
			
			$helper = Mage::helper('windsorcircle_export');

			$productData_Adv = array();
				
			foreach($product->getStoreIds() as $key => $_store){
				
				Mage::app()->setCurrentStore($_store);

				if(empty($this->categoryList[$_store])) {
					$root_id = Mage::app()->getStore()->getRootCategoryId();
					$this->loadTree($root_id, $_store);
				}
				$this->breadcrumb = array();
				$this->completedBreadcrumbIds = array();

				$this->searchArray($product->getCategoryIds(), $this->categoryList[$_store]);
				
				// Image Array
				$images = $this->getImages($product);
				
				$parentId = $product->getParentSku();

				if(!empty($parentId)) {
					$categoryList = '';
					$url = '';
				} else {
					$url = $product->setStoreId($_store)->getProductUrl(false);
					$categoryList = '"' . implode('","', $this->breadcrumb) . '"';
				}

                // If no custom attribute for brand then we will use default brand attribute
                $attribute = Mage::getStoreConfig('windsorcircle_export_options/messages/brand_attribute');
                if (empty($attribute)) {
                    $brandName = $this->getAttributeValue('brand', $product->getBrand());
                } else {
                    $brandName = $this->getAttributeValue($attribute, $product->getData($attribute));
                }

				if(empty($productData_Adv[$product->getId()])){
					$productData_Adv[$product->getId(). ':' . $_store] = array($product->getId(),
																	$_store,
																	($product->getStatus() == 1 ? 'Y' : 'N'),
																	$product->getSku(),
                                                                    (!empty($parentId) ? $parentId : ''),
																	$categoryList,
																	$helper->formatString($product->getName()),
																	$helper->formatString($product->getDescription()),
																	$url,
																	array_shift($images),
																	$product->getPrice(),
																	$product->getSpecialPrice(),
																	$this->formatDates($product),
                                                                    (!empty($brandName) ? $brandName : ''),
																	$this->getAvailability($product),
																	$product->getQty(),
																	$this->getShippingWeight($product),
																);
																
				}
				
				fputcsv($handle, $productData_Adv[$product->getId(). ':' . $_store], "\t");
			}
			
			return;
		}

		/**
		 * Format dates for specials in ISO 8601 format
		 * @param Mage_Catalog_Model_Product $product
		 * @return string FromDate/ToDate or if no ToDate then just returns FromDate
		 */
		protected function formatDates(Mage_Catalog_Model_Product $product){
			$formatDate = array();

			if($product->getSpecialFromDate() != null){
				$formatDate[] = date_format(date_create($product->getSpecialFromDate()), 'c');
			}

			if($product->getSpecialToDate() != null){
				$formatDate[] = date_format(date_create($product->getSpecialToDate()), 'c');
			}
			
			if($formatDate == null){
				return '';
			} else {
				return implode('/', $formatDate);
			}
		}
		
		/**
		 * Gets availability of product
		 * @param Mage_Catalog_Model_Product $product
		 * @return string in_stock|out of stock|available for order
		 */
		protected function getAvailability(Mage_Catalog_Model_Product $product){
			if($product->getIsInStock() == 1){
				return 'in stock';
			} elseif($product->getBackorders() == 0){
				return 'out of stock';
			} else {
				return 'available for order';
			}
		}
		
		/**
		 * getImages URL
		 * @param Mage_Catalog_Model_Product $product
		 * @return array ImageUrls
		 */
		protected function getImages(Mage_Catalog_Model_Product $product){
			$allImages = array();
			$imageType = Mage::getStoreConfig('windsorcircle_export_options/messages/image_type');
			if(!empty($imageType) && $imageType == '2') {
				$productImage = $product->getSmallImage();
			} else {
				$productImage = $product->getImage();
			}
			if(!empty($productImage) && $productImage != 'no_selection') {
				$allImages[] = Mage::getModel('catalog/product_media_config')
								->getMediaUrl($productImage);
			}
			return $allImages;
		}
		
		/**
		 * Shipping Weight of product
		 * @param Mage_Catalog_Model_Product $product
		 * @return string Product Weight
		 */
		protected function getShippingWeight(Mage_Catalog_Model_Product $product){
			$weight = (float) $product->getWeight();
			$weight = !empty($weight) ? $weight . ' lb' : '';
			
			return $weight;
		}

		/**
		 * 
		 * Get Attribute Option Text
		 * @param string $attribute
		 * @param int $option
		 */
		protected function getAttributeValue($attribute, $option) {
			if(empty($attribute) || empty($option)) {
				return;
			}

			if(isset($this->attributeValues[$attribute][$option])) {
				return $this->attributeValues[$attribute][$option];
			}

			$attribute_model	= Mage::getModel('eav/entity_attribute');
        	$attribute_table	= Mage::getModel('eav/entity_attribute_source_table');

        	$attribute_code		= $attribute_model->getIdByCode('catalog_product', $attribute);
        	$loadedAttribute	= $attribute_model->load($attribute_code);

            $attribute_table->setAttribute($loadedAttribute);

        	$optionValue = $attribute_table->getOptionText($option);

			if(!empty($optionValue)) {
                if (is_array($optionValue)) {
                    $optionValue = implode(',', $optionValue);
                }

				$this->attributeValues[$attribute][$option] = $optionValue;
        		return $optionValue;
        	} else {
        		return $option;
        	}
		}
		
		/**
		 * Get taxes of product (e.g. Country:State:Value:TaxShipping => US:IL:0.0825:y)
		 * @param Mage_Catalog_Model_Product $product
		 * @return string TaxRates of current product
		 */
		protected function getTax(Mage_Catalog_Model_Product $product){
			$taxRate = array();
			
			if(empty($this->taxCollection[$product->getTaxClassId()])){
				$taxCollection = Mage::getModel('tax/calculation')->getCollection()
					->addFieldToFilter('product_tax_class_id', array('eq' => $product->getTaxClassId()));
				
				foreach($taxCollection as $taxes){
					$tax = Mage::getSingleton('tax/calculation')->getRatesByCustomerAndProductTaxClasses($taxes['customer_tax_class_id'], $product->getTaxClassId());
					foreach($tax as $taxRule){
						$this->taxCollection[$product->getTaxClassId()][] = $taxRule['country'] . ':' . $taxRule['state'] . ':' . $taxRule['value'] . ':y';
						// Use data as array key so there is not duplicate data
						$taxRate[$taxRule['country'].$taxRule['state'].$taxRule['postcode'].$taxRule['product_class']] = $taxRule['country'] . ':' . $taxRule['state'] . ':' . $taxRule['value'] . ':y';
					}
				}
			} else {
				foreach($this->taxCollection[$product->getTaxClassId()] as $taxRule){
					$taxRate[] = $taxRule;
				}
			}
			
			return implode(',', $taxRate);
		}
		
		/**
		 * Shipping country of product
		 * @param Mage_Catalog_Model_Product $product
		 * @return string All 2 Letter Country Codes that product can sell too
		 */
		protected function getShippingCountry(Mage_Catalog_Model_Product $product){
			$countryNames = array();
			
			$storeIds = $product->getStoreIds();

			foreach($storeIds as $id){
				if(array_key_exists($id, $this->allowedCountries)){
					foreach($this->allowedCountries[$id] as $country){
						$countryNames[$country['value']] = $country['value'];
					}
				} else {
					Mage::app()->setCurrentStore($id);
					
					$this->allowedCountries[$id] = Mage::getResourceModel('directory/country_collection')->loadByStore()->toOptionArray(false);
									
					foreach($this->allowedCountries[$id] as $country){
						$countryNames[$country['value']] = $country['value'];
					}
				}
			}

			// Set store back to admin
			Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
			
			ksort($countryNames);
			return implode(',', $countryNames);
		}
	}
