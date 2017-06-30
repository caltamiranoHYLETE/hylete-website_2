<?php
/**
 * Copyright (c) 2009-2013 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
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
 * @package     Vaimo_Blog
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 */
class Vaimo_Blog_Block_Adminhtml_Blog_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('lookGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        //$this->setUseAjax(true);
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $blogProductType = Mage::getStoreConfig( "blog/settings/product_type");

        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('blog_publish_date')
            ->addAttributeToSelect('blog_author');

        if ($blogProductType != "") {
            $collection->addAttributeToFilter('type_id' , array('eq' => $blogProductType));
        }

        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner', $adminStore);
            $collection->joinAttribute('custom_name', 'catalog_product/name', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId());
        }
        else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }

        $this->setCollection($collection);

        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id',
            array(
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'entity_id',
            ));

        $this->addColumn('name',
            array(
                'header'=> Mage::helper('catalog')->__('Name'),
                'index' => 'name',
        ));

        $store = $this->_getStore();

        if ($store->getId()) {
        $this->addColumn('custom_name',
            array(
                'header'=> Mage::helper('catalog')->__('Name in %s', $store->getName()),
                'index' => 'custom_name',
            ));
        }

        if (Mage::helper('blog')->isAuthorActive()) {
            $this->addColumn('blog_author',
                array(
                    'header'=> Mage::helper('catalog')->__('Author'),
                    'width' => '120px',
                    'index' => 'blog_author',
                    'type'  => 'text'
            ));
        }

        if (Mage::helper('blog')->isShowPublishDateActive()) {
            $this->addColumn('blog_publish_date',
                array(
                    'header'=> Mage::helper('catalog')->__('Publish Date'),
                    'width' => '70px',
                    'index' => 'blog_publish_date',
                    'type'  => 'date'
                ));
        }


         $this->addColumn('visibility',
             array(
                 'header'=> Mage::helper('catalog')->__('Visibility'),
                 'width' => '70px',
                 'index' => 'visibility',
                 'type'  => 'options',
                 'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
         ));

         $this->addColumn('status',
             array(
                 'header'=> Mage::helper('catalog')->__('Status'),
                 'width' => '70px',
                 'index' => 'status',
                 'type'  => 'options',
                 'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
         ));

         if (!Mage::app()->isSingleStoreMode()) {
             $this->addColumn('websites',
                 array(
                    'header'=> Mage::helper('catalog')->__('Websites'),
                    'width' => '100px',
                    'sortable'  => false,
                    'index'     => 'websites',
                    'type'      => 'options',
                    'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
             ));
         }

         $this->addColumn('action',
             array(
                 'header'    => Mage::helper('catalog')->__('Action'),
                 'width'     => '50px',
                 'type'      => 'action',
                 'getter'     => 'getId',
                 'actions'   => array(
                     array(
                         'caption' => Mage::helper('catalog')->__('Edit'),
                         'url'     => array(
                             'base'=>'adminhtml/catalog_product/edit',
                             'params'=>array('store'=>$this->getRequest()->getParam('store'))
                         ),
                         'field'   => 'id'
                     )
                 ),
                 'filter'    => false,
                 'sortable'  => false,
                 'index'     => 'stores',
         ));

         return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getId()));
    }

    /*public function getGridUrl()
    {
        return $this->getUrl('*\/*\/grid', array('_current'=>true));
    }*/


     protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites',
                    'catalog/product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }
}