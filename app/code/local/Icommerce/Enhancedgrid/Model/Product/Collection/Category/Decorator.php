<?php
/**
 * Copyright © 2009-2011 Icommerce Nordic AB
 *
 * Icommerce reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Icommerce, except as provided by licence. A licence
 * under Icommerce's rights in the Program may be available directly from
 * Icommerce.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Icommerce
 * @package     Icommerce_Enhancedgrid
 * @copyright   Copyright © 2009-2012 Icommerce Nordic AB
 */
class Icommerce_Enhancedgrid_Model_Product_Collection_Category_Decorator extends Icommerce_Enhancedgrid_Model_Collection_Decorator_Abstract {

    
    public function setCollection(Icommerce_Enhancedgrid_Model_Resource_Eav_Mysql4_Product_Collection $collection) {
        return parent::setCollection( $collection );
    }

    /**
     * Adds category data for the products collection using the currently stored collection model.
     */
    public function addCategories() {
        
        $collection = $this->getCollection();
        
        $alias_prefix = $this->_getAliasPrefix();
        
        $collection->joinField( 'category_id', 'catalog/category_product', 'category_id', 'product_id=entity_id', null, 'left' );

        $category_name_attribute_id = Mage::getModel( 'eav/entity_attribute' )->loadByCode( 'catalog_category', 'name' )->getId();
        
        //@nelkaake -m 13/11/10: Added support for tables with prefixes
        $ccev_t = Mage::getConfig()->getTablePrefix() . 'catalog_category_entity_varchar';
        
        $collection->joinField( 'categories', $ccev_t, "GROUP_CONCAT({$alias_prefix}categories.value)", 'entity_id=category_id', 
            "{$alias_prefix}categories.attribute_id={$category_name_attribute_id}", 'left' );
        
        $collection->joinField( 'category', $ccev_t, 'value', 'entity_id=category_id', 
            "{$alias_prefix}category.attribute_id={$category_name_attribute_id}", 'left' );
        
        $collection->groupByAttribute( 'entity_id' );
        
        return $this;
    
    }
    


    protected function _getAliasPrefix() {
        if(Mage::helper('enhancedgrid/version')->isBaseMageVersionAtLeast('1.6')) {
            return 'at_';
        } 
        
        return '_table_';
    }

}