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
 * Manage Promo Quote Edit Tab Actions
 *
 * @category   TBT
 * @package    TBT_Rewards
 * * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_Rewards_Block_Manage_Promo_Quote_Edit_Tab_Actions extends TBT_Rewards_Block_Manage_Promo_Quote_Edit_Tab_Abstract {
	protected $_currencyList;
	protected $_currencyModel;
	
	protected function _prepareForm() {
		$model = $this->_getRule ();
		
		$form = new Varien_Data_Form ();
		
		$form->setHtmlIdPrefix ( 'rule_' );
		
		// Dispatch event 
		Mage::dispatchEvent( 'rewards_block_manage_actions_setup_cartrule_before', array(
            'actions_block' => $this,
		    'rule'	   		=> $model,
		    'form'			=> $form
        ) );
		
		
		if (! $this->_isRedemptionType ()) {
			$this->_getPointsActionFieldset ( $form );
		}
		
		// Don't apply any discounts to shipping until we assure this feature.
		// TODO allow shipping discounts
		$model->setApplyToShipping ( false );
		$model->setSimpleFreeShipping ( false );
		
		$this->_getPriceActionFieldset ( $form, $model );
		
		$this->_getApplyToActionFieldset ( $form, $model );
		
		// Dispatch event 
		Mage::dispatchEvent( 'rewards_block_manage_actions_setup_cartrule_after', array(
            'actions_block' => $this,
		    'rule'	        => $model,
		    'form'			=> $form
        ) );
		
		$form->setValues ( $model->getData () );
		
		$this->setForm ( $form );
		
		return parent::_prepareForm ();
	}

}
