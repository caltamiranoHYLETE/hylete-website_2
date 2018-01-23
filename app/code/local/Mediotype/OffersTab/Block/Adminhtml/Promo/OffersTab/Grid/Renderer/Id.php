<?php

/**
 * Class Mediotype_OffersTab_Block_Adminhtml_Promo_OffersTab_Grid_Renderer_Id
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Block_Adminhtml_Promo_OffersTab_Grid_Renderer_Id
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	/**
	 * @param Varien_Object $row
	 * @return string
	 */
	public
	function render(Varien_Object $row)
	{
		$value = $row->getData($this->getColumn()->getIndex());
		$html = '<span>' . $value . '</span>';

		return $html;
	}
}
