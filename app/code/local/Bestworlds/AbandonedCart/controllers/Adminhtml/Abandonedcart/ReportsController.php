<?php
/**
 * Best Worlds
 * http://www.bestworlds.com
 * 888-751-5348
 *
 * Need help? contact us:
 *  http://www.bestworlds.com/contact-us
 *
 * Want to customize or need help with your store?
 *  Phone: 888-751-5348
 *  Email: info@bestworlds.com
 *
 * @category    Bestworlds
 * @package     Bestworlds_AbandonedCart
 * @copyright   Copyright (c) 2018 Best Worlds
 * @license     http://www.bestworlds.com/software_product_license.html
 */

/**
 * Report controller
 *
 * @category   Bestworlds
 * @package    Bestworlds_AbandonedCart
 * @author     Best Worlds Team <info@bestworlds.com>
 */
class Bestworlds_AbandonedCart_Adminhtml_Abandonedcart_ReportsController extends Mage_Adminhtml_Controller_Action
{
    public function _initAction()
    {
        $act = $this->getRequest()->getActionName();
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('abandonedcart')->__('Reports'), Mage::helper('abandonedcart')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('abandonedcart')->__('Shopping Cart'), Mage::helper('abandonedcart')->__('BestWorlds Abandoned Cart Reports'));
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/promo/abandonedcart/abandonedcart_reports');
    }

    public function indexAction(){

        $this->_title($this->__('Reports'))
            ->_title($this->__('Shopping Cart'))
            ->_title($this->__('BestWorlds Abandoned Cart Reports'));

        $this->_initAction()
            ->_setActiveMenu('report/shopcart/abandonedcart_reports')
            ->_addBreadcrumb(Mage::helper('abandonedcart')->__('BestWorlds Abandoned Cart Reports'), Mage::helper('abandonedcart')->__('BestWorlds Abandoned Cart Reports'))
            ->renderLayout();
    }

    protected function _ajaxResponse($result = array())
    {
        $this->getResponse()->setBody(Zend_Json::encode($result));
        return;
    }

    public function filterDateAction()
    {
        $response=[];

        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('startDate') && $this->getRequest()->getPost('endDate')) {
            $response['filter']['startDate']    = $this->getRequest()->getPost('startDate');
            $response['filter']['endDate']      = $this->getRequest()->getPost('endDate');
            $response = $this->_getReport($response);
            $response = $this->_getPieData($response);
        }else{
            $response= ['error' => 'Please try again later'];
        }
        $this->_ajaxResponse($response);
    }

    private function _getPercentage($val1, $val2, $precision)
    {

        return ($val1 && $val2)? round(($val1/$val2)*100, $precision) : '0';
    }

    private function _getPieData($response)
    {
        $report         =[];
        $timeDiff       = Bestworlds_AbandonedCart_Block_Adminhtml_Reports::CLOSETIME;
        $mdl            = Mage::getModel('core/date');
        $colors         = array(array(  'color'     => '#F7464A',
            'highlight' => '#FF5A5E'),
            array(  'color'     => '#46BFBD',
                'highlight' => '#5AD3D1'),
            array(  'color'     => '#FDB45C',
                'highlight' => '#FFC870'),
            array(  'color'     => '#949FB1',
                'highlight' => '#A8B3C5'),
            array(  'color'     => '#4D5360',
                'highlight' => '#616774'),
            array(  'color'     => '#97BBCD',
                'highlight' => '#E1E8EC')
        );
        $captureTypes   = array(    Bestworlds_AbandonedCart_Model_Capturetypes::ADD2CARTPROMPT,
            Bestworlds_AbandonedCart_Model_Capturetypes::EMAIL_MARKETING,
            Bestworlds_AbandonedCart_Model_Capturetypes::DURING_CHECKOUT,
            Bestworlds_AbandonedCart_Model_Capturetypes::LOGGED_IN
        );

        $coreResource   = Mage::getSingleton('core/resource');
        $readConnection = $coreResource->getConnection('core_read');


        $fromDate = date('Y-m-d H:i:s', $mdl->gmtTimestamp(strtotime(str_replace('-', '/', $response['filter']['startDate']. ' 00:00:00'))));
        $toDate = date('Y-m-d H:i:s', $mdl->gmtTimestamp(strtotime(str_replace('-', '/', $response['filter']['endDate']. ' 23:59:59'))));

        $collection= Mage::getModel('sales/quote')->getCollection()
            ->addFieldToFilter('email_captured_from', array('notnull' => true))
            ->addFieldToFilter('created_at', array('gteq' => $fromDate))
            ->addFieldToFilter('created_at', array('lteq' => $toDate))
            ->addFieldToFilter('items_count', array('gt' => 0))
            ->addFieldToFilter('is_active', array('eq' => 1));
        $collection->getSelect()->where("TIMESTAMPDIFF(SECOND, updated_at, UTC_TIMESTAMP()) > ".$timeDiff);


        $reachable = $collection->getSize();

        $count      = 0;
        foreach ($captureTypes as $captureType) {

            $collection= Mage::getModel('sales/quote')->getCollection()
                ->addFieldToFilter('email_captured_from', array('eq' => $captureType))
                ->addFieldToFilter('created_at', array('gteq' => $fromDate))
                ->addFieldToFilter('created_at', array('lteq' => $toDate))
                ->addFieldToFilter('items_count', array('gt' => 0))
                ->addFieldToFilter('is_active', array('eq' => 1));
            $collection->getSelect()->where("TIMESTAMPDIFF(SECOND, updated_at, UTC_TIMESTAMP()) > ".$timeDiff);

            $report[$captureType]['total']          = $collection->getSize();
            $report[$captureType]['percentage']     = $this->_getPercentage($report[$captureType]['total'], $reachable, 2);
            $report[$captureType]['color']          = $colors[$count]['color'];
            $report[$captureType]['highlight']      = $colors[$count]['highlight'];

            $count++;
        }
        $response['pie']= $report;
        return $response;
    }

    private function _getReport($response)
    {
        $report = [];
        $timeDiff = Bestworlds_AbandonedCart_Block_Adminhtml_Reports::CLOSETIME;
        $quote = Mage::getResourceModel('sales/quote');
        $order = Mage::getResourceModel('sales/order');

        $mdl          = Mage::getModel('core/date');
        $quoteAdapter = $quote->getReadConnection();
        $orderAdapter = $order->getReadConnection();

        $allStores = Mage::app()->getStores();
        $_store_ids= array();
        foreach ($allStores as $_eachStoreId => $val) {
            $_store_ids[]= Mage::app()->getStore($_eachStoreId)->getId();
        }

        //GET TOTAL ORDERS FROM ORDER TABLE
        $totalOrders = $orderAdapter->select()
            ->from($order->getMainTable(), array('count' =>  new Zend_Db_Expr('COUNT('.$order->getMainTable().'.entity_id)')))
            ->where("TIMESTAMPDIFF(SECOND, created_at, UTC_TIMESTAMP()) > ".$timeDiff);

        $totalOperations = $quoteAdapter->select()
            ->from($quote->getMainTable(), array('count' =>  new Zend_Db_Expr('COUNT('.$quote->getMainTable().'.entity_id)')))
            ->where("items_count >?", 0)
            ->where("TIMESTAMPDIFF(SECOND, updated_at, UTC_TIMESTAMP()) > ".$timeDiff);

        $totalAbandonedCarts = $quoteAdapter->select()
            ->from($quote->getMainTable(), array('count' =>  new Zend_Db_Expr('COUNT('.$quote->getMainTable().'.entity_id)')))
            ->where("items_count >?", 0)
            ->where("is_active =?",1)
            ->where("TIMESTAMPDIFF(SECOND, updated_at, UTC_TIMESTAMP()) > ".$timeDiff);

        $totalAbandonedCartsSubtotal = $quoteAdapter->select()
            ->from($quote->getMainTable(), array('total' =>  new Zend_Db_Expr('SUM('.$quote->getMainTable().'.subtotal)')))
            ->where("items_count >?", 0)
            ->where("is_active =?",1)
            ->where("TIMESTAMPDIFF(SECOND, updated_at, UTC_TIMESTAMP()) > ".$timeDiff);

        $totalAbandonedCartsWithEmail = $quoteAdapter->select()
            ->from($quote->getMainTable(), array('count' =>  new Zend_Db_Expr('COUNT('.$quote->getMainTable().'.entity_id)')))
            ->where('customer_email is not null')
            ->where("items_count >?", 0)
            ->where("is_active =?",1)
            ->where("TIMESTAMPDIFF(SECOND, updated_at, UTC_TIMESTAMP()) > ".$timeDiff);

        $totalAbandonedCartsWithEmailGrandtotal = $quoteAdapter->select()
            ->from($quote->getMainTable(), array('total' =>  new Zend_Db_Expr('SUM('.$quote->getMainTable().'.grand_total)')))
            ->where('customer_email is not null')
            ->where("items_count >?", 0)
            ->where("is_active =?",1)
            ->where("TIMESTAMPDIFF(SECOND, updated_at, UTC_TIMESTAMP()) > ".$timeDiff);


        $startDate = date('Y-m-d H:i:s', $mdl->gmtTimestamp(strtotime(str_replace('-', '/', $response['filter']['startDate'] . ' 00:00:00'))));

        $totalOperations->where($quote->getMainTable().'.created_at >= ?', $startDate);
        $totalOrders->where($order->getMainTable().'.created_at >= ?', $startDate);
        $totalAbandonedCarts->where($quote->getMainTable().'.created_at >= ?', $startDate);
        $totalAbandonedCartsSubtotal->where($quote->getMainTable().'.created_at >= ?', $startDate);
        $totalAbandonedCartsWithEmail->where($quote->getMainTable().'.created_at >= ?', $startDate);
        $totalAbandonedCartsWithEmailGrandtotal->where($quote->getMainTable().'.created_at >= ?', $startDate);

        $endDate = date('Y-m-d H:i:s', $mdl->gmtTimestamp(strtotime(str_replace('-', '/', $response['filter']['endDate'] . ' 23:59:59'))));

        $totalOperations->where($quote->getMainTable().'.created_at <= ?', $endDate);
        $totalOrders->where($order->getMainTable().'.created_at <= ?', $endDate);
        $totalAbandonedCarts->where($quote->getMainTable().'.created_at <= ?', $endDate);
        $totalAbandonedCartsSubtotal->where($quote->getMainTable().'.created_at <= ?', $endDate);
        $totalAbandonedCartsWithEmail->where($quote->getMainTable().'.created_at <= ?', $endDate);
        $totalAbandonedCartsWithEmailGrandtotal->where($quote->getMainTable().'.created_at <= ?', $endDate);

        $totalOps       = $quoteAdapter->fetchOne($totalOperations);
        $totalOrders    = $orderAdapter->fetchOne($totalOrders);
        $report['bw_carts_started_and_abandoned']   = '0';
        if ( ($totalOps - $totalOrders) > 0) {
            $report['bw_carts_started_and_abandoned']   = $totalOps - $totalOrders;
        }
        $report['bw_carts_started']                 = $totalOps;
        $report['bw_reachable_carts']               = $quoteAdapter->fetchOne($totalAbandonedCartsWithEmail);
        $report['bw_abandonment_rate']              = '0';
        $report['bw_reachable_as_percentage']       = '0';
        if ($totalOps > 0) {
            $report['bw_abandonment_rate']= round(($report['bw_carts_started_and_abandoned'] * 100) / $totalOps) . '%';
            $report['bw_reachable_as_percentage']  = round(($quoteAdapter->fetchOne($totalAbandonedCartsWithEmail) * 100) / $totalOps );
        }

        $report['bw_avg_reachable_value'] = '0';
        if ($report['bw_reachable_carts'] > 0) {
            $report['bw_avg_reachable_value'] = number_format($quoteAdapter->fetchOne($totalAbandonedCartsWithEmailGrandtotal) / $report['bw_reachable_carts'], 2);
        }

        $response['left']= $report;
        return $response;
    }
}
