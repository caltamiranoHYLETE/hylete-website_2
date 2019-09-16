<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Customer
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Customer model
 *
 * @category    Mage
 * @package     Mage_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Hylete_Customer_Model_Customer extends Mage_Customer_Model_Customer
{
	/**
	 * Send email with new account related information
	 *
	 * @param string $type
	 * @param string $backUrl
	 * @param string $storeId
	 * @throws Mage_Core_Exception
	 * @return Mage_Customer_Model_Customer
	 */
	public function sendNewAccountEmail($type = 'registered', $backUrl = '', $storeId = '0')
	{
		$types = array(
			'registered'   => self::XML_PATH_REGISTER_EMAIL_TEMPLATE, // welcome email, when confirmation is disabled
			'confirmed'    => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE, // welcome email, when confirmation is enabled
			'confirmation' => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE, // email with confirmation link
		);
		if (!isset($types[$type])) {
			Mage::throwException(Mage::helper('customer')->__('Wrong transactional account email type'));
		}

		if (!$storeId) {
			$storeId = $this->_getWebsiteStoreId($this->getSendemailStoreId());
		}

		//$this->_sendEmailTemplate($types[$type], self::XML_PATH_REGISTER_EMAIL_IDENTITY,
		//	array('customer' => $this, 'back_url' => $backUrl), $storeId);

		return $this;
	}
}
