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

 

class Itoris_CmsDisplayRules_Helper_Data extends Mage_Core_Helper_Abstract {

	protected $alias = 'cms_display';

	public function isAdminRegistered() {
		try {
			return Itoris_Installer_Client::isAdminRegistered($this->getAlias());
		} catch(Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			return false;
		}
	}

	public function isRegisteredAutonomous($website = null) {
		return Itoris_Installer_Client::isRegisteredAutonomous($this->getAlias(), $website);
	}

	public function registerCurrentStoreHost($sn) {
		return Itoris_Installer_Client::registerCurrentStoreHost($this->getAlias(), $sn);
	}

	public function isRegistered($website) {
		return Itoris_Installer_Client::isRegistered($this->getAlias(), $website);
	}

	public function getAlias() {
		return $this->alias;
	}

	/**
	 * Get store id by parameter from the request
	 *
	 * @return int
	 */
	public function getStoreId() {
		if (Mage::app()->getRequest()->getParam('store')) {
			return Mage::app()->getStore(Mage::app()->getRequest()->getParam('store'))->getId();
		}
		return 0;
	}

	/**
	 * Get website id by parameter from the request
	 *
	 * @return int
	 */
	public function getWebsiteId() {
		if (Mage::app()->getRequest()->getParam('website')) {
			return Mage::app()->getWebsite(Mage::app()->getRequest()->getParam('website'))->getId();
		}
		return 0;
	}

	/**
	 * Get settings
	 *
	 * @return Itoris_CmsDisplayRules_Model_Settings
	 */
	public function getSettings($backend = false) {
		/** @var $settingsModel Itoris_CmsDisplayRules_Model_Settings */
		$settingsModel = Mage::getSingleton('itoris_cmsdisplayrules/settings');
		$productId = 0;
		if (($product = Mage::registry('current_product')) && $product instanceof Mage_Catalog_Model_Product) {
			$productId = $product->getId();
		}
		if ($backend || !Mage::app()->getWebsite()->getId()) {
			if (!is_array(Mage::app()->getRequest()->getParam('website')) && !is_array(Mage::app()->getRequest()->getParam('store'))) {
				$settingsModel->load($this->getWebsiteId(), $this->getStoreId(), $productId);
			}
		} else {
			$settingsModel->load(Mage::app()->getWebsite()->getId(), Mage::app()->getStore()->getId(), $productId);
		}

		return $settingsModel;
	}

	public function getScopeData() {
		if ($this->getStoreId()) {
			return array(
				'scope'    => 'store',
				'scope_id' => $this->getStoreId(),
			);
		} elseif ($this->getWebsiteId()) {
			return array(
				'scope'    => 'website',
				'scope_id' => $this->getWebsiteId(),
			);
		} else {
			return array(
				'scope'    => 'default',
				'scope_id' => 0
			);
		}
	}

	public function customerGroup($selectedGroupId) {
		$customer = Mage::getSingleton('customer/session');
		$customerId = (string)$customer->getCustomerGroupId();
        $allowedGroups = array();
		if (is_array($selectedGroupId)) {
			foreach ($selectedGroupId as $key => $value) {
                $groupId = isset($value['group_id']) ? $value['group_id'] : $value;
                if ($groupId !== null) {
                    $allowedGroups[] = $groupId;
                }
			}

		} else {
            $allowedGroups = explode(',', $selectedGroupId);
		}
        if (is_null($selectedGroupId) || empty($allowedGroups)) {
            return null;
        } else {
            if (in_array($customerId, $allowedGroups)) {
                return true;
            } else {
                return false;
            }
        }
	}

	public function isVisibleByRestrictionDate($startDate, $endDate) {
		$currentDate = Mage::app()->getLocale()->date();
		$start = $startDate == ''? null : $this->getDate($startDate);
		$end = $endDate == '' ? null : $this->getDate($endDate);
		if (!empty($startDate) && !empty($endDate)) {
			if ($start->compareDate($currentDate) !== 1 && $end->compareDate($currentDate) !== -1) {
				return false;
			} else {
				return true;
			}
		} elseif (!empty($startDate) && empty($endDate)) {
			if ($start->compareDate($currentDate) !== 1) {
				return false;
			} else {
				return true;
			}
		} elseif (empty($startDate) && !empty($endDate))  {
			if ($end->compareDate($currentDate) !== -1) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	public function getDate($dateOrigValue) {
		$dateOrig = new Zend_Date($dateOrigValue, Zend_Date::ISO_8601);
		$dateWithTimezone = new Zend_Date($dateOrig, Zend_Date::ISO_8601);
		$currentTimezone = Mage::app()->getLocale()->date()->getTimezone();
		if ($dateWithTimezone->getTimezone() != $currentTimezone) {
			$dateWithTimezone->setTimezone(Mage::app()->getLocale()->date()->getTimezone());
			$dateWithTimezone->setYear($dateOrig->getYear());
			$dateWithTimezone->setMonth($dateOrig->getMonth());
			$dateWithTimezone->setDay($dateOrig->getDay());
			$dateWithTimezone->setHour($dateOrig->getHour());
		}

		return $dateWithTimezone;
	}

	public function isRegisteredFrontend() {
		return !Mage::app()->getStore()->isAdmin()
			&& $this->getSettings()->getEnabled()
			&& $this->isRegisteredAutonomous();
	}

	public function isRegisteredAdmin() {
		return Mage::app()->getStore()->isAdmin()
			&& $this->getSettings()->getEnabled()
			&& $this->isAdminRegistered();
	}
}

?>