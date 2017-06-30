<?php
/**
 * Created by JetBrains PhpStorm.
 * User: martin
 * Date: 6/26/13
 * Time: 1:46 PM
 * To change this template use File | Settings | File Templates.
 */

class Ebizmarts_BakerlooRestful_Model_Api_Stats extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    /**
     * Process GET requests.
     *
     * @return type
     * @throws Exception
     */
    public function get() {

        $type = $this->_getQueryParameter('type');

        if($type == "order_count_this_month_per_day_and_last_month"){
            return $this->_getOrderCountThisMonthPerDayAndLastMonth();
        }

        return null;
    }

    /*
    private function _getOrderCountThisMonth(){

        $firstOfMonthDate = date('Y-m-d', mktime(0, 0, 0, date("m")  , 1, date("Y")));

        $orderCount = Mage::getModel('bakerloo_restful/order')->getCollection()
            ->addFieldToFilter('main_table.created_at',array("from"=>$firstOfMonthDate))
            ->count();

        return $orderCount;


    }
    */

    private function _getOrderCountThisMonthPerDayAndLastMonth(){

        $returnObject = array();
        $stop = false;
        $iterDay = date('Y-m-d', mktime(0, 0, 0, date('m')-1, 1, date('Y')));
        $tomorrow = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d")+1, date("Y")));

        while(!$stop) {

            $nextDay = date('Y-m-d', strtotime($iterDay . ' + 1 day'));


            $ordersThatDay = Mage::getModel('bakerloo_restful/order')->getCollection()
                ->addFieldToFilter('main_table.created_at', array("from"=>$iterDay, "to"=>$nextDay))
                ->addFieldToFilter('main_table.order_id', array('gt' => 0));

            $returnObject[$iterDay] = array("order_count" => $ordersThatDay->count());

            $iterDay = $nextDay;
            if($iterDay==$tomorrow){
                $stop = true;
            }
        }

        return $returnObject;


    }

    /*
    private function _getOrderTotalsStats(){

        $returnObject = array();
        $currencyList = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
        $usersList = Mage::getModel('admin/user')->getCollection()->getColumnValues('username');

        for($currencyIter = 0;$currencyIter<count($currencyList);$currencyIter++){

            $returnObject[$currencyList[$currencyIter]] = array();

            for($usersIter = 0;$usersIter<count($usersList);$usersIter++){

                $ordersAmountsTotals = Mage::getModel('bakerloo_restful/order')->getCollection()
                    ->addFieldToFilter('main_table.admin_user',array("eq"=>$usersList[$usersIter]))
                    ->addFieldToFilter('main_table.order_currency_code',array("eq"=>$currencyList[$currencyIter]))
                    ->getColumnValues('grand_total');

                $countAmountsTotals = array_sum($ordersAmountsTotals);

                $returnObject[$currencyList[$currencyIter]][$usersList[$usersIter]] = $countAmountsTotals;

            }
        }

        return $returnObject;

    }
    */
}