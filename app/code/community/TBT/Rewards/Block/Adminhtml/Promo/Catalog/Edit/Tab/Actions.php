<?php
/**
 * Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Sweet Tooth SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).

 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, Sweet Tooth is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by Sweet Tooth, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time Sweet Tooth spent 
 * during the support process.
 * Sweet Tooth does not guarantee compatibility with any other framework extension. 
 * Sweet Tooth is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * support@sweettoothrewards.com or call 1.855.699.9322, so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2014 Sweet Tooth Inc. (http://www.sweettoothrewards.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml Promo Catalog Edit Tab Actions
 *
 * @category   TBT
 * @package    TBT_Rewards
 * * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_Rewards_Block_Adminhtml_Promo_Catalog_Edit_Tab_Actions extends Mage_Adminhtml_Block_Promo_Catalog_Edit_Tab_Actions {
	
	protected $_currencyList;
	protected $_currencyModel;
	
	protected function _prepareForm() {
		$model = Mage::registry ( 'current_promo_catalog_rule' );
		parent::_prepareForm ();
		
		$form = $this->getForm ();
		
		$fieldset = $form->addFieldset ( 'points_action_fieldset', array ('legend' => Mage::helper ( 'rewards' )->__ ( 'Reward With Points' ) ) );
		
		$fieldset->addField ( 'points_action', 'select', array ('label' => Mage::helper ( 'salesrule' )->__ ( 'Action' ), 'name' => 'points_action', 'options' => Mage::getSingleton ( 'rewards/catalogrule_actions' )->getOptionArray () ) );
		
		$fieldset->addField ( 'points_currency_id', 'select', array ('label' => Mage::helper ( 'salesrule' )->__ ( 'Points Currency' ), 'title' => Mage::helper ( 'salesrule' )->__ ( 'Points Currency' ), 'name' => 'points_currency_id', 'options' => Mage::helper ( 'rewards/currency' )->getAvailCurrencies () ) );
		
		$fieldset->addField ( 'points_amount', 'text', array ('name' => 'points_amount', 'required' => false, 'class' => 'validate-not-negative-number', 'label' => Mage::helper ( 'salesrule' )->__ ( 'Points Amount (X)' ) ) );
		
		$fieldset->addField ( 'points_amount_step', 'text', array ('name' => 'points_amount_step', 'label' => Mage::helper ( 'salesrule' )->__ ( 'Monetary Step (Y)' ) ) );

		$fieldset->addField ( 'points_max_qty', 'text', array ('name' => 'points_max_qty', 'label' => Mage::helper ( 'salesrule' )->__ ( 'Maximum Total of Points To Transfer (0 for unlimited)' ) ) );
		
		$form->setValues ( $model->getData () );
		
		$this->setForm ( $form );
		
		return $this;
	}
	/**
	 * Returns a list of allowed currencies for the customer
	 *
	 * @return array
	 */
	protected function _getCurrencyList() {
		if (is_null ( $this->_currencyList )) {
			$this->_currencyList = $this->_getCurrencyModel ()->getConfigAllowCurrencies ();
		}
		return $this->_currencyList;
	}
	
	/**
	 * Returns the currency model
	 *
	 * @return Currency model
	 */
	protected function _getCurrencyModel() {
		if (is_null ( $this->_currencyModel ))
			$this->_currencyModel = Mage::getModel ( 'directory/currency' );
		
		return $this->_currencyModel;
	}

}