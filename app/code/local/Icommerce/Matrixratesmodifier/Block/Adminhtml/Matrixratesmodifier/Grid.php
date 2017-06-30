<?php
/**
 * Copyright (c) 2009-2011 Icommerce Nordic AB
 *
 * Icommerce reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Icommerce, except as provided by licence. A licence
 * under Icommerce's rights in the Program may be available directly from
 * Icommerce.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Icommerce
 * @package     Icommerce_Matrixratesmodifier
 * @copyright   Copyright (c) 2009-2011 Icommerce Nordic AB
 */
class Icommerce_Matrixratesmodifier_Block_Adminhtml_Matrixratesmodifier_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('matrixratesmodifier_grid');
        // This is the primary key of the database
        $this->setDefaultSort('pk');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('matrixratesmodifier/matrixratesmodifier')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('pk', array(
            'header'    => Mage::helper('matrixratesmodifier')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'pk',
            'type'  => 'number',
        ));

        $this->addColumn('website_id', array(
            'header'    => Mage::helper('matrixratesmodifier')->__('Website'),
            'align'     =>'left',
            'width'     => '100px',
            'index'     => 'website_id',
            'type'      => 'options',
            'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(false),
        ));


        $countries = Mage::getModel('matrixratesmodifier/matrixratesmodifier')->getCountries();
        $this->addColumn('dest_country_id', array(
            'header'    => Mage::helper('matrixratesmodifier')->__('Country'),
            'align'     =>'left',
            'width'     => '130px',
            'index'     => 'dest_country_id',
            'type'      => 'options',
            'options'   => $countries,
        ));

        $this->addColumn('condition_name', array(
            'header'    => Mage::helper('matrixratesmodifier')->__('Condition'),
            'align'     =>'left',
            'index'     => 'condition_name',
            'type'		=> 'options',
            'width'     => '160px',
            'options'	=> Mage::getModel('matrixrate_shipping/carrier_matrixrate')->getCode("condition_name"),
        ));

        $this->addColumn('description', array(
            'header'    => Mage::helper('matrixratesmodifier')->__('Description'),
            'align'     =>'left',
            'width'     => '270px',
            'index'     => 'description',
            'type'      => 'text',
        ));

        $this->addColumn('condition_from_value', array(
            'header'    => Mage::helper('matrixratesmodifier')->__('Condition From'),
            'align'     =>'left',
            'index'     => 'condition_from_value',
            'type'  => 'number',
        ));

        $this->addColumn('condition_to_value', array(
            'header'    => Mage::helper('matrixratesmodifier')->__('Condition To'),
            'align'     =>'left',
            'index'     => 'condition_to_value',
            'type'  => 'number',
        ));

        if (Mage::helper('matrixratesmodifier')->showCostField()) {
            $this->addColumn('cost', array(
                'header'    => Mage::helper('matrixratesmodifier')->__('Cost'),
                'align'     =>'left',
                'index'     => 'cost',
                'type'  => 'number',
            ));
        }

        $this->addColumn('price', array(
            'header'    => Mage::helper('matrixratesmodifier')->__('Price'),
            'align'     =>'left',
            'index'     => 'price',
            'type'  => 'number',
        ));

        if (Icommerce_Default_Helper_Data::isModuleActive('Icommerce_MatrixrateExtended') && Icommerce_Db::columnExists('shipping_matrixrate', 'freightcat')) {
            $this->addColumn('freightcat', array(
                'header'    => Mage::helper('matrixratesmodifier')->__('Freight Category'),
                'align'     =>'center',
                'index'     => 'freightcat',
            ));
        }

        $this->addColumn('delivery_type', array(
            'header'    => Mage::helper('matrixratesmodifier')->__('Method Name'),
            'align'     =>'left',
            'index'     => 'delivery_type',
        ));

        // To allow grid modifications for site specific customizations
        Mage::dispatchEvent('matrixratemodifier_prepare_grid', array('grid' => $this));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}