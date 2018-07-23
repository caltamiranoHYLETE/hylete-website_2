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
    class MD_Cybersource_Block_Adminhtml_Cards_Grid extends Mage_Adminhtml_Block_Widget_Grid
    {
        protected $_stores = array();
        protected $_helper = null;
        public function __construct(){
            parent::__construct();
            $this->_helper = Mage::helper('md_cybersource');
            $this->_stores = Mage::getModel('core/store')->getCollection()->toOptionHash();
            $this->setId('membershipPlansGrid');
            $this->setUseAjax(false);
            $this->setDefaultSort('card_id');
            $this->setDefaultDir('ASC');
            $this->setSaveParametersInSession(true);
        }

        protected function _prepareCollection() {
            $collection = Mage::getModel("md_cybersource/cards")
            ->getCollection();
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }

        public function _prepareColumns() {
            $this->addColumn('card_id', array(
                'header' => $this->_helper->__('Id'),
                'index' => 'card_id',
                'type'  => 'number',
                'width'=> '50px'
            ));
            $this->addColumn('firstname',array(
                'header'=>$this->_helper->__('Name'),
                'index'=>'firstname',
                'type'=>'text',
                'filter'=>false,
                'frame_callback' => array($this, 'displayFullName'),
                'width'=> '50px',
            ));
            $this->addColumn('email', array(
                'header' => $this->_helper->__('Email'),
                'index' => 'email',
            ));
            $this->addColumn('postcode', array(
                'header' => $this->_helper->__('Zip'),
                'index' => 'postcode',
            ));
            $this->addColumn('postcode', array(
                'header' => $this->_helper->__('Zip'),
                'index' => 'postcode',
            ));
            $this->addColumn('country_id', array(
                'header'    => $this->_helper->__('Country'),
                'width'     => '100',
                'type'      => 'country',
                'index'     => 'country_id',
            ));
            $this->addColumn('region', array(
                'header'    => $this->_helper->__('State/Province'),
                'width'     => '100',
                'index'     => 'region',
            ));
            return parent::_prepareColumns();
        }

        public function displayFullName($value, $row, $column, $isExport){
            return $value.' '.$row->getLastname();
        }

        public function getRowUrl($row){
            return $this->getUrl('adminhtml/mdcybersource_cards/edit',array('id'=>$row->getId()));
        }

        protected function _getStore(){
            $storeId = (int) $this->getRequest()->getParam('store', 0);
            return Mage::app()->getStore($storeId);
        }
    }

