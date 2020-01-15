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

 

class Itoris_CmsDisplayRules_Block_Admin_Cms_Page_Tab_PageInformation extends Mage_Adminhtml_Block_Cms_Page_Edit_Tab_Main {

	protected $_origTemplateFile = null;
	const NO_CMS = 0;

	public function __construct() {
		parent::__construct();
		if ($this->getDataHelper()->isRegisteredAdmin()) {
			$this->_origTemplateFile = Mage::getDesign()->getTemplateFilename($this->getTemplate());
			$this->setTemplate('itoris/cmsdisplayrules/cms/page/tab/pageInformation.phtml');
			$this->prepareCmsForm();
		}
	}

	public function getOrigTemplateFile() {
		return $this->_origTemplateFile;
	}

	protected function prepareCmsForm() {
		$form = new Varien_Data_Form();
		$currentPage = Mage::registry('cms_page')->getId();
		$pageModel = Mage::getModel('itoris_cmsdisplayrules/page')->load($currentPage);

		$fieldset = $form->addFieldset('itoris_cms_display_rules', array(
			'legend' => $this->__('Page Display Rules'),
		));

		$userGroups = Mage::getResourceModel('customer/group_collection')->toOptionArray();
		array_unshift($userGroups, array(
			'label' => $this->__('All Groups'),
			'value' => 'all',
		));
		$groupIds = $pageModel->getGroupId();
		if (empty($groupIds)) {
			$groupIds[] = 'all';
		}
		$fieldset->addField('itoris_cms_display_rules_user_groups', 'multiselect', array(
			'name'     => 'itoris_cms_display_rules[groups]',
			'label'    => $this->__('Show Page to the following User Groups'),
			'title'    => $this->__('Show Page to the following User Groups'),
			'values'   => $userGroups,
			'required' => true,
			'value'    => $groupIds,
		));
		$fieldset->addField('itoris_cms_display_rules_starting', 'date', array(
			'name'		   => 'itoris_cms_display_rules[starting]',
			'label'        => $this->__('Starting on'),
			'title'        => $this->__('Starting on'),
			'image'        => $this->getSkinUrl('images/grid-cal.gif'),
			'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
			'format'       => Varien_Date::DATE_INTERNAL_FORMAT,
			'value'        => $pageModel->getStartDate(),
		));
		$fieldset->addField('itoris_cms_display_rules_ending', 'date', array(
			'name'		   => 'itoris_cms_display_rules[ending]',
			'label'        => $this->__('Ending on'),
			'title'        => $this->__('Ending on'),
			'image'        => $this->getSkinUrl('images/grid-cal.gif'),
			'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
			'format'       => Varien_Date::DATE_INTERNAL_FORMAT,
			'value'        => $pageModel->getFinishDate(),
		));
		$cmsPageCollection = Mage::getModel('cms/page')->getCollection();
		$cmsPageCollection->getSelect()->order('identifier');
		$cmsPages = array();
		foreach ($cmsPageCollection as $model) {
			if ($model->getId() != $currentPage) {
				$cmsPages[] = array(
					'label' => $model->getIdentifier() . ': ' . $model->getTitle(),
					'value' => $model->getId(),
				);
			}
		}
		array_unshift($cmsPages, array(
			'label' => $this->__('No CMS, display 404 - Page Not Found'),
			'value' => self::NO_CMS,
		));
		$fieldset->addField('itoris_cms_display_rules_another_cms', 'select', array(
			'name'		  => 'itoris_cms_display_rules[another_cms]',
			'label'       => $this->__('Otherwise, display another CMS instead'),
			'title'       => $this->__('Otherwise, display another CMS instead'),
			'values' => $cmsPages,
			'value'        => $pageModel->getAnotherCms(),
		));
		$elementRenderer = new Itoris_CmsDisplayRules_Block_Admin_Form_Renderer_Element();

		$fieldset->addField('itoris_cms_display_rules_note', 'text', array(
			'label'       => '',
			'itoris_note'     => $this->__("Note: The CMS redirect may work by chain if rule doesn't match the criteria, i.e. CMS1 -> CMS2 -> CMS3 etc. If the last page's rule in the chain fails the 404 Page Not Found will be shown."),
		))->setRenderer($elementRenderer);

		$this->setChild('itoris_cms_display_rules_page_form', $form);
	}


	/**
	 * @return Itoris_CmsDisplayRules_Helper_Data
	 */
	public function getDataHelper() {
		return Mage::helper('itoris_cmsdisplayrules');
	}
}
?>