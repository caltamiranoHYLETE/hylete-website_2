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
class Icommerce_Matrixratesmodifier_Block_Adminhtml_Matrixratesmodifier_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $helper = Mage::helper('matrixratesmodifier');

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $general = $form->addFieldset( 'general_form', array('legend' => $this->__('Shipping Destination')) );

       	$websitesTmp = Mage::app()->getWebsites();
       	$websites = array();
       	foreach($websitesTmp as $site)
       		$websites[$site->getId()] = $site->getName();

    	$general->addField('website_id', 'select', array(
          'label'     => Mage::helper('matrixratesmodifier')->__('Website'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'website_id',
          'onclick' => "",
          'onchange' => "",
          'values' => $websites,
          'disabled' => false,
          'readonly' => false,
        ));

		$countries = Mage::getSingleton('directory/country')->getResourceCollection()->toOptionArray();
		$countries[0] = $this->__('Worldwide');
        $general->addField('dest_country_id', 'select', array(
          'label'     => Mage::helper('matrixratesmodifier')->__('Country'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'dest_country_id',
          'onclick' => "",
          'onchange' => "",
          'values' => $countries,
          'disabled' => false,
          'readonly' => false,
          'after_element_html' => Mage::helper('matrixratesmodifier')->__("<p>You can find 'Worldwide' at the top of the list</p>"),
        ));

        $general->addField('dest_region_id', 'text', array(
            'label'     => Mage::helper('matrixratesmodifier')->__('Region'),
            'required'  => false,
            'class'		=> 'validate-number',
            'name'      => 'dest_region_id',
        ));

        $general->addField('dest_zip', 'text', array(
            'label'     => Mage::helper('matrixratesmodifier')->__('Postal Code From'),
            'required'  => false,
            'name'      => 'dest_zip',
            'after_element_html' => Mage::helper('matrixratesmodifier')->__("<p>Leave field empty if not used. Don't enter: 0</p>"),
        ));

        $general->addField('dest_zip_to', 'text', array(
            'label'     => Mage::helper('matrixratesmodifier')->__('Postal Code To'),
            'required'  => false,
            'name'      => 'dest_zip_to',
            'after_element_html' => Mage::helper('matrixratesmodifier')->__("<p>Leave field empty if not used. Don't enter: 0</p>"),
        ));

        if(Icommerce_Db::columnExists("shipping_matrixrate", "code"))
        {
            $general->addField('code', 'text', array(
                'label'     => Mage::helper('matrixratesmodifier')->__('Shipping Code'),
                'required'  => false,
                'name'      => 'code',
                'after_element_html' => Mage::helper('matrixratesmodifier')->__("<p>Only used internally if needed.</p>"),
            ));
        }

        $conditions = $form->addFieldset( 'conditions', array('legend' => $this->__('Shipping Conditions')) );

        $conditions->addField('condition_name', 'select', array(
          'label'     => Mage::helper('matrixratesmodifier')->__('Condition'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'condition_name',
          'onclick' => "",
          'onchange' => "",
          'values' => Mage::getModel('matrixrate_shipping/carrier_matrixrate')->getCode("condition_name"),
          'disabled' => false,
          'readonly' => false
        ));

        $conditions->addField('condition_from_value', 'text', array(
            'label'     => Mage::helper('matrixratesmodifier')->__('Condition From'),
            'required'  => true,
            'class'		=> 'validate-number',
            'name'      => 'condition_from_value',
        ));

        $conditions->addField('condition_to_value', 'text', array(
            'label'     => Mage::helper('matrixratesmodifier')->__('Condition To'),
            'required'  => true,
            'class'		=> 'validate-number',
            'name'      => 'condition_to_value',
        ));


        $details = $form->addFieldset( 'details', array('legend' => $this->__('Shipping Details')) );

        if ($helper->showCostField()) {
            $details->addField('cost', 'text', array(
                'label'     => Mage::helper('matrixratesmodifier')->__('Cost'),
                'class'		=> 'validate-number',
                'name'      => 'cost',
            ));
        }

        $details->addField('price', 'text', array(
            'label'     => Mage::helper('matrixratesmodifier')->__('Price'),
            'required'  => true,
            'class'		=> 'validate-number',
            'name'      => 'price',
        ));

        $details->addField('delivery_type', 'text', array(
            'label'     => Mage::helper('matrixratesmodifier')->__('Method Name'),
            'required'  => true,
            'class'		=> 'required-entry',
            'name'      => 'delivery_type',
            'after_element_html' => Mage::helper('matrixratesmodifier')->__("<p>The title of the shipping rate. Is displayed in the checkout page.</p>"),
        ));

        $details->addField('description', 'editor', array(
            'name'      => 'description',
            'label'     => Mage::helper('matrixratesmodifier')->__('Description'),
            'title'     => Mage::helper('matrixratesmodifier')->__('Description'),
            'wysiwyg'   => false,
            'after_element_html' => Mage::helper('matrixratesmodifier')->__("<p>Is displayed in the checkout page, depending on theme.</p>"),
        ));

        $details->addField('short_description', 'text', array(
                'name'      => 'short_description',
                'label'     => Mage::helper('matrixratesmodifier')->__('Short description'),
                'title'     => Mage::helper('matrixratesmodifier')->__('Short description'),
                'required'  => false,
        ));

        if ($helper->showLogoField()) {
            $details->addField('logo', 'image', array(
                'name'      => 'logo',
                'label'     => Mage::helper('matrixratesmodifier')->__('Logo'),
                'title'     => Mage::helper('matrixratesmodifier')->__('Logo'),
                'after_element_html' => Mage::helper('matrixratesmodifier')->__("<p>Is displayed in the checkout page (depending on theme).</p>"),
            ));
        }

        if ( Icommerce_Default_Helper_Data::isModuleActive('Icommerce_MatrixrateExtended') && Icommerce_Db::columnExists('shipping_matrixrate', 'freightcat')) {
        	$details->addField('freightcat', 'text', array(
            	'label'     => Mage::helper('matrixratesmodifier')->__('Freight Category'),
            	'required'  => false,
            	'name'      => 'freightcat',
        	));
        }

        Mage::dispatchEvent('matrixratemodifier_edit_form_prepare_customize',array('form' => $form));

        if ( Mage::getSingleton('adminhtml/session')->getMatrixratesmodifierData() ) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getMatrixratesmodifierData());
            Mage::getSingleton('adminhtml/session')->setMatrixratesmodifierData(null);
        } elseif ( Mage::registry('matrixratesmodifier_data') ) {
            $form->setValues(Mage::registry('matrixratesmodifier_data')->getData());
        }
        return parent::_prepareForm();
    }
}
