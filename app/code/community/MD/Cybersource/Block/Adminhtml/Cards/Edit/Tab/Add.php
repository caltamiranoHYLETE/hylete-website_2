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
class MD_Cybersource_Block_Adminhtml_Cards_Edit_Tab_Add extends Mage_Adminhtml_Block_Template
{
    public function __construct() {
        parent::__construct();
        $this->setTemplate("md/cybersource/cards/add.phtml");
    }

    public function getCountryHtmlSelect($countryId = null){
        if (is_null($countryId)) {
            $countryId = Mage::getStoreConfig('general/country/default');
        }
        $select = $this->getLayout()->createBlock('core/html_select')
        ->setName('md_cybersource[address_info][country_id]')
        ->setId('md_cybersource_country_id')
        ->setTitle(Mage::helper('md_cybersource')->__('Country'))
        ->setClass('')
        ->setValue($countryId)
        ->setOptions($this->getCountryOptions());

        return $select->getHtml();
    }

    public function getCountryOptions(){
        $options    = false;
        $useCache   = Mage::app()->useCache('config');
        if ($useCache) {
            $cacheId    = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
            $cacheTags  = array('config');
            if ($optionsCache = Mage::app()->loadCache($cacheId)) {
                $options = unserialize($optionsCache);
            }
        }

        if ($options == false) {
            $options = $this->getCountryCollection()->toOptionArray();
            if ($useCache) {
                Mage::app()->saveCache(serialize($options), $cacheId, $cacheTags);
            }
        }
        return $options;
    }

    public function getCountryCollection(){
        if (!$this->_countryCollection) {
            $this->_countryCollection = Mage::getSingleton('directory/country')->getResourceCollection()
            ->loadByStore();
        }
        return $this->_countryCollection;
    }

    protected function _getConfig(){
        return Mage::getSingleton('payment/config');
    }

    public function getCcAvailableTypes(){
        $types = Mage::getModel('md_cybersource/cardconfig')->getCcTypesCybersource();
        $availableTypes = explode(',',Mage::getModel('md_cybersource/config')->getCcTypes());
        if ($availableTypes) {
            foreach ($types as $code=>$name) {
                if (!in_array($code, $availableTypes)) {
                    unset($types[$code]);
                }
            }
        }

        return $types;
    }

    public function getMonths(){
        $raw_data = Mage::app()->getLocale()->getTranslationList('month');
        $formatted_data = array('' => 'Month');    
        foreach ($raw_data as $key => $value) {
            $monthNum = ($key < 10) ? '0'.$key : $key;
            $formatted_data[$monthNum] = $monthNum . ' - ' . $value;
        }
        return $formatted_data;
    }

    /**
    * Retrieve credit card expire months
    *
    * @return array
    */
    public function getCcMonths(){
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] =  $this->__('Month');
            $months = $this->getMonths();
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    /**
    * Retrieve credit card expire years
    *
    * @return array
    */
    public function getCcYears(){
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = $this->_getConfig()->getYears();
            $years = array(0=>$this->__('Year'))+$years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }

    public function hasVerification(){
        return Mage::getModel('md_cybersource/config')->isCardVerificationEnabled();
    }
}

