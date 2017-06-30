<?php
/**
 * Created by PhpStorm.
 * User: arne
 * Date: 2011-jan-03
 * Time: 16:15:37
 * To change this template use File | Settings | File Templates.
 */

class Icommerce_Sales {

    // Function to create a new attribute on order, for 1.3/1.
    static function checkCreateOrderAttribute( $acode, $type, $sub_table=null ){
        $flat_table = "sales_flat_order";
        if( $sub_table ){
            $flat_table .= "_".$sub_table;
        }
        // New Magento (1.4/1.8/1.9/...?
        if( Icommerce_Db::tableExists($flat_table) ){
            if( !Icommerce_Db::columnExists($flat_table, $acode) ){
                $len = 32767;
                Icommerce_Db::addColumn($flat_table,$acode,$type,$len);
            }
        } else {
            // Order is still EAV
            $eav_type = "order";
            if( $sub_table ){
                $eav_type .= "_".$sub_table;
            }
            if( !Icommerce_Eav::getAttributeId($acode,$eav_type) ){
                $etid = Icommerce_Eav::getEntityTypeId( $eav_type );
                $template_attr = Icommerce_Db::getValue( "SELECT attribute_code FROM eav_attribute WHERE entity_type_id=$etid AND backend_type='$type';" );
                if( !$template_attr ){
                    throw new Exception( "Icommerce_Sales::createOrderAttribute - No template attribute available: $acode / $type" );
                }
                Icommerce_Eav::createAttribute( $acode, $eav_type, $template_attr );
            }
        }
    }

    static function getLastOrderObject(){
        static $st_last_order;
        if( !$st_last_order ){
            $lastOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
            $st_last_order = Mage::getSingleton('sales/order')->load( $lastOrderId );
        }
        return $st_last_order;
    }

    /* 
     * Get order information such as:
     * $_orderId = $order['increment_id']; // Order number in Magento
     * $_grand = $order['grand_total']; // Total order sum (inc. tax, shipping, payment method cost)
     * $_sub = $order['subtotal']; // Total sum of all products (excl. tax and incl. discounts)
     */
    static function getLastOrder()
    {
        return self::getLastOrderObject()->getData();
    }

    /*
    * Get order address information such as:
    * $street = $address['street'];
    * $postcode = $address['postcode'];
    * $city = $address['city'];
    * $country = $address['country_id'];  // SE, FR, DK..
    */
    static function getLastOrderAddress()
    {
        return self::getLastOrderObject()->getBillingAddress();
    }

    /**
	 * Returns option ID if it exists on given attribute. If option not yet exists, it creates the option.
	 * @param $order The order
     * @param $shipment The shipment
     * @return boolean True if shipment is partial (not whole order)
	 */
    static function isPartialShipment( $order, $shipment ){
        $o_items = $order->getAllItems();
        $s_items = $shipment->getAllItems();
        $pid_qtys = array();
        foreach( $o_items as $oi ){
            if( $qty=$oi->getData("qty_ordered") ){
                $pid_qtys[$oi->getData("product_id")] = $qty;
            }
        }

        foreach( $s_items as $si ){
            if( $qty=$si->getData("qty") ){
                $pid = $si->getData("product_id");
                if( isset($pid_qtys[$pid]) ){
                    $pid_qtys[$pid] -= $qty;
                }
            }
        }

        foreach( $pid_qtys as $pid => $qty ){
            if( $qty>0 ) return true;
        }

        return false;
    }

}
