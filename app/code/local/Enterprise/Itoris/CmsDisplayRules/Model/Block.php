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

 

class Itoris_CmsDisplayRules_Model_Block extends Mage_Core_Model_Abstract {

	protected function _construct() {
		$this->_init('itoris_cmsdisplayrules/block');
	}

	protected function _afterLoad() {
		if ($this->getId()) {
			$resource = Mage::getSingleton('core/resource');
			$connection = $resource->getConnection('read');
			$groupTable = $resource->getTableName('itoris_cms_display_rules_block_group');
			$selectedGroupId = $connection->fetchAll("select group_id from {$groupTable} where block_id={$this->getId()}");
			$groupIds = array();
			foreach ($selectedGroupId as $groupId) {
				$groupIds[] = $groupId['group_id'];
			}
			$this->setGroupId($groupIds);
		}
		return parent::_afterLoad();
	}
}
?>