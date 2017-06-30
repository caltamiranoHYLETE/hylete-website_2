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
class Icommerce_Matrixratesmodifier_Block_Adminhtml_Matrixratesmodifier_Edit extends Mage_Adminhtml_Block_Widget_Form_Container 
{
    public function __construct()
    {
        parent::__construct();
               
        $this->_objectId = 'id';
        $this->_blockGroup = 'matrixratesmodifier';
        $this->_controller = 'adminhtml_matrixratesmodifier';
 
        $this->_updateButton('save', 'label', Mage::helper('matrixratesmodifier')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('matrixratesmodifier')->__('Delete'));
    }
 
    public function getHeaderText()
    {
        if( Mage::registry('matrixratesmodifier_data') && Mage::registry('matrixratesmodifier_data')->getPk() ) 
            return Mage::helper('matrixratesmodifier')->__("Edit Rate: '%s'", $this->htmlEscape(Mage::registry('matrixratesmodifier_data')->getPk()));
        else 
            return Mage::helper('matrixratesmodifier')->__('Add Rate');
    }
}