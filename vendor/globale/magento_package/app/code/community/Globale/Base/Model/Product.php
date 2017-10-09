<?php
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\API\Common\Attribute;


/**
 * Class Globale_Base_Model_Product
 */
class Globale_Base_Model_Product extends Mage_Core_Model_Abstract {

    const XML_PATH_EXTRA_ATTR_LIST     = 'globale_settings/products_settings/product_extra_attr';
    const XML_PATH_BRAND_ATTR          = 'globale_settings/products_settings/brand_attr';

    /**
     * Gets array of magento products and returns commons of sdk products for SaveProductsList call
     * @param Mage_Catalog_Model_Product[] $Products
     * @return Common\Request\Product[]
     */
    public function createProductCommonData(array $Products) {

        $ProductRequests = array();
        foreach ($Products as $Product){
            /**@var $Product Mage_Catalog_Model_Product */
            $ProductRequest = new Common\Request\Product();

            $StoreId = $Product->getStoreId();

            $ProductRequest->setProductCode($Product->getSku());
            $ProductRequest->setKeywords($Product->getMetaKeyword());
            $ProductRequest->setHeight($Product->getHeight());
            $ProductRequest->setWeight($Product->getWeight());
            $ProductRequest->setNetWeight($Product->getNetWeight());
            $ProductRequest->setLength($Product->getLength());
            $ProductRequest->setWidth($Product->getWidth());
            $ProductRequest->setVolume($Product->getVolume());
            $ProductRequest->setNetVolume($Product->getNetVolume());
            $ProductRequest->setName($Product->getName());
            $ProductRequest->setURL($Product->getProductUrl());
            $ProductRequest->setOriginalListPrice($Product->getPrice());
			$ProductRequest->setOriginalSalePrice($Product->getPrice());
            $ProductRequest->setGenericHSCode(null);
            $ProductRequest->setIsBundle($Product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE);
            $ProductRequest->setIsVirtual($Product->getIsVirtual());



            // Get the image url
            $ProductImageUrl = ($Product->getImage()) ? $Product->getImage() : $Product->getThumbnail();
            if(!empty($ProductImageUrl)) {

                try{
                    /**@var $ImageHelper Mage_Catalog_Helper_Image */
                    $ImageHelper =  Mage::helper('catalog/image')->init($Product, 'image');
                    $ProductRequest->setImageWidth($ImageHelper->getOriginalWidth());
                    $ProductRequest->setImageHeight($ImageHelper->getOriginalHeight());
                }
                catch (Exception $e) {
                    //do nothing
                }


                // Get the image full url
                $ProductImageUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($ProductImageUrl);
                $ProductRequest->setImageURL($ProductImageUrl);
                //@TODO why not $ImageHelper->__toString() ---from cache ??
            }

            $CountryOfManufacture = Mage::getResourceModel('catalog/product')->getAttributeRawValue($Product->getId(), 'country_of_manufacture',$StoreId);
            $ProductRequest->setOriginCountryCode($CountryOfManufacture);
            $IsBlockedForGlobale = Mage::getResourceModel('catalog/product')->getAttributeRawValue($Product->getId(), 'is_blocked_for_globale',$StoreId);
            $ProductRequest->setIsBlockedForGlobalE((boolean)$IsBlockedForGlobale);

            $ProductRequest->setDescription(Mage::helper('globale_base/data')->buildProductDescription($Product));
            $ProductRequest->setBrand($this->getProductBrandInfo($Product));
            $ProductRequest->setCategories($this->getProductCategories($Product));

            $ProductExtraAttributes = $this->getProductExtraAttributes($Product,$StoreId);
            $ProductRequest->setAttributes($ProductExtraAttributes);

            $LocalVATRateType = $this->buildLocalVATRateType($Product);
            $ProductRequest->setLocalVATRateType($LocalVATRateType);

            $EnglishProduct = $this->loadEnglishProduct($Product);

            if($EnglishProduct !== null){
                $EnglishStoreId = $EnglishProduct->getStoreId();
                $ProductRequest->setNameEnglish($EnglishProduct->getName());
                $ProductRequest->setDescriptionEnglish(Mage::helper('globale_base/data')->buildProductDescription($EnglishProduct));
                $ProductRequest->setAttributesEnglish($this->getProductExtraAttributes($Product, $EnglishStoreId));
            }

            //@TODO check usage -- set ProductGroupCode
            $this->setParentSkuAndId($Product->getId(),$ProductRequest);

            $ProductRequests[] = $ProductRequest;
        }

        return $ProductRequests;
    }


	/**
	 * Build Common Product for getCart/SentCart
	 * @param Mage_Catalog_Model_Product $Product
	 * @return Common\Request\Product
	 */
    public function updateProductCommonAdditionalData(Mage_Catalog_Model_Product $Product){

		/**@var $LoadedProduct Mage_Catalog_Model_Product */
		$LoadedProduct = Mage::getModel('catalog/product')->load($Product->getId());

		$ProductRequests = $this->createProductCommonData(array($LoadedProduct));
		/**@var $ProductRequest Common\Request\Product */
		$ProductRequest = current($ProductRequests);

		//data that came from Product
		$ProductRequest->setProductCode($Product->getSku());
		$ProductRequest->setWeight($Product->getWeight());


		// //Add Selected ExtraAttributes
		$AdditionalProductExtraAttributes = $this->getAdditionalProductExtraAttributes($Product);
		if(!empty($AdditionalProductExtraAttributes)){

			$ProductExtraAttributes = array_merge($ProductRequest->getAttributes(), $AdditionalProductExtraAttributes);
			$ProductRequest->setAttributes($ProductExtraAttributes);
		}

		//Add Selected ExtraAttributes English Product
		$EnglishStoreId = Mage::getModel('globale_base/settings')->getEnglishStoreId();
		if($EnglishStoreId) {
			$EnglishAdditionalProductExtraAttributes = $this->getAdditionalProductExtraAttributes($Product, $EnglishStoreId);

			if (!empty($EnglishAdditionalProductExtraAttributes)) {

				$EnglishProductExtraAttributes = array_merge($ProductRequest->getAttributesEnglish(), $EnglishAdditionalProductExtraAttributes);
				$ProductRequest->setAttributesEnglish($EnglishProductExtraAttributes);
			}
		}
		//@todo check usage
		//$ProductRequest->setParentCartItemId($ParentCartItemId   ID if parent );
		//$ProductRequest->setCartItemOptionId($CartItemOptionId);  ?????

		return $ProductRequest;
	}


    /**
     * Load same Product from "English Store" . If no store defined as "English Store" - return null
     * @param Mage_Catalog_Model_Product $Product
     * @return Mage_Catalog_Model_Product|null
     */
    public function loadEnglishProduct(Mage_Catalog_Model_Product $Product){

        $EnglishProduct = null;
        $EnglishStoreId = Mage::getModel('globale_base/settings')->getEnglishStoreId();

        if(!empty($EnglishStoreId)  ){

            if($Product->getStoreId() == $EnglishStoreId ){
                $EnglishProduct = $Product;
            }else{
                $EnglishProduct = Mage::getModel('catalog/product')
                    ->setStoreId($EnglishStoreId)
                    ->load($Product->getId());
            }
        }
        return $EnglishProduct;
    }

    /**
     * @param $ChildId
     * @param Common\Request\Product $ProductRequest
     * @return boolean
     * @todo check what wg do in the case of more than one parent ? // tbs=> 19282
     */
    private function setParentSkuAndId($ChildId,$ProductRequest){
        $ParentProduct = Mage::getModel('catalog/product_type_configurable');
        $ParentProductIds = $ParentProduct->getParentIdsByChild($ChildId);
        if(!isset($ParentProductIds[0]) || empty($ParentProductIds[0])){
            $ParentProduct = Mage::getModel('bundle/product_type');
            $ParentProductIds = $ParentProduct->getParentIdsByChild($ChildId);
        }

        if(!isset($ParentProductIds[0])){
            return false;
        }

        $ParentCollection = Mage::getResourceModel('catalog/product_collection')
            ->addFieldToFilter('entity_id', array('in'=>$ParentProductIds[0]))
            ->addAttributeToSelect('sku');
        $ParentSku = $ParentCollection->getColumnValues('sku');

        if(isset($ParentSku[0])){
            $ProductRequest->setProductGroupCode($ParentSku[0]);
            return true;
        }

        return false;
    }


    /**
     * Create SDK object of VATRateType from Magento settings
     * @param Mage_Catalog_Model_Product $Product
     * @return Common\VatRateType
     */
    public function buildLocalVATRateType(Mage_Catalog_Model_Product $Product)
    {
        $Store = Mage::app()->getStore();
        $Request = Mage::getSingleton('tax/calculation')->getRateRequest(null, null, null, $Store);
        $TaxClassId = $Product->getData('tax_class_id');

        $AppliedRates = Mage::getSingleton('tax/calculation')->getAppliedRates($Request->setProductClassId($TaxClassId));

        if(!empty($AppliedRates) && isset($AppliedRates[0]) ){
            $Rate = $AppliedRates[0]['percent'];
            $RateName = $AppliedRates[0]['id'];
        }else{
            $Rate = 0;
            $RateName = 'Magento No Tax';
        }

        $LocalVATRateType = new GlobalE\SDK\Models\Common\VatRateType($Rate, $RateName, $RateName);
        return $LocalVATRateType;
    }

    /**
     * Get Product Brand Info
     * @param Mage_Catalog_Model_Product $Product
     * @return array $BrandInfo
     */
    protected function getProductBrandInfo(Mage_Catalog_Model_Product $Product) {

        // get the brand name from magento admin configuration
        $BrandName = Mage::getStoreConfig(Globale_Base_Model_Product::XML_PATH_BRAND_ATTR);

        $BrandCode = Mage::getResourceModel('catalog/product')->getAttributeRawValue($Product->getId(),$BrandName,1);
        $Brands = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $BrandName)->getSource()->getAllOptions();

        $BrandInfo = array();
        if(count($Brands) && $BrandCode) {
            foreach ($Brands as $Brand) {
                if($Brand['value'] == $BrandCode) {
                    $BrandInfo['BrandCode'] = $BrandCode;
                    $BrandInfo['Name'] = $Brand['label'];
                    break;
                }
            }
        }
        return $BrandInfo;
    }

    /**
     * Get Product Categories
     * @param Mage_Catalog_Model_Product $Product
     * @return array $CategoryList
     */
    protected function getProductCategories(Mage_Catalog_Model_Product $Product) {

        $Categories = $Product->getCategoryIds();
        $CategoryList = array();
        foreach ($Categories as $Category) {
            /** @var $CategoryInformation Mage_Catalog_Model_Category */
            $CategoryInformation = Mage::getModel('catalog/category')->load($Category) ;
            $CategoryList[] = array("CategoryCode" => $Category,
                "Name" => $CategoryInformation->getName()
            );
        }
        return $CategoryList;
    }


    /**
     * Build Array of product extra attributes for store
     * @param Mage_Catalog_Model_Product $Product
     * @param int $StoreId
     * @return Attribute[]
     */
    protected function getProductExtraAttributes(Mage_Catalog_Model_Product $Product, $StoreId){

        $AttrValues = array();
        $AttrArray = $this->getExtraAttributeArray();

        if(!empty($AttrArray)){
            foreach($AttrArray as $AttrName){

                $AttrInfo = $this->getProductAttributeInfo($Product,$AttrName,$StoreId);

                if(!empty($AttrInfo)){
                    $AttrValues[] = $AttrInfo;
                }
            }
        }

        return $AttrValues;
    }


    /**
     * Return array of Global-e Extra Attributes
     * @return array
     */
    protected function getExtraAttributeArray(){
        $AttrArray = array();
        $AttrList = Mage::getStoreConfig(Globale_Base_Model_Product::XML_PATH_EXTRA_ATTR_LIST);
        if(!empty($AttrList)) {
            $AttrArray = explode(',', $AttrList);
            foreach($AttrArray as &$AttrName){
                $AttrName = trim($AttrName);
            }
        }
        return  $AttrArray;
    }



    /**
     * Get Attribute Info of the product
     * @param Mage_Catalog_Model_Product $Product
     * @param $AttrName
     * @param $StoreId
     * @return null | Attribute
     */
    protected function getProductAttributeInfo(Mage_Catalog_Model_Product $Product, $AttrName, $StoreId){
        /**@var $Attr Mage_Catalog_Model_Resource_Eav_Attribute */
        $Attr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $AttrName);

        $StoreLabels = $Attr->getStoreLabels();
        if (isset($StoreLabels[$StoreId])) {
            $Label = $StoreLabels[$StoreId];
        } else {
            $Label = $Attr->getStoreLabel($StoreId);
        }

        $AttrInputType = $Attr->getFrontendInput();
        $AttributeRawValue = Mage::getResourceModel('catalog/product')->getAttributeRawValue($Product->getId(), $AttrName, $StoreId);

        //if product doesn't have value for attribute
        if($AttributeRawValue === false){
            return null;
        }

        switch($AttrInputType){
            case 'select':
                $Source = $Attr->getSource();
                $AttributeValue = $Source->getOptionText($AttributeRawValue);

                //If need to load attribute data from Store !== Product's store
                if($Product->getStoreId() !== $StoreId ){

                    $AttributeValue = $this->loadAttributeOptionValueByStoreId($Source, $AttributeValue, $StoreId);
                }

                break;
            case 'text':
            default:
                $AttributeValue = $AttributeRawValue;
        }

        if(empty($AttributeValue)){
            return null;
        }


        $AttributeInfo = new Attribute();
        $AttributeInfo
            ->setAttributeCode($Label)
            ->setName($AttributeValue)
            ->setAttributeTypeCode($AttrName);

        return $AttributeInfo;
    }

    /**
     * Load Attribute Name - Attribute Option value , according to Store settings (multi language)
     * @param Mage_Eav_Model_Entity_Attribute_Source_Abstract $Source
     * @param string $AttributeValue
     * @param int $StoreId
     * @return string
     */
    public function loadAttributeOptionValueByStoreId(Mage_Eav_Model_Entity_Attribute_Source_Abstract $Source, $AttributeValue, $StoreId)
    {
        $NewAttributeValue = '';

        $Resource = Mage::getSingleton('core/resource');
        $ReadConnection = $Resource->getConnection(Mage_Core_Model_Resource::DEFAULT_READ_RESOURCE);

        $OptionId = $Source->getOptionId($AttributeValue);

        /**
         * we use simple query way because $Source->getFlatUpdateSelect of Mage_Eav_Model_Resource_Entity_Attribute_Option has a problem :
         * no table with alas 'e' for left join
         */

        $Sql = 'SELECT IF(v2.value_id > 0, v2.value, v.value) AS att_value
					FROM %s o
					INNER JOIN %s v ON o.option_id=v.option_id  AND v.store_id = %d
					LEFT JOIN %s v2 ON o.option_id=v2.option_id AND v2.store_id = %d
					WHERE o.attribute_id= %d AND o.option_id = %d ';

        $Query = sprintf($Sql,
            $Resource->getTableName('eav/attribute_option'),
            $Resource->getTableName('eav/attribute_option_value'), Mage_Core_Model_App::ADMIN_STORE_ID,
            $Resource->getTableName('eav/attribute_option_value'), $StoreId,
            $Source->getAttribute()->getAttributeId(), $OptionId
        );

        $Result = $ReadConnection->fetchAll($Query);

        if (isset($Result[0]['att_value'])) {
            $NewAttributeValue = $Result[0]['att_value'];
        }

        return $NewAttributeValue;
    }


	/**
	 * Load Configurable Selected Attributes in required structure
	 * @param Mage_Catalog_Model_Product $Product
	 * @return array
	 */
	public function loadSelectedAttributes(Mage_Catalog_Model_Product $Product){
		$SelectedAttributes = array();

		$SelectedAttributesInfo = $Product->getTypeInstance(true)->getSelectedAttributesInfo($Product);
		if(!empty($SelectedAttributesInfo)){
			//convert structure from array [0 => label, value] to array [label => value]
			foreach ($SelectedAttributesInfo AS $SelectedAttribute){
				$SelectedAttributes[$SelectedAttribute['label']] = $SelectedAttribute['value'];
			}
		}
		return $SelectedAttributes;
	}


	/**
	 * Load Additional Extra Attributes - for example Configurable Selected Options
	 * @param Mage_Catalog_Model_Product $Product
	 * @param int $StoreId
	 * @return Attribute[] |null
	 */
	public function getAdditionalProductExtraAttributes(Mage_Catalog_Model_Product $Product, $StoreId = null){
		$AdditionalProductExtraAttributes = array();

		if($Product->getTypeId() == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE ){
			$AdditionalProductExtraAttributes = $this->loadConfigurableAttributes($Product, $StoreId);
		}
		//@TODO -- Add case for BUNDLES
		return $AdditionalProductExtraAttributes;
	}

	/**
	 * Load Selected Configurable Attributes Data as array of Attribute object
	 * @param Mage_Catalog_Model_Product $Product
	 * @param $StoreId
	 * @return Attribute[] | array()
	 */
	protected function loadConfigurableAttributes(Mage_Catalog_Model_Product $Product, $StoreId = null){
		$ConfigurableAttributes = array();

		if (!$StoreId) {
			$StoreId = $Product->getStoreId();
		}

		$SelectedAttributesInfo = $this->loadSelectedAttributes($Product);

		if (empty($SelectedAttributesInfo)) {
			return array();
		}

		$AdditionalAttributesCollection = $Product->getTypeInstance(true)->getConfigurableAttributes($Product);

		/**@var $AdditionalAttributesCollection Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection */
		foreach ($AdditionalAttributesCollection as $Attribute) {
			$Label = $Attribute->getLabel();

			//if Attribute not selected ==> continue
			if (!(array_key_exists($Label, $SelectedAttributesInfo))) {
				continue;
			}
			$SelectedOptionValue = $SelectedAttributesInfo[$Label];

			/**@var $ProductAttribute Mage_Catalog_Model_Resource_Eav_Attribute */
			$ProductAttribute = $Attribute->getProductAttribute();

			//AttributeTypeCode --> Magento AttributeCode
			$AttributeTypeCode = $ProductAttribute->getAttributeCode();

			//Getting AttributeCode --> Magento Attribute Store Label or Attribute Label
			$StoreLabels = $ProductAttribute->getStoreLabels();
			if (isset($StoreLabels[$StoreId])) {
				$AttributeCode = $StoreLabels[$StoreId];
			} else {
				$AttributeCode = $ProductAttribute->getStoreLabel($StoreId);
			}

			//IF store doesn't have label => use Attribute Label
			if (empty($AttributeCode)) {
				$AttributeCode = $Label;
			}

			//If need to load attribute data from Store != Product's store
			if ($Product->getStoreId() !== $StoreId) {
				/**@var $Source Mage_Eav_Model_Entity_Attribute_Source_Abstract */
				$Source = $ProductAttribute->getSource();
				$SelectedOptionValue = $this->loadAttributeOptionValueByStoreId($Source, $SelectedOptionValue, $StoreId);
			}

			$Attribute = new Attribute();
			$Attribute
				->setAttributeTypeCode($AttributeTypeCode)
				->setName($SelectedOptionValue)
				->setAttributeCode($AttributeCode);

			$ConfigurableAttributes[] = $Attribute;
		}
		return $ConfigurableAttributes;

	}

}