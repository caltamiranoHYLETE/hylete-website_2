<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Shipping
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 *
 * @category   Webshopapps
 * @package    Webshopapps_shippingoveride2
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
class Webshopapps_Shippingoverride2_Model_Mysql4_Shippingoverride2 extends Mage_Core_Model_Mysql4_Abstract
{

    private $_request;
    private $_customerGroupCode;
    private $_customerId;
    private static $_debug;
    private $_highest = 999;
    private $_usingPriorities = false;
    private $_options;
    private $_runningCartPrice = 0;

    protected function _construct()
    {
        $this->_init('shipping/shippingoverride2', 'pk');
    }


    /**
     * ******* NOTE *************: If you change this interface please ensure you also change in Dropcommon
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @param $exclusionList
     * @param $error
     * @param $weightIncArr
     * @param array $minPriceArr
     * @param int $productShipPrice
     * @return array
     */
    public function getNewRate(Mage_Shipping_Model_Rate_Request $request, &$exclusionList, &$error, &$weightIncArr, &$minPriceArr = array(),
                               &$productShipPrice = 0)
    {
        $this->_request = $request;
        self::$_debug = Mage::helper('shippingoverride2')->isDebug();
        $this->_options = explode(',', Mage::getStoreConfig("shipping/shippingoverride2/ship_options"));

        $readAdaptor = $this->_getReadAdapter();

        if (self::$_debug) {
            Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'Found these advanced options in effect',
                $this->_options);
        }

        $items = $request->getAllItems();

        // get the special_shipping_group's for the items in the cart

        $structuredItems = $this->getStructuredItems($items);

        $shipPriceOption = Mage::helper('shippingoverride2')->getShipPriceOption();
        switch ($shipPriceOption) {
            case 'append_ship_price': // append onto shipping price
                $productShipPrice = $this->getShippingPriceNoCSV($structuredItems);
                return array();
                break;
            case 'replace_ship_price':
                return $this->_setProductsToIgnore($structuredItems, false);
                break;
            default:
                return $this->_processRulesFromTable($structuredItems, $readAdaptor, $exclusionList, $error, $weightIncArr,$minPriceArr);
                break;
        }

    }


    protected function _processRulesFromTable($structuredItems, &$readAdaptor, &$exclusionList, &$error, &$weightIncArr,
                        &$minPriceArr)
    {
        $deliveryOverrideList = array();
        $starIncludeAll = Mage::helper('shippingoverride2')->isStarIncludeAll();

        $this->_customerGroupCode = $this->getCustomerGroupCode();
        $this->_customerId= $this->getCustomerId();

        if (self::$_debug) {
            Mage::helper('wsalogger/log')->postInfo('shippingoverride2', 'Customer Group Code', $this->_customerGroupCode);
            Mage::helper('wsalogger/log')->postInfo('shippingoverride2', 'Customer Id', $this->_customerId);
        }

        $zipSearchString = $this->_getZipSearchString($readAdaptor);
        $searchPOBox = $this->_searchPOBox();

        $numGroups = $starIncludeAll ? count($structuredItems) - 1 : count($structuredItems);

        foreach ($structuredItems as $group => $structuredItem) {
            if ($group == 'none' && $starIncludeAll) {
                continue;
            }
            if (self::$_debug) {
                Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'Shipping Group', $group);
            }
            $rates = $this->runSelectStmt($readAdaptor, $structuredItem, $group, $searchPOBox, $zipSearchString);
            if (self::$_debug) {
                Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'Rates Found', $rates);
            }
            if (!empty($rates)) {
                // found something - what do we do with this?
                foreach ($rates as $rate) {
                    $surchargeLiftgate = false;
                    if ($rate['price'] == -1) {
                        switch ($rate['algorithm']) {
                            case 'ORDER_SINGLE':
                                if ($numGroups > 1) {
                                    break;
                                }
                                // exclude delivery type from shipping
                                if (!in_array($rate['delivery_type'], $exclusionList)) {
                                    $exclusionList[] = $rate['delivery_type'];
                                    if (array_key_exists('rules', $rate) && $rate['rules'] != '') {
                                        $this->getCustomError($rate, $error);
                                    }
                                }
                                break;
                            case 'ORDER_MERGED':
                            case 'SURCHARGE_ORDER_MERGED':
                                if ($numGroups == 1) {
                                    break;
                                }
                            case 'ORDER_RES':
                            case 'ORDER_COM'://SO-34
                            $addType = trim($this->_request->getUpsDestType(), '_M');
                                if (($rate['algorithm']=='ORDER_RES' && $addType != 'RES') ||
                                    ($rate['algorithm']=='ORDER_COM' && $addType != 'COM')) {
                                    break;
                                }
                            case 'ORDER_FREE'://SO-33
                                if(!$this->_request->getFreeShipping()){
                                    break;
                                }
                            default:
                                // exclude delivery type from shipping
                                if (!in_array($rate['delivery_type'], $exclusionList)) {
                                    $exclusionList[] = $rate['delivery_type'];
                                    if (array_key_exists('rules', $rate) && $rate['rules'] != '') {
                                        $this->getCustomError($rate, $error);
                                    }
                                }
                                break;
                        }
                        continue;
                    }

                    switch ($rate['algorithm']) {
                        case 'OVERRIDE':
                            $deliveryOverrideList[$rate['delivery_type']] = array(
                                'ship_price' => $rate['price'],
                                'ship_percent' => $rate['percentage'],
                                'exclude_groups' => array($group),
                                'include_groups' => array(),
                                'wipe_rate' => true,
                                'surcharge' => false,
                                'override' => true,
                                'weight_increase' => 0,
                            );
                            if ($rate['rules'] != '') {
                                $deliveryOverrideList[$rate['delivery_type']]['rules'][] = $rate['rules'];
                            }
                            break;
                        case 'OVERRIDE_ITEM':
                            $deliveryOverrideList[$rate['delivery_type']] = array(
                                'ship_price' => $rate['price'] * $structuredItem['qty'],
                                'ship_percent' => $rate['percentage'],
                                'exclude_groups' => array($group),
                                'include_groups' => array(),
                                'wipe_rate' => true,
                                'surcharge' => false,
                                'override' => true,
                                'weight_increase' => 0,
                            );
                            if ($rate['rules'] != '') {
                                $deliveryOverrideList[$rate['delivery_type']]['rules'][] = $rate['rules'];
                            }
                            break;
                        case 'ORDER_LIFT':
                            $surchargeLiftgate = true;
                        case 'ORDER_SINGLE':
                            if ($numGroups > 1 && $rate['algorithm'] == 'ORDER_SINGLE') {
                                break;
                            }
                        case 'ORDER':
                        case 'ORDER_RES':
                        case 'ORDER_COM':
                        case 'ORDER_MERGED':
                        case 'ORDER_FREE'://SO-33

                        $resCom = Mage::helper('shippingoverride2')->getResCom($this->_request);

                        if (($rate['algorithm'] == 'ORDER_RES' && $resCom != 'RES') ||
                            ($rate['algorithm'] == 'ORDER_COM' && $resCom != 'COM') ||
                            ($rate['algorithm'] == 'ORDER_MERGED' && $numGroups == 1)) {
                            break;
                        }

                        if ($rate['algorithm'] == 'ORDER_FREE' && !$this->_request->getFreeShipping()) {
                            break;
                        }

                        // flat rate override2, get flat totals
                        $this->processOrderAlgorithm($rate, $deliveryOverrideList, $group, $surchargeLiftgate);
                        break;
                        case 'ITEM':
                        case 'ITEM_RES':
                        case 'ITEM_COM':
                            if (($rate['algorithm']=='ITEM_RES' && $this->_request->getUpsDestType() != 'RES') ||
                                ($rate['algorithm']=='ITEM_COM' && $this->_request->getUpsDestType() != 'COM')) {
                                break;
                            }
                                // flat rate override2, get flat totals
                            if (array_key_exists($rate['delivery_type'], $deliveryOverrideList)) {
                                if (!$deliveryOverrideList[$rate['delivery_type']]['override']) {
                                    $deliveryOverrideList[$rate['delivery_type']]['ship_price'] += $rate['price'] * $structuredItem['qty'];
                                    $deliveryOverrideList[$rate['delivery_type']]['ship_percent'] += $rate['percentage'];
                                    $deliveryOverrideList[$rate['delivery_type']]['surcharge'] = false;
                                    $deliveryOverrideList[$rate['delivery_type']]['exclude_groups'][] = $group;
                                    if ($rate['rules'] != '') {
                                        $deliveryOverrideList[$rate['delivery_type']]['rules'][] = $rate['rules'];
                                    }
                                }
                            } else {
                                $deliveryOverrideList[$rate['delivery_type']] = array(

                                    'ship_price' => $rate['price'] * $structuredItem['qty'],
                                    'ship_percent' => $rate['percentage'],
                                    'exclude_groups' => array($group),
                                    'include_groups' => array(),
                                    'wipe_rate' => true,
                                    'surcharge' => false,
                                    'override' => false,
                                    'weight_increase' => 0,
                                );
                                if ($rate['rules'] != '') {
                                    $deliveryOverrideList[$rate['delivery_type']]['rules'][] = $rate['rules'];
                                }
                            }
                            break;
                        case 'ADD_ITEM':
                            if (array_key_exists($rate['delivery_type'], $deliveryOverrideList)) {
                                if (!$deliveryOverrideList[$rate['delivery_type']]['override']) {
                                    $deliveryOverrideList[$rate['delivery_type']]['ship_price'] += $rate['price'] * ($structuredItem['qty'] - $rate['item_from_value']);
                                    $deliveryOverrideList[$rate['delivery_type']]['ship_percent'] += $rate['percentage'];
                                    $deliveryOverrideList[$rate['delivery_type']]['exclude_groups'][] = $group;
                                    $deliveryOverrideList[$rate['delivery_type']]['surcharge'] = false;
                                    if ($rate['rules'] != '') {
                                        $deliveryOverrideList[$rate['delivery_type']]['rules'][] = $rate['rules'];
                                    }
                                }
                            } else {
                                $deliveryOverrideList[$rate['delivery_type']] = array(

                                    'ship_price' => $rate['price'] * ($structuredItem['qty'] - $rate['item_from_value']),
                                    'ship_percent' => $rate['percentage'],
                                    'exclude_groups' => array($group),
                                    'include_groups' => array(),
                                    'wipe_rate' => true,
                                    'surcharge' => false,
                                    'override' => false,
                                    'weight_increase' => 0,
                                );
                                if ($rate['rules'] != '') {
                                    $deliveryOverrideList[$rate['delivery_type']]['rules'][] = $rate['rules'];
                                }
                            }
                            break;
                        case 'SURCHARGE_ORDER_MERGED':
                            if ($numGroups == 1) {
                                break;
                            }
                        // now same as SURCHARGE_ORDER, make sure it drops through into this, can't be any other case here
                        case 'SURCHARGE_ORDER':
                        case 'SURCHARGE_ORDER_RES':
                        case 'SURCHARGE_ORDER_COM':

                            $resCom = Mage::helper('shippingoverride2')->getResCom($this->_request);

                            if (($rate['algorithm'] == 'SURCHARGE_ORDER_RES' && $resCom != 'RES') ||
                                ($rate['algorithm'] == 'SURCHARGE_ORDER_COM' && $resCom != 'COM')) {
                                break;
                            }

                            // flat rate override2, get flat totals
                            if (array_key_exists($rate['delivery_type'], $deliveryOverrideList)) {
                                if (!$deliveryOverrideList[$rate['delivery_type']]['override']) {
                                    $deliveryOverrideList[$rate['delivery_type']]['ship_price'] += $rate['price'];
                                    $deliveryOverrideList[$rate['delivery_type']]['ship_percent'] += $rate['percentage'];
                                    $deliveryOverrideList[$rate['delivery_type']]['include_groups'][] = $group;
                                    if ($rate['rules'] != '') {
                                        $deliveryOverrideList[$rate['delivery_type']]['rules'][] = $rate['rules'];
                                    }
                                }
                                // keep items
                            } else {

                                $deliveryOverrideList[$rate['delivery_type']] = array(
                                    'ship_price' => $rate['price'],
                                    'ship_percent' => $rate['percentage'],
                                    'exclude_groups' => array(),
                                    'include_groups' => array($group),
                                    'wipe_rate' => false,
                                    'surcharge' => true,
                                    'override' => false,
                                    'weight_increase' => 0,
                                );
                                if ($rate['rules'] != '') {
                                    $deliveryOverrideList[$rate['delivery_type']]['rules'][] = $rate['rules'];
                                }
                            }
                            break;
                        case 'SURCHARGE_ITEM':
                            // flat rate override2, get flat totals
                            if (array_key_exists($rate['delivery_type'], $deliveryOverrideList)) {
                                if (!$deliveryOverrideList[$rate['delivery_type']]['override']) {
                                    $deliveryOverrideList[$rate['delivery_type']]['ship_price'] += $rate['price'] * $structuredItem['qty'];
                                    $deliveryOverrideList[$rate['delivery_type']]['ship_percent'] += $rate['percentage'];
                                    $deliveryOverrideList[$rate['delivery_type']]['include_groups'][] = $group;
                                    if ($rate['rules'] != '') {
                                        $deliveryOverrideList[$rate['delivery_type']]['rules'][] = $rate['rules'];
                                    }
                                }
                                // keep items
                            } else {
                                $deliveryOverrideList[$rate['delivery_type']] = array(
                                    'ship_price' => $rate['price'] * $structuredItem['qty'],
                                    'ship_percent' => $rate['percentage'],
                                    'exclude_groups' => array(),
                                    'surcharge' => true,
                                    'include_groups' => array($group),
                                    'wipe_rate' => false,
                                    'override' => false,
                                    'weight_increase' => 0,
                                );
                                if ($rate['rules'] != '') {
                                    $deliveryOverrideList[$rate['delivery_type']]['rules'][] = $rate['rules'];
                                }
                            }
                            break;

                        case 'PERCENTAGE_CART':
                            // flat rate override2, get flat totals
                            if (array_key_exists($rate['delivery_type'], $deliveryOverrideList)) {
                                if (!$deliveryOverrideList[$rate['delivery_type']]['override']) {
                                    //	$deliveryOverrideList[$rate['delivery_type']]['product_ship_price'] += $structuredItem['shipping_price'];
                                    $deliveryOverrideList[$rate['delivery_type']]['ship_price'] += $rate['price'] + ($rate['percentage'] / 100) * $structuredItem['price'];
                                    $deliveryOverrideList[$rate['delivery_type']]['surcharge'] = false;
                                    $deliveryOverrideList[$rate['delivery_type']]['exclude_groups'][] = $group;
                                    if ($rate['rules'] != '') {
                                        $deliveryOverrideList[$rate['delivery_type']]['rules'][] = $rate['rules'];
                                    }
                                }
                            } else {
                                $deliveryOverrideList[$rate['delivery_type']] = array(
                                    'ship_price' => $rate['price'] + ($rate['percentage'] / 100) * $structuredItem['price'],
                                    'exclude_groups' => array($group),
                                    'surcharge' => false,
                                    'ship_percent' => 0,
                                    'include_groups' => array(),
                                    'wipe_rate' => true,
                                    'override' => false,
                                    'weight_increase' => 0,
                                );
                                if ($rate['rules'] != '') {
                                    $deliveryOverrideList[$rate['delivery_type']]['rules'][] = $rate['rules'];
                                }
                            }
                            break;
                        case 'SURCHARGE_PERCENTAGE_CART':
                            // flat rate override2, get flat totals
                            if (array_key_exists($rate['delivery_type'], $deliveryOverrideList)) {
                                if (!$deliveryOverrideList[$rate['delivery_type']]['override']) {
                                    $deliveryOverrideList[$rate['delivery_type']]['ship_price'] += $rate['price'] + ($rate['percentage'] / 100) * $structuredItem['price'];
                                    $deliveryOverrideList[$rate['delivery_type']]['include_groups'][] = $group;
                                    if ($rate['rules'] != '') {
                                        $deliveryOverrideList[$rate['delivery_type']]['rules'][] = $rate['rules'];
                                    }
                                }
                                // keep items
                            } else {
                                $deliveryOverrideList[$rate['delivery_type']] = array(
                                    'ship_price' => $rate['price'] + ($rate['percentage'] / 100) * $structuredItem['price'],
                                    'ship_percent' => 0,
                                    'exclude_groups' => array($group),
                                    'include_groups' => array(),
                                    'wipe_rate' => false,
                                    'surcharge' => true,
                                    'override' => false,
                                    'weight_increase' => 0,
                                );
                                if ($rate['rules'] != '') {
                                    $deliveryOverrideList[$rate['delivery_type']]['rules'][] = $rate['rules'];
                                }
                            }
                            break;
                        case 'WEIGHTINC':
                            if (!array_key_exists($group, $weightIncArr)) {
                                $weightIncArr[$group] = $rate['price'];
                            } else {
                                $weightIncArr[$group] += $rate['price']; // double using here, this is actually weight value
                            }
                            break;
                        case 'PREVENT_MIN':
                            // will prevent shipping if min price is not hit
                            $minPriceArr[$rate['delivery_type']]=$rate['price'];
                            break;

                    }
                }
            }
        }

        if (count($weightIncArr) > 0) {
            $weightIncArr['all_items'] = $this->_getTotalWeightIncrease($weightIncArr);
        }


        if (self::$_debug) {
            Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'Override List', $deliveryOverrideList);
            Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'Exclude List', $exclusionList);
            Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'Custom Message', $error);
            Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'Weight Increase', $weightIncArr);

        }

        return $this->_processRuleResults($deliveryOverrideList, $structuredItems, $starIncludeAll, $weightIncArr
            , $exclusionList);


    }

    /**
     * This function implements the ORDER algorithm which replaces the shipping price for
     * items in a shipping group with a custom rate
     *
     * @param $rate
     * @param $deliveryOverrideList
     * @param $group
     * @param $surchargeLiftgate
     */
    private function processOrderAlgorithm($rate, &$deliveryOverrideList, $group, $surchargeLiftgate)
    {
        if (array_key_exists($rate['delivery_type'], $deliveryOverrideList)) {
            if (!$deliveryOverrideList[$rate['delivery_type']]['override']) {
                $deliveryOverrideList[$rate['delivery_type']]['ship_price'] += $rate['price'];
                $deliveryOverrideList[$rate['delivery_type']]['ship_percent'] += $rate['percentage'];
                $deliveryOverrideList[$rate['delivery_type']]['exclude_groups'][] = $group;
                $deliveryOverrideList[$rate['delivery_type']]['surcharge'] = false;
                $deliveryOverrideList[$rate['delivery_type']]['surcharge_liftgate'] = $surchargeLiftgate;

                if ($rate['rules'] != '') {
                    $deliveryOverrideList[$rate['delivery_type']]['rules'][] = $rate['rules'];
                }
            }
        } else {
            $deliveryOverrideList[$rate['delivery_type']] = array(
                'ship_price' => $rate['price'],
                'ship_percent' => $rate['percentage'],
                'exclude_groups' => array($group),
                'include_groups' => array(),
                'wipe_rate' => true,
                'surcharge' => false,
                'override' => false,
                'surcharge_liftgate' => $surchargeLiftgate,
                'weight_increase' => 0,
            );
            if ($rate['rules'] != '') {
                $deliveryOverrideList[$rate['delivery_type']]['rules'][] = $rate['rules'];
            }
        }
    }

    protected function _processRuleResults(&$deliveryOverrideList, $structuredItems, $starIncludeAll,
                                           $weightIncArr = array(), $exclusionList = array())
    {


        foreach ($deliveryOverrideList as $deliveryType => $override2Details) {
            $excludeGroups = $override2Details['exclude_groups'];
            $deliveryOverrideList[$deliveryType]['product_ship_price'] = 0;
            $flatExclusionGroups = array();
            if (array_key_exists('rules', $override2Details)) {
                $this->processRules($override2Details['rules'], $flatExclusionGroups);

            }
            if (in_array('include_all', $excludeGroups)) {
                // the product_ship_price is subject to problems - will most likely be here
                if (!$deliveryOverrideList[$deliveryType]['override']) {
                    foreach ($structuredItems['include_all']['shipping_price_array'] as $shippingGroup => $price) {
                        if (!in_array($shippingGroup, $flatExclusionGroups)) {
                            $deliveryOverrideList[$deliveryType]['product_ship_price'] += $price;
                        }
                    }
                    foreach ($structuredItems['include_all']['ship_price_order_array'] as $shippingGroup => $price) {
                        if (!in_array($shippingGroup, $flatExclusionGroups)) {
                            $deliveryOverrideList[$deliveryType]['product_ship_price'] += $price;
                        }
                    }
                }
                continue;
            }
            foreach ($structuredItems as $group => $structuredItem) {

                if (array_key_exists($group, $weightIncArr)) {
                    $deliveryOverrideList[$deliveryType]['weight_increase'] += $weightIncArr[$group];
                }

                if ($group == 'include_all') {
                    if (!in_array($group, $excludeGroups) && !$deliveryOverrideList[$deliveryType]['override']
                        ) {
                        foreach ($structuredItem['shipping_price_array'] as $shippingGroup => $price) {
                            if (!in_array($shippingGroup, $flatExclusionGroups)) {
                                $deliveryOverrideList[$deliveryType]['product_ship_price'] += $price;
                            }
                        }
                        foreach ($structuredItem['ship_price_order_array'] as $shippingGroup => $price) {
                            if (!in_array($shippingGroup, $flatExclusionGroups)) {
                                $deliveryOverrideList[$deliveryType]['product_ship_price'] += $price;
                            }
                        }
                    }
                    continue;
                }

                if (!$deliveryOverrideList[$deliveryType]['override'] && !in_array($group, $flatExclusionGroups)) {
                    if (!$starIncludeAll) {
                        $deliveryOverrideList[$deliveryType]['product_ship_price'] += $structuredItem['shipping_price'] + $structuredItem['ship_price_order'];
                    } else {
                        if ($structuredItems['include_all']['shipping_price_array'][$group] == 0 &&
                            !in_array($group, $excludeGroups) &&
                        (count($exclusionList) == 0 || !in_array($group, $exclusionList))
                        ) {
                            $deliveryOverrideList[$deliveryType]['product_ship_price'] += $structuredItem['shipping_price'];
                        }
                        if ($structuredItems['include_all']['ship_price_order_array'][$group] == 0 &&
                            !in_array($group, $excludeGroups) &&
                            (count($exclusionList) == 0 || !in_array($group, $exclusionList))
                        ) {
                            $deliveryOverrideList[$deliveryType]['product_ship_price'] += $structuredItem['ship_price_order'];//SO-36
                        }
                    }
                }


                if ((count($exclusionList) > 0 && in_array($group, $exclusionList)) || in_array($group, $excludeGroups)) {
                    continue;
                }

                // need to get only the items that apply to this group
                // this could be everything
                if (array_key_exists('cart_details', $deliveryOverrideList[$deliveryType])) {
                    $deliveryOverrideList[$deliveryType]['cart_details']['qty'] += $structuredItem['qty'];
                    $deliveryOverrideList[$deliveryType]['cart_details']['weight'] += $structuredItem['weight'];
                    $deliveryOverrideList[$deliveryType]['cart_details']['price'] += $structuredItem['price'];
                    //$deliveryOverrideList[$deliveryType]['cart_details']['item_group']+= $structuredItem['item_group'];
                    $deliveryOverrideList[$deliveryType]['cart_details']['item_group'] =
                        array_merge($structuredItem['item_group'], $deliveryOverrideList[$deliveryType]['cart_details']['item_group']);
                } else {
                    $deliveryOverrideList[$deliveryType]['cart_details'] = array(
                        'item_group' => $structuredItem['item_group'],
                        'qty' => $structuredItem['qty'],
                        'weight' => $structuredItem['weight'],
                        'price' => $structuredItem['price'],
                    );
                }
            }
        }


        // now check for those where it is total cart price

        foreach ($deliveryOverrideList as $deliveryType => $override2Details) {
            if (array_key_exists('cart_details', $deliveryOverrideList[$deliveryType])) {
                if ($deliveryOverrideList[$deliveryType]['cart_details']['qty'] == $this->_request->getPackageQty()) {
                    $deliveryOverrideList[$deliveryType]['cart_details'] = "";
                    $deliveryOverrideList[$deliveryType]['whole_cart'] = true;
                } else {
                    $deliveryOverrideList[$deliveryType]['whole_cart'] = false;
                }
            } else {
                $deliveryOverrideList[$deliveryType]['whole_cart'] = true;
            }
            if ($deliveryOverrideList[$deliveryType]['whole_cart'] && $deliveryOverrideList[$deliveryType]['ship_price'] == 0
                && $deliveryOverrideList[$deliveryType]['ship_percent'] == 0 && !$deliveryOverrideList[$deliveryType]['surcharge']
            ) {
                // Cant wipe this as may be a surcharge on order
                $deliveryOverrideList[$deliveryType]['wipe_rate'] = 1;
            }

            if (self::$_debug) {
                $tempLog['Delivery Type'] = $deliveryType;
                $tempLog['Exclude Groups'] = $override2Details['exclude_groups'];
                $tempLog['Product Ship Price'] = $override2Details['product_ship_price'];
                $tempLog['Ship Price'] = $override2Details['ship_price'];
                $tempLog['Include Groups'] = $override2Details['include_groups'];
                $tempLog['Weight Increase'] = $override2Details['weight_increase'];
                $tempLog['Whole Cart'] = $deliveryOverrideList[$deliveryType]['whole_cart'];
                Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'Override List to use', $tempLog);
            }

        }

        return $deliveryOverrideList;
    }

    protected function _setProductsToIgnore($structuredItems, $starIncludeAll)
    {

        $deliveryOverrideList = array();

        if (!array_key_exists('match', $structuredItems)) {
            return array();
        }

        $deliveryOverrideList['ALL'] = array(
            'ship_price' => 0,
            'ship_percent' => 0,
            'exclude_groups' => array('match'),
            'include_groups' => array(),
            'wipe_rate' => true,
            'surcharge' => false,
            'override' => false,
            'surcharge_liftgate' => 0,
            'weight_increase' => 0,
        );

        return $this->_processRuleResults($deliveryOverrideList, $structuredItems, $starIncludeAll);

    }


    protected function _getZipSearchString(&$readAdaptor)
    {
        $zipRangeSet = Mage::getStoreConfig('shipping/shippingoverride2/zip_range');
        $postcode = $this->_request->getDestPostcode();

        if ($zipRangeSet) {
            #  Want to search for postcodes within a range
            $zipSearchString = $readAdaptor->quoteInto(" AND dest_zip<=? ", $postcode) .
                $readAdaptor->quoteInto(" AND dest_zip_to>=? )", $postcode);
        } else {
            $zipSearchString = $readAdaptor->quoteInto(" AND ? LIKE dest_zip )", $postcode);
        }

        if (self::$_debug) {
            Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'Street Address', $this->_request->getDestStreet());
        }

        return $zipSearchString;

    }


    protected function _searchPOBox()
    {
        // if POBOX search on CITY field
        $searchPOBox = false;
        if (preg_match('/(^|(?:post(al)? *(?:office *)?|p[. ]*o\.? *))box *#? *\w+/ui', $this->_request->getDestStreet())) {
            $searchPOBox = true;
            if (self::$_debug) {
                Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'Can\'t Deliver to POBOX', '');
            }
        }

        return $searchPOBox;
    }

    protected function getShippingPriceNoCSV($structuredItem)
    {

        $productShipPrice = 0;
        foreach ($structuredItem['include_all']['shipping_price_array'] as $price) {
            $productShipPrice += $price;
        }
        foreach ($structuredItem['include_all']['ship_price_order_array'] as $price) {
            $productShipPrice += $price;
        }
        if (self::$_debug) {
            Mage::helper('wsalogger/log')->postDebug('shippingoverride2',
                'No csv. Surcharging all rates by this amount', $productShipPrice);
        }
        return $productShipPrice;
    }

    protected function _getTotalWeightIncrease($weightIncArr)
    {
        $totalCartIncrease = 0;
        foreach ($weightIncArr as $increaseValue) {
            $totalCartIncrease += $increaseValue;
        }
        return $totalCartIncrease;
    }


    protected function getCustomerGroupCode()
    {
        if ($ruleData = Mage::registry('rule_data')) {
            $gId = $ruleData->getCustomerGroupId();
            return Mage::getModel('customer/group')->load($gId)->getCode();
        } else {
            return Mage::getModel('customer/group')->load(
                Mage::getSingleton('customer/session')->getCustomerGroupId())->getCode();
        }

    }


    protected function getCustomerId()
    {
        if (Mage::registry('rule_data')) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote()->getCustomer()->getEntityId();
        } else {
            return Mage::getSingleton('customer/session')->getId();
        }
    }


    private function processRules($rules, &$exclusionGroups)
    {
        $exclusionGroups = array();

        foreach ($rules as $rule) {
            $algorithm_array = explode("&", $rule); // Multi-formula extension
            foreach ($algorithm_array as $algorithm_single) {
                $algorithm = explode("=", $algorithm_single, 2);
                if (!empty($algorithm) && count($algorithm) == 2) {
                    if (strtolower($algorithm[0]) == "ex_flat") {
                        $exclusionGroups[] = $algorithm[1];
                    }
                }
            }
        }
        if (self::$_debug) {
            Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'Rules in Effect', $exclusionGroups);
        }
    }

    private function getCustomError($rate, &$error)
    {
        $ruleField = explode("&", $rate['rules']);

        foreach ($ruleField as $i => $part) {
            $rule = explode("=", $part, 2);

            if (strtolower($rule[0]) == "p") {
                if ($rule[1] < $this->_highest) {
                    $this->_highest = $rule[1];
                    $this->_usingPriorities = true;
                    $errorCode = explode("=", $ruleField[$i + 1]);
                    $error = $errorCode[1];
                }
            } else if (!$this->_usingPriorities && count($rule) == 2) {
                $error = $rule[1];
            }
        }
    }


    private function runSelectStmt($read, $structuredItem, $group, $searchPOBox, $zipSearchString)
    {

        if (in_array('warehouse', $this->_options)) {
            $warehouseId = Mage::helper('shippingoverride2')->getWarehouseId($this->_request);
        }

        if ($searchPOBox) {
            $destCity = 'POBOX';
        } else {
            $destCity = $this->_request->getDestCity();
        }

        for ($j = 0; $j < 9; $j++) {
            //$select = $read->select()->from($table);
            $select = $read->select()->from(array('shippingoverride2' =>
                    Mage::getSingleton('core/resource')->getTableName('shippingoverride2/shippingoverride2')),
                array('pk' => 'pk',
                    'price' => 'price',
                    'percentage' => 'percentage',
                    'delivery_type' => 'delivery_type',
                    'algorithm' => 'algorithm',
                    'special_shipping_group' => 'special_shipping_group',
                    'rules' => 'rules',
                    'item_from_value' => 'item_from_value'));

            switch ($j) {
                case 0:
                    $select->where(
                        $read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()) .
                        $read->quoteInto(" AND dest_region_id=? ", $this->_request->getDestRegionId()) .
                        $read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  ", $destCity) .
                        $zipSearchString
                    );
                    break;
                case 1:
                    $select->where(
                        $read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()) .
                        $read->quoteInto(" AND dest_region_id=?  AND dest_city=''", $this->_request->getDestRegionId()) .
                        $zipSearchString
                    );
                    break;
                case 2:
                    $select->where(
                        $read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()) .
                        $read->quoteInto(" AND dest_region_id=? ", $this->_request->getDestRegionId()) .
                        $read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_zip='')", $destCity)
                    );
                    break;
                case 3:
                    $select->where(
                        $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()) .
                        $read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0'", $destCity) .
                        $zipSearchString
                    );
                    break;
                case 4:
                    $select->where(
                        $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()) .
                        $read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0' AND dest_zip='') ", $destCity)
                    );
                    break;
                case 5:
                    $select->where(
                        $read->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' ", $this->_request->getDestCountryId()) .
                        $zipSearchString
                    );
                    break;
                case 6:
                    $select->where(
                        $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()) .
                        $read->quoteInto(" AND dest_region_id=? AND dest_city='' AND dest_zip='') ", $this->_request->getDestRegionId())
                    );
                    break;

                case 7:
                    $select->where(
                        $read->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' AND dest_zip='') ", $this->_request->getDestCountryId())
                    );
                    break;

                case 8:
                    $select->where(
                        "  (dest_country_id='0' AND dest_region_id='0' AND dest_zip='')"
                    );
                    break;
            }

            if ($group == 'include_all' || $group == 'none') {
                $select->where('special_shipping_group=?', '');
            } else {
                $select->where('special_shipping_group=?', $group);

            }

            if (in_array('subtotalpw', $this->_options)) {
                $select->where('weight_from_value<?', $this->_request->getPackageWeight());
                $select->where('weight_to_value>=?', $this->_request->getPackageWeight());

                if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Dropship','carriers/dropship/active') &&
                    Mage::getStoreConfig('carriers/dropship/use_cart_price')) {
                    $select->where('price_from_value<?', $this->_request->getCartValue());//SO-50
                    $select->where('price_to_value>=?', $this->_request->getCartValue());
                } else {
                    $select->where('price_from_value<?', $this->_runningCartPrice);
                    $select->where('price_to_value>=?', $this->_runningCartPrice);
                }

            } else {
                $select->where('weight_from_value<?', $structuredItem['weight']);
                $select->where('weight_to_value>=?', $structuredItem['weight']);
                $select->where('item_weight_from_value<?', $structuredItem['item_weight']);
                $select->where('item_weight_to_value>=?', $structuredItem['item_weight']);
                $select->where('price_from_value<?', $structuredItem['price']);
                $select->where('price_to_value>=?', $structuredItem['price']);
            }

            $select->where('item_from_value<?', $structuredItem['qty']);
            $select->where('item_to_value>=?', $structuredItem['qty']);

            $groupArr[0] = "STRCMP(LOWER(customer_group),LOWER('" . $this->_customerGroupCode . "')) =0";
            $groupArr[1] = "customer_group=''";
            $groupArr[2] = "STRCMP(LOWER(customer_group),LOWER('" . $this->_customerId . "')) =0";
            $select->where(join(' OR ', $groupArr));

            $select->where('website_id=?', $this->_request->getWebsiteId());
            if (in_array('warehouse', $this->_options)) {
                $select->where("vendor IS NULL OR vendor in (0,?)", $warehouseId);
            }
            $select->order('price ASC');
            /*
            pdo has an issue. we cannot use bind
            */

            $row = $read->fetchAll($select);

            if (!empty($row)) {
                if (self::$_debug) {
                    Mage::helper('wsalogger/log')->postDebug('shippingoverride', 'SQL Select', $select->getPart('where'));
                }
                return $row;
            }

        }
        if (self::$_debug) {
            Mage::helper('wsalogger/log')->postDebug('shippingoverride', 'SQL Select', $select->getPart('where'));
        }
    }

    private function getStructuredItems($items)
    {
        $useParent = Mage::helper('shippingoverride2')->useParent();
        $useDiscount = in_array('usediscount', $this->_options);
        $useTax = in_array('usetax', $this->_options);
        $useBase = in_array('usebase', $this->_options);
        $disablePromotions = Mage::getStoreConfig("shipping/shippingoverride2/disable_promotions");
        $shipPriceOption = Mage::helper('shippingoverride2')->getShipPriceOption();
        $productRateActive = Mage::helper('wsacommon/shipping')->isModuleEnabled('Webshopapps_Productrate', 'carriers/productrate/active');

        $structuredItems = array();

        foreach ($items as $item) {

            $itemGroup = array();
            $weight = 0;
            $qty = 0;
            $price = 0;
            $itemWeight = $item->getWeight();

            if (!Mage::helper('wsacommon/shipping')->getItemTotals($item,
                                                                   $weight,
                                                                   $qty,
                                                                   $price,
                                                                   $useParent,
                                                                   $disablePromotions,
                                                                   $itemGroup,
                                                                   $useDiscount,
                                                                   $this->_request->getFreeShipping(),
                                                                   $useBase,
                                                                   $useTax
            )) {
                continue;
            }

            $this->_runningCartPrice += $price;

            if ($item->getParentItem() != null && $useParent
            ) {
                // must be a bundle
                $product = $item->getParentItem()->getProduct();
            } else if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE && !$useParent) {
                if ($item->getHasChildren()) {
                    foreach ($item->getChildren() as $child) {
                        $product = $child->getProduct();
                        break;
                    }
                }
            } else {
                $product = $item->getProduct();
            }

            if (!isset($product) || !is_object($product)) {
                $structuredItems[] = "";
                Mage::helper('wsalogger/log')->postCritical('shippingoverride2', 'Fatal Error', 'Item/Product is Malformed');
                break;
            }

            $attribute = Mage::getStoreConfig('shipping/shippingoverride2/attribute_filter_product');

            // changed to use another attribute name if required
            if (empty($attribute)) {
                $attribute = 'special_shipping_group';
            }

            $specialShippingGroupArray = array(); //SO-41

            if ($product->getAttributeText($attribute) == 'SKU' && in_array('use_sku', $this->_options)) { //PROD-68
                if (self::$_debug) {
                    Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'Custom Option SKU used as shipping group for item ID', $item->getId());
                }

                $specialShippingGroupArray[] = $item->getProduct()->getData('sku');//Add basic SKU to package ID array.

                if ($optionIds = $product->getCustomOption('option_ids')) {
                    foreach (explode(',', $optionIds->getValue()) as $optionId) {
                        if ($option = $product->getOptionById($optionId)) {
                            $confItemOption = $product->getCustomOption('option_' . $optionId);
                            $group = $option->groupFactory($option->getType())
                                ->setOption($option)->setListener(new Varien_Object());

                            if ($optionSku = $group->getOptionSku($confItemOption->getValue(), '-')) {
                                $specialShippingGroupArray[] = $optionSku;
                            }
                        }
                    }
                }
            } else if (in_array('group_text', $this->_options)) {
                $specialShippingGroupArray[] = $product->getData($attribute);
            } else {
                $specialShippingGroupArray[] = $product->getAttributeText($attribute);
            }

            $productOrderShippingPrice = 0;

            if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Desttype', 'shipping/desttype/active')) {
                if ($this->_request->getUpsDestType() == 'COM') {
                    $productShippingPrice = $productRateActive ? 0 : $product->getData('ship_price_com');
                } else {
                    // default to residential which is highest
                    $productShippingPrice = $productRateActive ? 0 : $product->getData('ship_price_res');
                }
            } else {
                $productShippingPrice = $productRateActive ? 0 : $product->getData('shipping_price');
                $productOrderShippingPrice = $productRateActive ? 0 : $product->getData('ship_price_order');
            }

            foreach ($specialShippingGroupArray as $specialShippingGroup) {

                if (empty($specialShippingGroup)) {
                    $specialShippingGroup = 'none';
                }

                switch ($shipPriceOption) {
                    case 'append_ship_price': // only interested in include_all group
                        $this->_addToShippingGroup('include_all', $structuredItems, $qty, $weight, $price, $itemWeight, $productShippingPrice, $productOrderShippingPrice, $itemGroup);
                        $this->_addProductShipPrice($specialShippingGroup, $structuredItems, $productShippingPrice, $productOrderShippingPrice, $qty);
                        break;
                    case 'replace_ship_price':
                        if (is_numeric($productShippingPrice)) {
                            // these items will be ignored in shipping rate calculations
                            // Note: Doesnt work if Product Rate also installed
                            $this->_addToShippingGroup('match', $structuredItems, $qty, $weight, $price, $itemWeight, $productShippingPrice, $productOrderShippingPrice, $itemGroup);
                        } else {
                            $this->_addToShippingGroup('none', $structuredItems, $qty, $weight, $price, $itemWeight, $productShippingPrice, $productOrderShippingPrice, $itemGroup);
                        }
                        break;
                    case 'ignore':
                        $productShippingPrice = 0; // ignore product shipping price, drop through to default
                        $productOrderShippingPrice = 0; // ignore product shipping price, drop through to default
                    case 'discount_csv':
                        // make the value negative
                        $productShippingPrice = $productShippingPrice <= 0 ? $productShippingPrice : 0 - $productShippingPrice;
                    default:
                        // standard behaviour using groups
                        $this->_addToShippingGroup($specialShippingGroup, $structuredItems, $qty, $weight, $price, $itemWeight, $productShippingPrice, $productOrderShippingPrice, $itemGroup);
                        if (Mage::helper('shippingoverride2')->isStarIncludeAll()) {
                            $this->_addToShippingGroup('include_all', $structuredItems, $qty, $weight, $price, $itemWeight, $productShippingPrice, $productOrderShippingPrice, $itemGroup);
                            $this->_addProductShipPrice($specialShippingGroup, $structuredItems, $productShippingPrice, $productOrderShippingPrice, $qty);
                        }
                        break;
                }
            }
        }

        if (self::$_debug) {

            Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'Shipping Price Option', $shipPriceOption);

            foreach ($structuredItems as $key => $structItem) {
                Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'Structured Item:', $key . ', Qty:' . $structItem['qty'] .
                    ', Weight:' . $structItem['weight'] . ', Price:' . $structItem['price'] . ', Item Weight:' . $structItem['item_weight'] .
                    ', Item Group Count:' . count($structItem['item_group']));

                if (array_key_exists('ship_price_order', $structItem)) {
                    Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'shipping price per order', $structItem['ship_price_order']);
                } else if (array_key_exists('ship_price_order_array', $structItem)) {
                    Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'shipping price per order', $structItem['ship_price_order_array']);
                }

                if (array_key_exists('shipping_price', $structItem)) {
                    Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'shipping price per item', $structItem['shipping_price']);
                } else if (array_key_exists('shipping_price_array', $structItem)) {
                    Mage::helper('wsalogger/log')->postDebug('shippingoverride2', 'shipping price per item', $structItem['shipping_price_array']);
                }

            }
        }

        return $structuredItems;
    }


    protected function _addToShippingGroup($specialShippingGroup, &$structuredItems, $qty, $weight, $price, $itemWeight,
                                           $productShippingPrice, $productOrderShippingPrice, $itemGroup)
    {

        if (array_key_exists($specialShippingGroup, $structuredItems)) {
            // have already got this package id
            $structuredItems[$specialShippingGroup]['qty'] = $structuredItems[$specialShippingGroup]['qty'] + $qty;
            $structuredItems[$specialShippingGroup]['weight'] = $structuredItems[$specialShippingGroup]['weight'] + $weight;
            if ($structuredItems[$specialShippingGroup]['item_weight'] < $itemWeight) {
                $structuredItems[$specialShippingGroup]['item_weight'] = $itemWeight;
            }
            $structuredItems[$specialShippingGroup]['price'] = $structuredItems[$specialShippingGroup]['price'] + $price;
            $structuredItems[$specialShippingGroup]['shipping_price'] += $productShippingPrice * $qty;
            $structuredItems[$specialShippingGroup]['ship_price_order'] += $productOrderShippingPrice;
            $structuredItems[$specialShippingGroup]['item_group'] = array_merge($itemGroup, $structuredItems[$specialShippingGroup]['item_group']);
            $structuredItems[$specialShippingGroup]['unique'] += 1;

        } else {
            $prodArray = array(
                'qty' => $qty,
                'weight' => $weight,
                'item_weight' => $itemWeight,
                'price' => $price,
                'shipping_price' => $productShippingPrice * $qty,
                'ship_price_order' => $productOrderShippingPrice,
                'item_group' => $itemGroup,
                'shipping_price_array' => array(),
                'ship_price_order_array' => array(),
                'unique' => 1);

            $structuredItems[$specialShippingGroup] = $prodArray;
        }
    }

    protected function _addProductShipPrice($specialShippingGroup, &$structuredItems, $productShippingPrice,$productOrderShippingPrice,  $qty)
    {
        if (array_key_exists($specialShippingGroup, $structuredItems['include_all']['shipping_price_array'])) {
            $structuredItems['include_all']['shipping_price_array'][$specialShippingGroup] +=
                $productShippingPrice * $qty;
            $structuredItems['include_all']['ship_price_order_array'][$specialShippingGroup] +=
                $productOrderShippingPrice;
        } else {
            $structuredItems['include_all']['shipping_price_array'] +=
                array($specialShippingGroup => $productShippingPrice * $qty);
            $structuredItems['include_all']['ship_price_order_array'] +=
                array($specialShippingGroup => $productOrderShippingPrice);
        }
    }


    /**
     * CSV Import routine
     * @param $object
     * @return unknown_type
     */
    public function uploadAndImport(Varien_Object $object)
    {
        $csvFile = $_FILES["groups"]["tmp_name"]["shippingoverride2"]["fields"]["import"]["value"];
        $csvName = $_FILES["groups"]["name"]["shippingoverride2"]["fields"]["import"]["value"];
        $usingDropship = in_array('warehouse', explode(',', Mage::getStoreConfig("shipping/shippingoverride2/ship_options"))) ? true : false;
        $usingDropship ? $offset = 1 : $offset = 0;
        $session = Mage::getSingleton('adminhtml/session');

        if (!empty($csvFile)) {

            $csv = trim(file_get_contents($csvFile));

            $table = Mage::getSingleton('core/resource')->getTableName('shippingoverride2/shippingoverride2');

            $specifiedWebsite = $object->getScopeId();
            if($specifiedWebsite == 0 ) {
                $websites = array_keys(Mage::app()->getWebsites());
            }
            else {
                $websites = array($specifiedWebsite);
            }
            foreach ($websites as $websiteId) {

                Mage::helper('wsacommon/shipping')->saveCSV($csv, $csvName, $websiteId, 'shippingoverride2');

                if (!empty($csv)) {
                    $exceptions = array();
                    $csvLines = explode("\n", $csv);
                    $csvLine = array_shift($csvLines);
                    $csvLine = $this->_getCsvValues($csvLine);
                    if (count($csvLine) < 17) {
                        $exceptions[0] = Mage::helper('shipping')->__('Invalid Shipping Override File Format');
                    }

                    $countryCodes = array();
                    $regionCodes = array();
                    foreach ($csvLines as $csvLine) {
                        $csvLine = $this->_getCsvValues($csvLine);
                        if (count($csvLine) > 0 && count($csvLine) < 17) {
                            $exceptions[0] = Mage::helper('shipping')->__('Invalid Shipping Override File Format');
                        } else {
                            $splitCountries = explode(",", trim($csvLine[0]));
                            $splitRegions = explode(",", trim($csvLine[1]));
                            foreach ($splitCountries as $country) {
                                $countryCodes[] = trim($country);
                            }
                            foreach ($splitRegions as $region) {
                                $regionCodes[] = $region;
                            }
                        }
                    }


                    if (empty($exceptions)) {
                        $data = array();
                        $countryCodesToIds = array();
                        $regionCodesToIds = array();
                        $countryCodesIso2 = array();

                        $countryCollection = Mage::getResourceModel('directory/country_collection')->addCountryCodeFilter($countryCodes)->load();
                        foreach ($countryCollection->getItems() as $country) {
                            $countryCodesToIds[$country->getData('iso3_code')] = $country->getData('country_id');
                            $countryCodesToIds[$country->getData('iso2_code')] = $country->getData('country_id');
                            $countryCodesIso2[] = $country->getData('iso2_code');
                        }

                        $regionCollection = Mage::getResourceModel('directory/region_collection')
                            ->addRegionCodeFilter($regionCodes)
                            ->addCountryFilter($countryCodesIso2)
                            ->load();


                        foreach ($regionCollection->getItems() as $region) {
                            $regionCodesToIds[$countryCodesToIds[$region->getData('country_id')]][$region->getData('code')][] = $region->getData('region_id');
                        }

                        foreach ($csvLines as $k => $csvLine) {
                            $csvLine = $this->_getCsvValues($csvLine);
                            $splitCountries = explode(",", trim($csvLine[0]));
                            $splitPostcodes = explode(",", trim($csvLine[3]));
                            $splitRegions = explode(",", trim($csvLine[1]));
                            $customerGroups = explode(",", trim($csvLine[12+$offset]));

                            if ($csvLine[2] == '*' || $csvLine[2] == '') {
                                $city = '';
                            } else {
                                $city = $csvLine[2];
                            }

                            if ($csvLine[4] == '*' || $csvLine[4] == '') {
                                $zip_to = '';
                            } else {
                                $zip_to = $csvLine[4];
                            }


                            if ($csvLine[5] == '*' || $csvLine[5] == '') {
                                $special_shipping_group = '';
                            } else {
                                $special_shipping_group = $csvLine[5];
                            }

                            $usingDropship ? $splitVendor = explode(",", trim($csvLine[6])) : $splitVendor = array('*');


                            $lineNo = 6+$offset;
                            if ($csvLine[$lineNo] == '*' || $csvLine[$lineNo] == '') {
                                $weight_from = -1;
                            } else if (!is_numeric($csvLine[$lineNo])) {
                                $exceptions[] = Mage::helper('shipping')->__('Invalid weight From "%s" in the Row #%s', $csvLine[$lineNo], ($k + 1));
                            } else {
                                $weight_from = (float)$csvLine[$lineNo];
                            }

                            $lineNo = 7+$offset;

                            if ($csvLine[$lineNo] == '*' || $csvLine[$lineNo] == '') {
                                $weight_to = 10000000;
                            } else if (!$this->_isPositiveDecimalNumber($csvLine[$lineNo])) {
                                $exceptions[] = Mage::helper('shipping')->__('Invalid weight To "%s" in the Row #%s', $csvLine[$lineNo], ($k + 1));
                            } else {
                                $weight_to = (float)$csvLine[$lineNo];
                            }

                            $lineNo = 8+$offset;
                            if ($csvLine[$lineNo] == '*' || $csvLine[$lineNo] == '') {
                                $price_from = -1;
                            } else if (!is_numeric($csvLine[$lineNo])) {
                                $exceptions[] = Mage::helper('shipping')->__('Invalid price From "%s" in the Row #%s', $csvLine[$lineNo], ($k + 1));
                            } else {
                                $price_from = (float)$csvLine[$lineNo];
                            }

                            $lineNo = 9+$offset;
                            if ($csvLine[$lineNo] == '*' || $csvLine[$lineNo] == '') {
                                $price_to = 10000000;
                            } else if (!$this->_isPositiveDecimalNumber($csvLine[$lineNo])) {
                                $exceptions[] = Mage::helper('shipping')->__('Invalid price To "%s" in the Row #%s', $csvLine[$lineNo], ($k + 1));
                            } else {
                                $price_to = (float)$csvLine[$lineNo];
                            }

                            $lineNo=10+$offset;
                            if ($csvLine[$lineNo] == '*' || $csvLine[$lineNo] == '') {
                                $item_from = 0;
                            } else if (!$this->_isPositiveDecimalNumber($csvLine[$lineNo])) {
                                $exceptions[] = Mage::helper('shipping')->__('Invalid item From "%s" in the Row #%s', $csvLine[$lineNo], ($k + 1));
                            } else {
                                $item_from = (float)$csvLine[$lineNo];
                            }

                            $lineNo=11+$offset;
                            if ($csvLine[$lineNo] == '*' || $csvLine[$lineNo] == '') {
                                $item_to = 10000000;
                            } else if (!$this->_isPositiveDecimalNumber($csvLine[$lineNo])) {
                                $exceptions[] = Mage::helper('shipping')->__('Invalid item To "%s" in the Row #%s', $csvLine[$lineNo], ($k + 1));
                            } else {
                                $item_to = (float)$csvLine[$lineNo];
                            }

                            $lineNo=14+$offset;
                            if ($csvLine[$lineNo] == '*' || $csvLine[$lineNo] == '') {
                                $percentage = '0';
                            } else {
                                $percentage = $csvLine[$lineNo];
                            }

                            if (count($csvLine) >= 18+$offset) {
                                if ($csvLine[17+$offset] == '*' || $csvLine[17+$offset] == '') {
                                    $rules = '';
                                } else {
                                    $rules = $csvLine[17+$offset];
                                }
                            } else {
                                $rules = '';
                            }

                            if (count($csvLine) > 18+$offset) {
                                if ($csvLine[18+$offset] == '*' || $csvLine[18+$offset] == '') {
                                    $item_weight_from = -1;
                                } else if (!$this->_isPositiveDecimalNumber($csvLine[18+$offset])) {
                                    $exceptions[] = Mage::helper('shipping')->__('Invalid weight From "%s" in the Row #%s', $csvLine[18+$offset], ($k + 1));
                                } else {
                                    $item_weight_from = (float)$csvLine[18+$offset];
                                }

                                if ($csvLine[19+$offset] == '*' || $csvLine[19+$offset] == '') {
                                    $item_weight_to = 10000000;
                                } else if (!$this->_isPositiveDecimalNumber($csvLine[19])) {
                                    $exceptions[] = Mage::helper('shipping')->__('Invalid weight To "%s" in the Row #%s', $csvLine[19+$offset], ($k + 1));
                                } else {
                                    $item_weight_to = (float)$csvLine[19+$offset];
                                }
                            } else {
                                $item_weight_from = -1;
                                $item_weight_to = 10000000;
                            }

                            foreach ($customerGroups as $customer_group) {

                                if ($customer_group == '*') {
                                    $customer_group = '';
                                }

                                foreach ($splitVendor as $vendorId) {

                                    if ($vendorId == '*') {
                                        $vendorId = '';
                                    } else {
                                        if (!$this->_isPositiveDecimalNumber($vendorId)) {
                                            $exceptions[] = Mage::helper('shipping')->__('Invalid Vendor ID "%s" in the Row #%s',
                                                $csvLine[6], ($k + 1));
                                            break;
                                        } else {
                                            $vendorId = trim($vendorId);
                                        }
                                    }

                                    foreach ($splitCountries as $country) {

                                        $country = trim($country);

                                        if (empty($countryCodesToIds) || !array_key_exists($country, $countryCodesToIds)) {
                                            $countryId = '0';
                                            if ($country != '*' && $country != '') {
                                                $exceptions[] = Mage::helper('shipping')->__('Invalid Country "%s" in the Row #%s', $country, ($k + 1));
                                                break;
                                            }
                                        } else {
                                            $countryId = $countryCodesToIds[$country];
                                        }

                                        foreach ($splitRegions as $region) {

                                            if (!isset($countryCodesToIds[$country])
                                                || !isset($regionCodesToIds[$countryCodesToIds[$country]])
                                                || !array_key_exists($region, $regionCodesToIds[$countryCodesToIds[$country]])
                                            ) {
                                                $regionIds = array('0');
                                                if ($region != '*' && $region != '') {
                                                    $exceptions[] = Mage::helper('shipping')->__('Invalid Region/State "%s" in the Row #%s', $region, ($k + 1));
                                                    break;
                                                }
                                            } else {
                                                $regionIds = $regionCodesToIds[$countryCodesToIds[$country]][$region];
                                            }


                                            foreach ($regionIds as $regionId) {
                                                foreach ($splitPostcodes as $postcode) {
                                                    if ($postcode == '*' || $postcode == '') {
                                                        $zip = '';
                                                        $new_zip_to = '';
                                                    } else {
                                                        $zip_str = explode("-", $postcode);
                                                        if (count($zip_str) != 2) {
                                                            $zip = trim($postcode);
                                                            if (ctype_digit($postcode) && trim($zip_to) == '') {
                                                                $new_zip_to = trim($postcode);
                                                            } else $new_zip_to = $zip_to;
                                                        } else {
                                                            $zip = trim($zip_str[0]);
                                                            $new_zip_to = trim($zip_str[1]);
                                                        }
                                                    }
                                                    $data[] = array('website_id' => $websiteId, 'dest_country_id' => $countryId, 'dest_region_id' => $regionId,
                                                        'dest_city' => $city, 'dest_zip' => $zip, 'dest_zip_to' => $new_zip_to,
                                                        'special_shipping_group' => $special_shipping_group,
                                                        'weight_from_value' => $weight_from, 'weight_to_value' => $weight_to,
                                                        'vendor' => $vendorId,
                                                        'price_from_value' => $price_from, 'price_to_value' => $price_to,
                                                        'item_from_value' => $item_from, 'item_to_value' => $item_to,
                                                        'customer_group' => $customer_group,
                                                        'price' => $csvLine[13+$offset], 'percentage' => $percentage, 'delivery_type' => $csvLine[15+$offset], 'algorithm' => $csvLine[16+$offset],
                                                        'rules' => $rules, 'item_weight_from_value' => $item_weight_from, 'item_weight_to_value' => $item_weight_to);
                                                    $dataDetails[] = array('country' => $country, 'region' => $region);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (empty($exceptions)) {
                        $connection = $this->_getWriteAdapter();

                        $condition = array(
                            $connection->quoteInto('website_id = ?', $websiteId),
                        );
                        $connection->delete($table, $condition);


                        foreach ($data as $dataLine) {
                            try {
                                $connection->insert($table, $dataLine);
                            } catch (Exception $e) {
                                $exceptions[] = $e;
                            }
                        }
                        Mage::helper('wsacommon/shipping')->updateStatus($session, count($data));
                    }
                    if (!empty($exceptions)) {
                        throw new Exception("\n" . implode("\n", $exceptions));
                    }
                }
            }
        }
    }

    private function _getCsvValues($string, $separator = ",")
    {
        $elements = explode($separator, trim($string));
        for ($i = 0; $i < count($elements); $i++) {
            $nquotes = substr_count($elements[$i], '"');
            if ($nquotes % 2 == 1) {
                for ($j = $i + 1; $j < count($elements); $j++) {
                    if (substr_count($elements[$j], '"') > 0) {
                        // Put the quoted string's pieces back together again
                        array_splice($elements, $i, $j - $i + 1, implode($separator, array_slice($elements, $i, $j - $i + 1)));
                        break;
                    }
                }
            }
            if ($nquotes > 0) {
                // Remove first and last quotes, then merge pairs of quotes
                $qstr =& $elements[$i];
                $qstr = substr_replace($qstr, '', strpos($qstr, '"'), 1);
                $qstr = substr_replace($qstr, '', strrpos($qstr, '"'), 1);
                $qstr = str_replace('""', '"', $qstr);
            }
            $elements[$i] = trim($elements[$i]);
        }
        return $elements;
    }

    private function _isPositiveDecimalNumber($n)
    {
        return preg_match("/^[0-9]+(\.[0-9]*)?$/", $n);
    }


}
