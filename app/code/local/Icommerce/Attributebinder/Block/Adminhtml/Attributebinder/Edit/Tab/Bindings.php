<?php


class Icommerce_Attributebinder_Block_Adminhtml_Attributebinder_Edit_Tab_Bindings extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('bindingGrid');
        // This is the primary key of the database
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('attributebinder')->__('Bindings'));

        //hide stuff that dont apply
        $this->setFilterVisibility(false);
        $this->setHeadersVisibility(false);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
    }

    /**
     * Retrieve the label used for the tab relating to this block
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Bindings');
    }

    /**
     * Retrieve the title used by this tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Click to view bindings');
    }

    /**
     * Determines whether to display the tab
     * Add logic here to decide whether you want the tab to display
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Stops the tab being hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareCollection()
    {
        $data = Mage::registry('attributebinder_data')->getData();
        $binder_id = $data ? $data['id'] : 0;

        $collection = Mage::getModel('attributebinder/attributebinder')->getBinderCollection($binder_id);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    private function getMainAttributeValues(){

        $data = Mage::registry('attributebinder_data')->getData();
        return Mage::getModel('attributebinder/attributebinder')->getAttributeValues($data["main_attribute_id"], true);

    }


    protected function _prepareColumns()
    {

        //$data = Mage::registry('attributebinder_data')->getData();

        $this->addColumn('main_attribute_value[]', array(
            'header' => Mage::helper('attributebinder')->__('Main attribute value'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'main_attribute_value',
            'type' => 'select',
            'filter'    => false,
            'sortable'  => false,
            'options' => $this->getMainAttributeValues()
       ));

        $this->addColumn('bind_attribute_value[]', array(
            'header'    => Mage::helper('attributebinder')->__('Source attribute value'),
            'align'     =>'left',
            'index'     => 'bind_attribute_value',
            'hiddenvalue' => 'bind_attribute_id',
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => 'Icommerce_Attributebinder_Block_Adminhtml_Column_HiddenFieldColumn',
        ));

        $this->addColumn('add_more_bindings[]', array(
            'header' => Mage::helper('attributebinder')->__('Add more bindings'),
            'align' => 'right',
            'width' => '120px',
            'index' => 'add_more_bindings',
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => 'Icommerce_Attributebinder_Block_Adminhtml_Column_DuplicateColumn',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return null;
    }

}
