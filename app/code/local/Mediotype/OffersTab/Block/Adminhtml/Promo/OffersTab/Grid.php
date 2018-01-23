<?php

/**
 * Class Grid
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Block_Adminhtml_Promo_OffersTab_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
//			'renderer' => 'Mediotype_OffersTab_Block_Adminhtml_Promo_OffersTab_Grid_Renderer_Id',
//		));

		$this->addColumn('created_at', array(
			'header' => Mage::helper('mediotype_offerstab')->__('Created At'),
			'sortable' => true,
			'width' => '60',
			'index' => 'created_time',
			'type' => 'datetime'
		));

		$this->addColumn('updated_at', array(
			'header' => Mage::helper('mediotype_offerstab')->__('Updated At'),
			'sortable' => true,
			'width' => '60',
			'index' => 'update_time',
			'type' => 'datetime'
		));

		return parent::_prepareColumns();
	}
}
