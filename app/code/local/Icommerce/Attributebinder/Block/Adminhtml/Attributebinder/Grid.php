<?php

class Icommerce_Attributebinder_Block_Adminhtml_Attributebinder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('attributebinderGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('attributebinder/attributebinder')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
                                    'header' => Mage::helper('attributebinder')->__('ID'),
                                    'align' => 'right',
                                    'width' => '50px',
                                    'index' => 'id',
                               ));

        $this->addColumn('main_attribute', array(
                                       'header' => Mage::helper('attributebinder')->__('Target attribute'),
                                       'align' => 'left',
                                       'index' => 'main_attribute_label',
                                  ));


        $this->addColumn('bind_attribute', array(
                                       'header' => Mage::helper('attributebinder')->__('Source attribute'),
                                       'align' => 'left',
                                       'index' => 'bind_attribute_label',
                                  ));

       
        $this->addColumn('action',
                         array(
                              'header' => Mage::helper('attributebinder')->__('Action'),
                              'width' => '100',
                              'type' => 'action',
                              'getter' => 'getId',
                              'actions' => array(
                                  array(
                                      'caption' => Mage::helper('attributebinder')->__('Edit'),
                                      'url' => array('base' => '*/*/edit'),
                                      'field' => 'id'
                                  )
                              ),
                              'filter' => false,
                              'sortable' => false,
                              'index' => 'stores',
                              'is_system' => true,
                         ));


        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('attributebinder');

        $this->getMassactionBlock()->addItem('delete', array(
                                                            'label' => Mage::helper('attributebinder')->__('Delete'),
                                                            'url' => $this->getUrl('*/*/massDelete'),
                                                            'confirm' => Mage::helper('attributebinder')->__('Are you sure?')
                                                       ));
        $this->getMassactionBlock()->addItem('reindex', array(
                                                            'label' => Mage::helper('attributebinder')->__('Reindex existing products'),
                                                            'url' => $this->getUrl('*/*/reindex'),
                                                            'confirm' => Mage::helper('attributebinder')->__('Reindex existing products with bindnings now?')
                                                       ));

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}