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
class Icommerce_Enhancedgrid_Model_System_Config_Source_Columns_Show
{
    public function toOptionArray()
    {
        $collection = $this->_getAttrCol();
    
        $cols = array();
        $cols[] = array('value' => 'id',   'label' => 'ID');
        $cols[] = array('value' => 'type_id',   'label' => Mage::helper('enhancedgrid')->__('Type (simple, bundle, etc)'));
        $cols[] = array('value' => 'attribute_set_id',   'label' => Mage::helper('enhancedgrid')->__('Attribute Set'));
        $cols[] = array('value' => 'qty',   'label' => Mage::helper('enhancedgrid')->__('Quantity'));
        $cols[] = array('value' => 'websites',   'label' => Mage::helper('enhancedgrid')->__('Websites'));
        $cols[] = array('value' => 'categories',   'label' => Mage::helper('enhancedgrid')->__('Categories'));
        //@nelkaake Tuesday April 27, 2010 :
        $cols[] = array('value' => 'created_at',   'label' => Mage::helper('enhancedgrid')->__('Date Created'));
        $cols[] = array('value' => 'updated_at',   'label' => Mage::helper('enhancedgrid')->__('Date Updated'));
        foreach($collection->getItems() as $col) {
            $cols[] = array('value' => $col->getAttributeCode(),   'label' => $col->getFrontendLabel());
        }
        return $cols;
    }

    /**
     * @return Mage_Eav_Model_Mysql4_Entity_Attribute_Collection
     */
    protected function _getAttrCol() {
        
        if ( Mage::helper( 'enhancedgrid/version' )->isBaseMageVersionAtLeast( '1.4' ) ) {
            $collection = Mage::getResourceModel( 'catalog/product_attribute_collection' );
            
        } else {
            $type_id = Mage::getModel( 'eav/entity' )->setType( 'catalog_product' )->getTypeId();
            $collection = Mage::getResourceModel( 'eav/entity_attribute_collection' );
            $collection->setEntityTypeFilter( $type_id );
        }
        
        $collection->addFilter( "is_visible", 1 );
        
        return $collection;
    }
}