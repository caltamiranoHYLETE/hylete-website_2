<?php
/**
 * HiConversion
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * [http://opensource.org/licenses/MIT]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category Hic
 * @package Hic_Integration
 * @Copyright © 2015 HiConversion, Inc. All rights reserved.
 * @license [http://opensource.org/licenses/MIT] MIT License
 */

/**
 * PayPal Model Config
 *
 * @category Hic
 * @package Integration
 * @author HiConversion <support@hiconversion.com>
 */
class Hic_Integration_Model_Config extends Mage_Paypal_Model_Config 
{
    /**
     * BN Code in configuration
     */
    const SETTINGS_BN_CODE   = 'integration/settings/bn_code';

    /**
     * Returns BN Code from Configuration
     *
     * @return string
     */
    public function getBNCode()
    {
        return Mage::getStoreConfig(self::SETTINGS_BN_CODE);
    }

    /**
     * BN code getter
     * override method
     *
     * @param string $countryCode ISO 3166-1
     */   
    public function getBuildNotationCode($countryCode = null)
    {
        $newBnCode = $this->getBNCode();
        if (empty($newBnCode)) {
            $newBnCode = parent::getBuildNotationCode($countryCode);
        }

        return $newBnCode;
    }
}