<?php
/**
 * Copyright (c) 2009-2012 Icommerce Nordic AB
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
 * @category    Vaimo
 * @package     Grid.php
 * @copyright   Copyright (c) 2009-2012 Icommerce Nordic AB
 * @author      Kaarel Taniloo
 */

class Icommerce_Style_Block_Adminhtml_Style_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  protected $relatedProducts = array();
  protected $defaultProduct = array();
  public function __construct()
  {
      parent::__construct();
      $this->setId('style');
      $this->setDefaultSort('ss');
      $this->setDefaultDir('DESC');
      $this->setUseAjax(true);

      $this->setAction($this->getUrl('style/admin/saveRelated'));
      $this->setMethod('post');

      $this->setSaveParametersInSession(true);
  }
  protected function _getProductId() {
      return $this->getRequest()->getParam('product_id');
  }

  protected function _prepareCollection()
  {
      static $collection;

      if($collection instanceof Mage_Catalog_Model_Resource_Product_Collection) {
          $this->setCollection($collection);
          return parent::_prepareCollection();
      }
      $collection = Mage::getModel('catalog/product')->getCollection()
              ->addAttributeToFilter('type_id', array('eq' => 'style'))
              ->addAttributeToSelect('related_products')
              ->addAttributeToSelect('name');

      $productId = $this->_getProductId();
      $collection->getSelect()
          ->join(array('catalog_product_link'=>'catalog_product_link'), '(e.entity_id = catalog_product_link.product_id AND catalog_product_link.linked_product_id = \'' . $productId . '\')', array('catalog_product_link.linked_product_id'))
          ->group('e.entity_id');

      $cloneCollection = clone $collection;
      $this->setCollection($collection);

      $childProduct = Mage::getModel('catalog/product');
      $_prod = Mage::getModel('catalog/product')->load($this->_getProductId());

      foreach($cloneCollection as $childProduct) {
          $rel = $childProduct->getRelatedProductIds();
          foreach($rel as $relatedProductId) {
              if($productId == $relatedProductId) {
                $this->relatedProducts[] = $childProduct->getId();
                    if($_prod->getDefaultStyleId() == $childProduct->getId())
                        $this->defaultProduct[0]  = $childProduct->getId();
              }
          }
      }
  }

  protected function _prepareColumns()
  {
      $columns = $this->_prepareCollection();
      $this->addColumn('entity_id', array(
          'header'    => Mage::helper('catalog')->__('Status'),
          'html_name'      => 'ss',
          'type'          => 'checkbox',
          'values'    => $this->relatedProducts,
          'align'     => 'center',
          'index'     => 'entity_id'
      ));

      $this->addColumn('default_style', array(
          'header'    => Mage::helper('catalog')->__('Default'),
          'html_name'      => 'defaultbox',
          'type'          => 'radio',
          'values'    => $this->defaultProduct,
          'align'     => 'center',
          'index'     => 'entity_id'
      ));

      $this->addColumn('id', array(
          'header'    => Mage::helper('catalog')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'id',
      ));

      $this->addColumn('name', array(
          'header'    => Mage::helper('catalog')->__('Name'),
          'align'     =>'left',
          'index'     => 'name',
      ));

      $this->addColumn('sku', array(
          'header'    => Mage::helper('catalog')->__('Sku'),
          'align'     =>'left',
          'index'     => 'sku',
      ));

      return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }


}