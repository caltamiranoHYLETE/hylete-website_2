<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Bakerlooorders_Edit_Tab_Items_Grid extends Mage_Adminhtml_Block_Widget_Grid {


    protected $_itemsCollection;

    public function __construct() {
        parent::__construct();

        $this->setId('bakerlooOrderItems');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);

    }

    protected function _prepareCollection() {

        $collection = new Varien_Data_Collection();
        $order = Mage::registry('bakerlooorder');

        if(is_null($order))
            $order = Mage::getModel('bakerloo_restful/order')->load($this->getRequest()->getParam('id'));

        $payload = json_decode($order->getJsonPayload());
        $error = $order->getFailMessage();

        if(!is_null($payload) and $payload){
            $products = isset($payload->products) ? $payload->products : array();
            if(!empty($payload->returns))
                $products = array_merge($products, $payload->returns);

            $collection = $this->_getItemsCollection($products, $error);
        }

        $this->_itemsCollection = $collection;

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    private function _getItemsCollection($products, $error){
        $collection = new Varien_Data_Collection();

        foreach($products as $prod){
            if($prod->type === 'bundle'){
                $bundleOptions = $prod->bundle_option;

                foreach($bundleOptions as $option){
                    $selections = $option->selections;

                    foreach($selections as $select){
                        if(isset($select->selected) and $select->selected){
                            $item = new Varien_Object();
                            $mageProduct = Mage::getModel('catalog/product')->load($select->product_id);

                            $item->setProductId($select->product_id);
                            $item->setName($mageProduct->getName());
                            $item->setSku($mageProduct->getSku());
                            $item->setQty($prod->qty * $select->qty);

                            if($error) {
                                $pattern = '/'.preg_quote($item->getSku(), '/').'/';

                                if(preg_match($pattern, $error))
                                    $item->setError($error);
                            }

                            $collection->addItem($item);
                        }
                    }
                }
            }
            elseif($prod->type === 'configurable') {
                $item = new Varien_Object();
                $mageProduct = Mage::getModel('catalog/product')->load($prod->child_id);

                $item->setProductId($mageProduct->getId());
                $item->setName($mageProduct->getName());
                $item->setSku($mageProduct->getSku());
                $item->setQty($prod->qty);


                if($error) {
                    $pattern = '/'.preg_quote($item->getSku(), '/').'/';

                    if(preg_match($pattern, $error))
                        $item->setError($error);
                }

                $collection->addItem($item);
            }
            else {
                $item = new Varien_Object();
                $mageProduct = Mage::getModel('catalog/product')->load($prod->product_id);

                $item->setProductId($prod->product_id);
                $item->setName($mageProduct->getName());
                $item->setSku($mageProduct->getSku());
                $item->setQty($prod->qty);


                if($error) {
                    $pattern = '/'.preg_quote($item->getSku(), '/').'/';

                    if(preg_match($pattern, $error))
                        $item->setError($error);
                }

                $collection->addItem($item);
            }
        }


        return $collection;
    }

    protected function _prepareColumns() {

        $h = Mage::helper('bakerloo_restful');

        $this->addColumn('id', array(
            'header' => $h->__('Product ID'),
            'index' => 'product_id',
            'type' => 'number',
            'filter' => false,
            'sortable' => false
        ));
        $this->addColumn('name', array(
            'header' => $h->__('Name'),
            'index' => 'name',
            'filter' => false,
            'sortable' => false
        ));
        $this->addColumn('sku', array(
            'header' => $h->__('SKU'),
            'index' => 'sku',
            'renderer' => 'bakerloo_restful/adminhtml_widget_grid_column_renderer_orderSku',
            'filter_condition_callback' => array($this, '_filterBySku')
        ));
        $this->addColumn('qty', array(
            'header' => $h->__('Quantity'),
            'index' => 'qty',
            'filter' => false,
            'sortable' => false
        ));
        $this->addColumn('error', array(
            'header' => $h->__('Error'),
            'index' => 'error',
            'filter' => false,
            'sortable' => false
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('adminhtml/bakerlooorders_edit_tab_items/grid', array('_current' => true));
    }

    /**
     * Return row url for js event handlers
     *
     * @param Varien_Object
     * @return string
     */
    public function getRowUrl($item) {
        return false;
    }

    protected function _filterBySku($collection, $column){
        $selection = new Varien_Data_Collection();

        $value = $column->getFilter()->getValue();

        if(!$value or empty($value))
            return $this;
        else {
            $pattern = '/^' . $value . '/';
            foreach($this->_collection as $item){
                if(preg_match($pattern, $item->getSku()))
                    $selection->addItem($item);
            }
        }
        $this->setCollection($selection);
        return $this;
    }

    /**
     * Sets sorting order by some column
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $dir = strtoupper($column->getDir());

            $items = $collection->getItems();
            usort($items, function($a, $b) use ($dir){
                if($dir === 'DESC')
                    return strcmp($b->getSku(), $a->getSku());
                else
                    return strcmp($a->getSku(), $b->getSku());
            });

            $collection = new Varien_Data_Collection();
            foreach($items as $item)
                $collection->addItem($item);
        }

        $this->setCollection($collection);
        return $this;
    }


}