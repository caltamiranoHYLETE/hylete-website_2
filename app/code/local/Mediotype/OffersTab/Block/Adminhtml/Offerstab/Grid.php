<?php

/**
 * Class Grid
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Block_Adminhtml_Offerstab_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	/**
	 * Grid constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->setId('offer_id');
		$this->setDefaultSort('offer_id');
	}

	/**
	 * @return $this
	 */
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('mediotype_offerstab/offer')->getCollection();
		$this->setCollection($collection);

		parent::_prepareCollection();

		return $this;
	}

	/**
	 * @return mixed
	 */
	protected function _prepareColumns()
	{
		$this->addColumn('offer_id', array(
			'header' => Mage::helper('mediotype_offerstab')->__('ID'),
			'sortable' => true,
			'width' => '60',
			'index' => 'offer_id'
		));

		// Custom renderer
//		$this->addColumn('order_id', array(
//			'header' => Mage::helper('mediotype_offerstab')->__('ID'),
//			'sortable' => true,
//			'width' => '60',
//			'index' => 'offer_id',
//			'renderer' => 'Mediotype_OffersTab_Block_Adminhtml_Promo_Offerstab_Grid_Renderer_Id',
//		));

		$this->addColumn('title', array(
			'header' => Mage::helper('mediotype_offerstab')->__('Title'),
			'sortable' => true,
			'width' => '60',
			'index' => 'title',
			'type' => 'text'
		));

		$this->addColumn('static_block_id', array(
			'header' => Mage::helper('mediotype_offerstab')->__('Static Block Id'),
			'sortable' => true,
			'width' => '60',
			'index' => 'static_block_id',
			'type' => 'text'
		));

		$this->addColumn('customer_group_ids', array(
			'header' => Mage::helper('mediotype_offerstab')->__('Customer Group Ids'),
			'sortable' => true,
			'width' => '60',
			'index' => 'customer_group_ids',
			'type' => 'text'
		));

		$this->addColumn('category_ids', array(
			'header' => Mage::helper('mediotype_offerstab')->__('Category Ids'),
			'sortable' => true,
			'width' => '60',
			'index' => 'category_ids',
			'type' => 'text'
		));

		$this->addColumn('product_ids', array(
			'header' => Mage::helper('mediotype_offerstab')->__('Product Ids'),
			'sortable' => true,
			'width' => '60',
			'index' => 'product_ids',
			'type' => 'text'
		));

		$this->addColumn('priority', array(
			'header' => Mage::helper('mediotype_offerstab')->__('Priority'),
			'sortable' => true,
			'width' => '60',
			'index' => 'priority',
			'type' => 'text'
		));

		$this->addColumn('landing_page_url', array(
			'header' => Mage::helper('mediotype_offerstab')->__('Landing Page URL'),
			'sortable' => true,
			'width' => '60',
			'index' => 'landing_page_url',
			'type' => 'text'
		));

		$this->addColumn('status', array(
			'header' => Mage::helper('mediotype_offerstab')->__('Status'),
			'sortable' => true,
			'width' => '60',
			'index' => 'status',
			'type' => 'text'
		));

		$this->addColumn('created_at', array(
			'header' => Mage::helper('mediotype_offerstab')->__('Created At'),
			'sortable' => true,
			'width' => '60',
			'index' => 'created_at',
			'type' => 'datetime'
		));

		$this->addColumn('updated_at', array(
			'header' => Mage::helper('mediotype_offerstab')->__('Updated At'),
			'sortable' => true,
			'width' => '60',
			'index' => 'updated_at',
			'type' => 'datetime'
		));

		return parent::_prepareColumns();
	}

	/**
	 * @param $row
	 * @return string
	 */
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}
