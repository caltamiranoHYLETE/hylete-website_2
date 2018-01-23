<?php

/**
 * Class Grid
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	/**
	 * Grid constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->setId('mediotype_orderstab_grid');
		$this->setDefaultSort('increment_id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	/**
	 * @return $this
	 */
	protected function _prepareCollection()
	{
		$collection = [];

		$this->setCollection($collection);

		parent::_prepareCollection();

		return $this;
	}

	/**
	 * @return mixed
	 */
	protected function _prepareColumns()
	{
		return parent::_prepareColumns();
	}

	/**
	 * @return mixed
	 */
	public function getGridUrl()
	{
		return $this->getUrl('*/*/grid', array('_current' => true));
	}
}
