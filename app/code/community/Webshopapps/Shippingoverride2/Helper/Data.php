<?php
/**
 * WebShopApps
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
 * @category    WebShopApps
 * @package     WebShopApps WsaLogger
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Webshopapps_Shippingoverride2_Helper_Data extends Mage_Core_Helper_Abstract
{
	private $ratesExist = FALSE;

    private static $_starIncludeAll;
    private static $_debug;
    private static $_shipPriceOption;
    private static $_useParent;

	/**
	 *
	 * Do rates exist for any carrier?
	 * @return boolean
	 */
	public function getRatesExist(){
		return $this->ratesExist;
	}
	/**
	 *
	 * Set it rates exist for current carrier.
	 * @param boolean $switch
	 */
	public function setRatesExist($switch){
		if(!$this->ratesExist){
			$this->ratesExist = $switch;
		}
	}


    public static function isDebug()
    {
        if (self::$_debug==NULL) {
            self::$_debug = Mage::helper('wsalogger')->isDebug('Webshopapps_Shippingoverride2');
        }
        return self::$_debug;
    }


    public static function getShipPriceOption()
    {
        if (self::$_shipPriceOption==NULL) {
            self::$_shipPriceOption = Mage::getStoreConfig('shipping/shippingoverride2/ship_price');
        }
        return self::$_shipPriceOption;
    }




    public static function isStarIncludeAll()
    {
        if (self::$_starIncludeAll==NULL) {
            self::$_starIncludeAll = Mage::getStoreConfig('shipping/shippingoverride2/star_include_all');
        }
        return self::$_starIncludeAll;
    }

    public static function useParent()
    {
        if (self::$_useParent==NULL) {
            self::$_useParent = Mage::getStoreConfig('shipping/shippingoverride2/use_parent');
        }
        return self::$_useParent;
    }

    public function getWarehouseId($request) {
        $warehouseId = null;

        if(Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Dropship') ||
            Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Shipmanager')){

            $allItems =$request->getAllItems();

            $warehouseDetails = Mage::helper('dropcommon/shipcalculate')->getWarehouseDetails(
                                                                                         $request->getDestCountryId(),
                                                                                         $request->getDestRegionCode(),
                                                                                         $request->getDestPostcode(),
                                                                                         $allItems
            );

            foreach ($warehouseDetails as $warehouse=>$whItems) {
                $warehouseId = $warehouse;
            }
        }

        return $warehouseId;
    }

    public function getResCom($request)
    {
        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Wsafreightcommon', 'shipping/wsafreightcommon/active')) {
            $shiptoType = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getData('shipto_type');
            $destType = Mage::getSingleton('wsafreightcommon/freightCommon')->getCode('dest_type', $shiptoType);
        } else {
            $destType = null;
        }

        if ($destType != null) {
            $resCom = $destType;
        } else {
            $resCom = $request->getUpsDestType();
        }

        return $resCom;
    }

}