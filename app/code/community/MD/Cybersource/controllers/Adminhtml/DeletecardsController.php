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
class MD_Cybersource_Adminhtml_DeletecardsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        try{
            $cards = Mage::getModel('md_cybersource/cards')
                        ->getCollection();
            if(!empty($cards->getData())){
                foreach($cards as $card){
                    $card->delete();
                }
                Mage::getSingleton("core/session")->addSuccess("Cards has been deleted.");
            }
            else{
                Mage::getSingleton("core/session")->addError("Customers having Cards not found."); 
            }
            $this->_redirectReferer();     
        }catch(Exception $e){
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->_redirectReferer();
        }
    }
}