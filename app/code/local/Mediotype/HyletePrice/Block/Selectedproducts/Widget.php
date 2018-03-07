<?php

/**
 * Selected products widget display block extensions.
 * @category  Class
 * @package   Mediotype_HyletePrice
 * @author    Rick Buczynski <rick@mediotype.com>
 * @copyright 2018 Mediotype
 */

/**
 * Class declaration
 * @category Class
 * @package  Mediotype_HyletePrice
 * @author   Rick Buczynski <rick@mediotype.com>
 */

class Mediotype_HyletePrice_Block_Selectedproducts_Widget extends Icommerce_SelectedProducts_Block_Widget
{
    /**
     * Prepare global layout.
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!$this->getChild('msrp_price')) {
            $this->append(
                $this->getLayout()
                    ->createBlock('mediotype_hyleteprice/catalog_product_list_msrp', 'msrp_price')
            );
        }

        if (!$this->getChild('multipack_offer')) {
            $this->append(
                $this->getLayout()
                    ->createBlock('mediotype_hyleteprice/catalog_product_list_multipack', 'multipack_offer')
            );
        }
    }

    /**
     * Get the product collection.
     * Extended to apply custom widget parameters.
     * @param  string $attribute
     * @param  integer $num_get
     * @param  boolean $desc
     * @param  array $attribs
     * @param  array $attributesToFilter
     * @param  integer $instock_only
     * @param  array $xtra_options
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getRealCollection(
        $attribute          = 'all',
        $num_get            = 3,
        $desc               = true,
        $attribs            = array('entity_id', 'sku', 'image', 'name'),
        $attributesToFilter = array(), 
        $instock_only       = 0, 
        $xtra_options       = array()
    ) {
        $collection = parent::getRealCollection(
            $attribute,
            $num_get,
            $desc,
            $attribs,
            $attributesToFilter,
            $instock_only,
            $xtra_options
        );

        $types = array_filter(array_map('trim', explode(',', $this->getProductTypes())));

        if (!empty($types)) {
            $collection->addAttributeToFilter('type_id', array('in' => $types));
        }

        return $collection;
    }
}
