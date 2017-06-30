<?php
/**
 * Copyright (c) 2009-2012 Icommerce Nordic AB
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
 * @category    Vaimo
 * @package     Style.php
 * @copyright   Copyright (c) 2009-2012 Icommerce Nordic AB
 * @author      Kaarel Taniloo
 */

class Icommerce_Style_Block_Adminhtml_Style extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {

    $this->_blockGroup = 'style';
    $this->_headerText = Mage::helper('product')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('product')->__('Add Item');
    parent::__construct();
  }
}