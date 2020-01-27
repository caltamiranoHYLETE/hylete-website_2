<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_CMSDISPLAYRULES
 * @copyright  Copyright (c) 2013 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */



class Itoris_CmsDisplayRules_Model_Cms_Block_Observer {

	public function editForm($obj) {
		$block = $obj->getBlock();
		if ($block instanceof Mage_Adminhtml_Block_Cms_Block_Edit_Form) {
			if ($this->getDataHelper()->isRegisteredAdmin()) {
				$form = $block->getForm();
				if (Mage::registry('cms_block')) {
					$currentBlock = Mage::registry('cms_block')->getId();
				} else {
					return;
				}
				$blockModel = Mage::getModel('itoris_cmsdisplayrules/block')->load($currentBlock);

				$fieldset = $form->addFieldset('itoris_cms_display_rules', array(
					'legend' => $this->getDataHelper()->__('Static Block Display Rules'),
				));

				$userGroups = Mage::getResourceModel('customer/group_collection')->toOptionArray();
				array_unshift($userGroups, array(
					'label' => $this->getDataHelper()->__('All Groups'),
					'value' => 'all',
				));
				$groupIds = $blockModel->getGroupId();
				if (empty($groupIds)) {
					$groupIds[] = 'all';
				}
				$fieldset->addField('itoris_cms_display_rules_user_groups', 'multiselect', array(
					'name'     => 'itoris_cms_display_rules[groups]',
					'label'    => $this->getDataHelper()->__('Show Static Block to the following User Groups'),
					'title'    => $this->getDataHelper()->__('Show Static Block to the following User Groups'),
					'values'   => $userGroups,
					'required' => true,
					'value'    => $groupIds,
				));
				$fieldset->addField('itoris_cms_display_rules_starting', 'date', array(
					'name'		   => 'itoris_cms_display_rules[starting]',
					'label'        => $this->getDataHelper()->__('Starting on'),
					'title'        => $this->getDataHelper()->__('Starting on'),
					'image'        => $block->getSkinUrl('images/grid-cal.gif'),
					'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
					'format'       => Varien_Date::DATE_INTERNAL_FORMAT,
					'value'        => $blockModel->getStartDate(),
				));
				$fieldset->addField('itoris_cms_display_rules_ending', 'date', array(
					'name'		   => 'itoris_cms_display_rules[ending]',
					'label'        => $this->getDataHelper()->__('Ending on'),
					'title'        => $this->getDataHelper()->__('Ending on'),
					'image'        => $block->getSkinUrl('images/grid-cal.gif'),
					'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
					'format'       => Varien_Date::DATE_INTERNAL_FORMAT,
					'value'        => $blockModel->getFinishDate(),
				));
				$cmsBlockCollection = Mage::getModel('cms/block')->getCollection();
				$cmsBlockCollection->getSelect()->order('identifier');
				$cmsBlocks = array();
				foreach ($cmsBlockCollection as $model) {
					if ($model->getId() != $currentBlock) {
						$cmsBlocks[] = array(
							'label' => $model->getIdentifier() . ': ' . $model->getTitle(),
							'value' => $model->getId(),
						);
					}
				}
				array_unshift($cmsBlocks, array(
					'label' => $this->getDataHelper()->__('No block, show nothing'),
					'value' => 0,
				));
				$fieldset->addField('itoris_cms_display_rules_another_cms', 'select', array(
					'name'		  => 'itoris_cms_display_rules[another_cms]',
					'label'       => $this->getDataHelper()->__('Otherwise, display another Static Block instead'),
					'title'       => $this->getDataHelper()->__('Otherwise, display another Static Block instead'),
					'values' => $cmsBlocks,
					'value'        => $blockModel->getAnotherCms(),
				));
				$elementRenderer = new Itoris_CmsDisplayRules_Block_Admin_Form_Renderer_Element();

				$fieldset->addField('itoris_cms_display_rules_note', 'text', array(
					'label'       => '',
					'itoris_note'     => $this->getDataHelper()->__("Note: The Static Block redirect may work by chain if rule doesn't match the criteria, i.e. Block1 -> Block2 -> Block3 etc. If the last block's rule in the chain fails nothing will be shown."),
				))->setRenderer($elementRenderer);
			}
		}
	}

	/**
	 * @return Itoris_CmsDisplayRules_Helper_Data
	 */
	public function getDataHelper() {
		return Mage::helper('itoris_cmsdisplayrules');
	}
}
?>