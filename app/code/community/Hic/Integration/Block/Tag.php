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
* Integration Head Block
*
* @category Hic
* @package Integration
* @author HiConversion <support@hiconversion.com>
*/
class Hic_Integration_Block_Tag extends Mage_Core_Block_Template
{

    /**
     * add product and category ids to placeholder 
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $info = parent::getCacheKeyInfo();
        if (Mage::registry('current_product'))
        {
            $info['product_id'] = Mage::registry('current_product')->getId();
        }
        if (Mage::registry('current_category'))
        {
            $info['category_id'] = Mage::registry('current_category')->getId();
        }
        return $info;
    }

}