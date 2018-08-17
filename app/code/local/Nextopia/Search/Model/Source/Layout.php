<?php
/**
 * Date: 10/7/2014
 * Time: 10:54 AM
 */

class Nextopia_Search_Model_Source_Layout {
	public function toOptionArray()
	{
		return array(
			/*array('value'=> 'page/empty.phtml', 'label'=>Mage::helper('nsearch')->__('Empty')),*/
			array('value'=> 'page/1column.phtml', 'label'=>Mage::helper('nsearch')->__('1 column')),
			array('value'=> 'page/2columns-left.phtml', 'label'=>Mage::helper('nsearch')->__('2 column with Left bar')),
			array('value'=> 'page/2columns-right.phtml', 'label'=>Mage::helper('nsearch')->__('2 column with right bar')),
			array('value'=> 'page/3columns.phtml', 'label'=>Mage::helper('nsearch')->__('3 column')),
		);
	}

} 