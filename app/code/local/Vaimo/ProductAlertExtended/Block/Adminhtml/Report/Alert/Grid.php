<?php
/**
 * Copyright (c) 2009-2013 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
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
 * @package     Vaimo_ProductAlertExtended
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Giorgos Tsioutsiouliklis <giorgos@vaimo.com>
 */
class Vaimo_ProductAlertExtended_Block_Adminhtml_Report_Alert_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    
    public function __construct() {
        parent::_construct ();
        $this->setId ( 'alertsGrid' );
        $this->setDefaultSort ( 'alert_id' );
        $this->setSaveParametersInSession ( true );
        $this->setUseAjax ( true );
        $this->setVarNameFilter ( 'alert_filter' );
        $this->setTemplate ( 'widget/grid.phtml' );
    }
    
    protected function _prepareColumns() {
        $this->addColumn ( 'alert_id', array (
                'header' => $this->__ ( 'Alert ID' ),
                'sortable' => true,
                'width' => '40px',
                'index' => 'alert_id' 
        ) );
        $this->addColumn ( 'customer_id', array (
                'header' => $this->__ ( 'Customer ID' ),
                'sortable' => true,
                'width' => '40px',
                'index' => 'customer_id' 
        ) );
        $this->addColumn ( 'product_id', array (
                'header' => $this->__ ( 'Product ID' ),
                'sortable' => true,
                'width' => '60px',
                'index' => 'product_id' 
        ) );
        $this->addColumn ( 'website_id', array (
                'header' => $this->__ ( 'Website ID' ),
                'sortable' => true,
                'width' => '40px',
                'index' => 'website_id' 
        ) );
        $this->addColumn ( 'email', array (
                'header' => $this->__ ( 'E-mail' ),
                'sortable' => true,
                'width' => '60px',
                'index' => 'email' 
        ) );
        $this->addColumn ( 'add_date', array (
                'header' => $this->__ ( 'Creation Date' ),
                'sortable' => true,
                'width' => '60px',
                'index' => 'add_date' 
        ) );
        $this->addColumn ( 'send_date', array (
                'header' => $this->__ ( 'Send Date' ),
                'sortable' => true,
                'width' => '60px',
                'index' => 'send_date' 
        ) );
        $this->addColumn ( 'send_count', array (
                'header' => $this->__ ( 'Times Send' ),
                'sortable' => true,
                'width' => '40px',
                'index' => 'send_count' 
        ) );
        $this->addColumn ( 'status', array (
                'header' => $this->__ ( 'Status' ),
                'sortable' => true,
                'width' => '40px',
                'index' => 'status' 
        ) );

        Mage::dispatchEvent('productalertextended_prepare_columns', array('grid' => $this));
    }
    
    protected function _prepareCollection() {
        $collection = Mage::getModel ( 'productalertextended/stock' )->getCollection ()->addFieldToSelect ( '*' );
        $this->setCollection ( $collection );
        
        parent::_prepareCollection ();

        Mage::dispatchEvent('productalertextended_prepare_collection', array('collection' => $collection));
        // $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }
}