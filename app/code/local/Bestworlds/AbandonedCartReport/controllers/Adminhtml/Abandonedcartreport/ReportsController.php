<?php

class Bestworlds_AbandonedCartReport_Adminhtml_Abandonedcartreport_ReportsController extends Mage_Adminhtml_Controller_Action {

    public function _initAction(){
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('abandonedcartreport')->__('Reports'), Mage::helper('abandonedcartreport')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('abandonedcartreport')->__('Shopping Cart'), Mage::helper('abandonedcartreport')->__('BestWorlds Reachable Carts Report'));
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/promo/abandonedcartreport/abandonedcart_reports');
    }

    public function indexAction(){

        $this->_title($this->__('Reports'))
            ->_title($this->__('Shopping Cart'))
            ->_title($this->__('Cart Recovery Report'));

        $this->_initAction()
            ->_setActiveMenu('report/shopcart/abandonedcartreport_reports')
            ->_addBreadcrumb(Mage::helper('abandonedcartreport')->__('BestWorlds Reachable Carts Report'), Mage::helper('abandonedcartreport')->__('Cart Recovery Report'))
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
        }else{
            $response= ['error' => 'Please try again later'];
        }
        $this->_ajaxResponse($response);
    }

    private function _getPercentage($val1, $val2, $precision){

        return ($val1 && $val2)? round(($val1/$val2)*100, $precision) : '0';
    }

    private function _getReport($response) {
        $report     = [];
        $timeDiff   = 3600;
        $quote = Mage::getResourceModel('sales/quote');
        $order = Mage::getResourceModel('sales/order');

        $mdl          = Mage::getModel('core/date');
        $quoteAdapter = $quote->getReadConnection();
        $orderAdapter = $order->getReadConnection();

        $allStores = Mage::app()->getStores();
        $_store_ids= array();
        foreach ($allStores as $_eachStoreId => $val)
        {
            $_store_ids[]= Mage::app()->getStore($_eachStoreId)->getId();
        }

        $totalOperations = $quoteAdapter->select()
            ->from($quote->getMainTable(), array('count' =>  new Zend_Db_Expr('COUNT('.$quote->getMainTable().'.entity_id)')))
            ->where("items_count >?", 0)
            ->where("TIMESTAMPDIFF(SECOND, updated_at, UTC_TIMESTAMP()) > ".$timeDiff);

        //GET TOTAL COMPLETED ORDERS FROM PERIOD, JUST LIKE THE SALES REPORT
        $aggregatedColumns = array( 'orders_count' =>  'sum(orders_count)');

        $totalsCollection = Mage::getResourceModel('sales/report_order_collection')
            ->setPeriod('day');

        $from   = new DateTime($response['filter']['startDate']);
        $from   = $from->format('Y-m-d');
        $to     = new DateTime($response['filter']['endDate']);
        $to     = $to->format('Y-m-d');
        $totalsCollection->setDateRange($from, $to);
        $totalsCollection->addStoreFilter($_store_ids);
        $totalsCollection->setAggregatedColumns($aggregatedColumns)
            ->addOrderStatusFilter(Mage_Sales_Model_Order::STATE_COMPLETE)
            ->isTotals(true);
        foreach ($totalsCollection as $item)
        {
            $totalCompletedOperations       = $item->getOrdersCount();
        }

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

        $totalAbandonedCartsGrandtotal = $quoteAdapter->select()
            ->from($quote->getMainTable(), array('total' =>  new Zend_Db_Expr('SUM('.$quote->getMainTable().'.grand_total)')))
            ->where("items_count >?", 0)
            ->where("is_active =?",1)
            ->where("TIMESTAMPDIFF(SECOND, updated_at, UTC_TIMESTAMP()) > ".$timeDiff);

        $totalAbandonedCartsWithEmail = $quoteAdapter->select()
            ->from($quote->getMainTable(), array('count' =>  new Zend_Db_Expr('COUNT('.$quote->getMainTable().'.entity_id)')))
            ->where('customer_email is not null')
            ->where("items_count >?", 0)
            ->where("is_active =?",1)
            ->where("TIMESTAMPDIFF(SECOND, updated_at, UTC_TIMESTAMP()) > ".$timeDiff);

        $totalAbandonedCartsWithEmailSubtotal = $quoteAdapter->select()
            ->from($quote->getMainTable(), array('total' =>  new Zend_Db_Expr('SUM('.$quote->getMainTable().'.subtotal)')))
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


        $parsedDate = date('Y-m-d H:i:s', $mdl->timestamp(strtotime(str_replace('-', '/', $response['filter']['startDate']))));
        $totalOperations->where($quote->getMainTable().'.created_at >= ?', $parsedDate);

        $totalAbandonedCarts->where($quote->getMainTable().'.created_at >= ?', $parsedDate);
        $totalAbandonedCartsSubtotal->where($quote->getMainTable().'.created_at >= ?', $parsedDate);
        $totalAbandonedCartsGrandtotal->where($quote->getMainTable().'.created_at >= ?', $parsedDate);
        $totalAbandonedCartsWithEmail->where($quote->getMainTable().'.created_at >= ?', $parsedDate);
        $totalAbandonedCartsWithEmailSubtotal->where($quote->getMainTable().'.created_at >= ?', $parsedDate);
        $totalAbandonedCartsWithEmailGrandtotal->where($quote->getMainTable().'.created_at >= ?', $parsedDate);

        $parsedDate = date('Y-m-d H:i:s', $mdl->timestamp(strtotime(str_replace('-', '/', $response['filter']['endDate']))));
        $totalOperations->where($quote->getMainTable().'.created_at <= ?', $parsedDate);

        $totalAbandonedCarts->where($quote->getMainTable().'.created_at <= ?', $parsedDate);
        $totalAbandonedCartsSubtotal->where($quote->getMainTable().'.created_at <= ?', $parsedDate);
        $totalAbandonedCartsGrandtotal->where($quote->getMainTable().'.created_at <= ?', $parsedDate);
        $totalAbandonedCartsWithEmail->where($quote->getMainTable().'.created_at <= ?', $parsedDate);
        $totalAbandonedCartsWithEmailSubtotal->where($quote->getMainTable().'.created_at <= ?', $parsedDate);
        $totalAbandonedCartsWithEmailGrandtotal->where($quote->getMainTable().'.created_at <= ?', $parsedDate);

        $totalOps   = $quoteAdapter->fetchOne($totalOperations);

        $report['bw_carts_started_and_abandoned']   = $orderAdapter->fetchOne($totalOperations) - $totalCompletedOperations;
        $report['bw_carts_started']                 = $orderAdapter->fetchOne($totalOperations);
        $report['bw_reachable_carts']               = $quoteAdapter->fetchOne($totalAbandonedCartsWithEmail);
        $report['bw_abandonment_rate']              = 0;
        $report['bw_reachable_as_percentage']       = 0;
        if ($totalOps > 0) {
            $report['bw_abandonment_rate']= round(($report['bw_carts_started_and_abandoned'] * 100) / $totalOps) . '%';
            $report['bw_reachable_as_percentage']  = round(($quoteAdapter->fetchOne($totalAbandonedCartsWithEmail) * 100) / $totalOps );
        }

        $report['bw_avg_reachable_value'] = 0;
        if ($report['bw_reachable_carts'] > 0) {
            $report['bw_avg_reachable_value'] = number_format($quoteAdapter->fetchOne($totalAbandonedCartsWithEmailGrandtotal) / $report['bw_reachable_carts'], 2);
        }

        $response['left']= $report;
        return $response;
    }

}