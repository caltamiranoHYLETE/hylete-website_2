<?php
/**
* Magedelight
* Copyright (C) 2015 Magedelight <info@magedelight.com>
*
* NOTICE OF LICENSE
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
*
* @category MD
* @package MD_Cybersource
* @copyright Copyright (c) 2015 Mage Delight (http://www.magedelight.com/)
* @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
* @author Magedelight <info@magedelight.com>
*/
class MD_Cybersource_Model_Cardconfig extends Mage_Payment_Model_Config    
{  
    public function getCcTypesCybersource($includePaypal = false)
    {
        $types = array();
        foreach (Mage::getConfig()->getNode('global/payment/cc_cybersource/types')->asArray() as $data) {
            if($includePaypal === false && $data['code'] == 'PAYPAL'){
                continue;
            }
            $types[$data['code']] = $data['name'];
        }

        if(is_array($types) and !empty($types)) {
            uasort($types, array($this, "cctypesSort"));
        }

        return $types;
    }

    public function cctypesSort($a, $b) {
        return strcmp($a, $b);
    }

    public function alterY(&$item, $key) {
        $item = $key;
    }

    public function getYearsStart() {
        $first = date('Y');
        $_y = range($first-5, $first);
        $_y = array_flip($_y);

        array_walk($_y, array($this, 'alterY'));

        return $_y;
    }
}
?>
