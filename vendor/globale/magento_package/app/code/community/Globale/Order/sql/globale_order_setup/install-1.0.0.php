<?php
/**
 * Installation script for the Global-e orders
 */

/** @var Mage_Core_Model_Resource_Setup $Installer */
$Installer = $this;
$Installer->startSetup();

/**
 * Create order details
 * Holds all Globale orders
 */
//<editor-fold desc="globale_orders">
$Installer->getConnection()->dropTable($Installer->getTable('globale_order/orders'));
$GlobaleOrders = $Installer->getConnection()->newTable($Installer->getTable('globale_order/orders'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'auto_increment' => true,
        'identity'       => true,
        'unsigned'       => true,
        'nullable'       => false,
        'primary'        => true,
    ),  'id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'       => true,
        'nullable'       => false,
    ), 'order_id')
    ->addColumn('globale_order_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'       => true,
    ),  'globale_order_id')
    ->addColumn('order_status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'       => false,
        'default'        => '0'
    ),  'order_status')
    ->addColumn('created_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'       => true,
    ),  'created_time')
    ->addColumn('update_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'       => true,
    ),  'update_time')
    ->addIndex(
        $Installer->getIdxName(
            $Installer->getTable('globale_orders'),
            'order_id',
            'order_id_UNIQUE',
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            'order_id',
            'order_id_UNIQUE',
            array(
                'type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            )
        );
$Installer->getConnection()->createTable($GlobaleOrders);
//</editor-fold>

/**
 * Create order details
 * Holds all the order information
 */
//<editor-fold desc="globale_order_details">
$Installer->getConnection()->dropTable($Installer->getTable('globale_order/details'));
$GlobaleOrderDetails = $Installer->getConnection()->newTable($Installer->getTable('globale_order/details'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'auto_increment' => true,
        'identity'       => true,
        'unsigned'       => true,
        'nullable'       => false,
        'primary'        => true,
    ),  'id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'       => true,
        'nullable'       => false,
    ), 'order_id')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'       => true,
        'nullable'       => false,
    ),  'user_id')
    ->addColumn('quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'       => true,
        'nullable'       => false,
    ),  'quote_id')
    ->addColumn('allow_mails_from_merchant', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'       => false,
        'default'        => false
    ),  'allow_mails_from_merchant')
    ->addColumn('cart_hash', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        'nullable'       => true,
    ),  'cart_hash')
    ->addColumn('clear_cart', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'       => false,
        'default'        => false
    ),  'clear_cart')
    ->addColumn('currency_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 3, array(
        'nullable'       => true,
    ),  'currency_code')
    ->addColumn('customer_comments', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'       => true,
    ),  'customer_comments')
    ->addColumn('do_not_charge_vat', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'       => false,
        'default'        => false
    ),  'do_not_charge_vat')
    ->addColumn('email_address', Varien_Db_Ddl_Table::TYPE_VARCHAR, 40, array(
        'nullable'       => true,
    ),  'email_address')
    ->addColumn('free_shipping_coupon_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'       => true,
    ),  'free_shipping_coupon_code')
    ->addColumn('is_end_customer_primary', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'       => false,
        'default'        => false
    ),  'is_end_customer_primary')
    ->addColumn('is_split_order', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'       => false,
        'default'        => false
    ),  'is_split_order')
    ->addColumn('is_free_shipping', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'       => false,
        'default'        => false
    ),  'is_free_shipping')
    ->addColumn('rounding_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'       => true,
    ),  'rounding_rate')
    ->addColumn('same_day_dispatch', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'       => false,
        'default'        => false
    ),  'same_day_dispatch')
    ->addColumn('same_day_dispatch_cost', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'       => true,
    ),  'same_day_dispatch_cost')
    ->addColumn('send_confirmation', Varien_Db_Ddl_Table::TYPE_BOOLEAN, '12,4', array(
        'nullable'       => false,
        'default'        => false
    ),  'send_confirmation')
    ->addColumn('ship_to_store_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'       => true,
    ),  'ship_to_store_code')
    ->addColumn('total_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'       => true,
    ),  'total_price')
    ->addColumn('transaction_total_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'       => true,
    ),  'transaction_total_price')
    ->addColumn('transaction_currency_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
        'nullable'       => true,
    ),  'transaction_currency_code')
    ->addColumn('url_parameters', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'       => true,
    ),  'url_parameters');
$Installer->getConnection()->createTable($GlobaleOrderDetails);
//</editor-fold>

/**
 * Create order addresses
 * Holds all the customer addresses details by type: shipping/billing and is_primery: true/false primery/secondary
 */
//<editor-fold desc="globale_order_addresses">
$Installer->getConnection()->dropTable($Installer->getTable('globale_order/addresses'));
$GlobaleOrderAddresses = $Installer->getConnection()->newTable($Installer->getTable('globale_order/addresses'))
    ->addColumn('address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'auto_increment' => true,
        'unsigned'       => true,
        'nullable'       => false,
        'primary'        => true,
    ),  'address_id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'       => true,
        'nullable'       => false,
    ), 'order_id')
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'       => true,
        'nullable'       => false,
    ),  'type')
    ->addColumn('is_primery', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'       => false,
        'default'        => false
    ),  'is_primery')
    ->addColumn('address1', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'       => true
    ),  'address1')
    ->addColumn('address2', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'       => true
    ),  'address2')
    ->addColumn('company', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'       => true
    ),  'company')
    ->addColumn('country_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 3, array(
        'nullable'       => true
    ),  'country_code')
    ->addColumn('country_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true
    ),  'country_name')
    ->addColumn('city', Varien_Db_Ddl_Table::TYPE_BOOLEAN, 32, array(
        'nullable'       => true
    ),  'city')
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 40, array(
        'nullable'       => true,
    ),  'email')
    ->addColumn('fax', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true,
    ),  'fax')
    ->addColumn('first_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'       => true
    ),  'first_name')
    ->addColumn('last_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'       => true
    ),  'last_name')
    ->addColumn('middle_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'       => true
    ),  'middle_name')
    ->addColumn('phone1', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true
    ),  'phone1')
    ->addColumn('phone2', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true
    ),  'phone2')
    ->addColumn('salutation', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'       => true
    ),  'salutation')
    ->addColumn('state_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 3, array(
        'nullable'       => true
    ),  'state_code')
    ->addColumn('state_or_province', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true
    ),  'state_or_province')
    ->addColumn('zip', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true
    ),  'zip')
    ->addForeignKey(
        $Installer->getFkName($Installer->getTable('globale_order_addresses'),
                              'order_id',
            $Installer->getTable('globale_orders'),
                              'order_id'
        ),
        'order_id',
        $Installer->getTable('globale_orders'),
        'order_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );
$Installer->getConnection()->createTable($GlobaleOrderAddresses);
//</editor-fold>

/**
 * Create order shipping
 * Holds all the shipping details of the order
 */
//<editor-fold desc="globale_order_shipping">
$Installer->getConnection()->dropTable($Installer->getTable('globale_order/shipping'));
$GlobaleOrderShipping = $Installer->getConnection()->newTable($Installer->getTable('globale_order/shipping'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'auto_increment' => true,
        'unsigned'       => true,
        'nullable'       => false,
        'primary'        => true,
    ),  'id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'       => true,
        'nullable'       => false,
    ), 'order_id')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'       => true,
        'nullable'       => false,
    ),  'user_id')
    ->addColumn('order_tracking_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'       => true
    ),  'order_tracking_number')
    ->addColumn('order_tracking_url', Varien_Db_Ddl_Table::TYPE_VARCHAR, 120, array(
        'nullable'       => true
    ),  'order_tracking_url')
    ->addColumn('order_waybill_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'       => true
    ),  'order_waybill_number')
    ->addColumn('shipping_method_type_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 120, array(
        'nullable'       => true
    ),  'shipping_method_type_code')
    ->addColumn('shipping_method_type_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true
    ),  'shipping_method_type_name')
    ->addColumn('shipping_method_status_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 120, array(
        'nullable'       => true
    ),  'shipping_method_status_name')
    ->addColumn('shipping_method_status_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 120, array(
        'nullable'       => true
    ),  'shipping_method_status_code')
    ->addColumn('shipping_method_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true,
    ),  'shipping_method_code')
    ->addColumn('total_duties_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'       => true,
    ),  'total_duties_price')
    ->addColumn('total_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'       => true
    ),  'total_price')
    ->addColumn('transaction_currency_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 3, array(
        'nullable'       => true
    ),  'transaction_currency_code');
$Installer->getConnection()->createTable($GlobaleOrderShipping);
//</editor-fold>

/**
 * Create order payments table
 * Holds all the payments details of the order
 */
//<editor-fold desc="globale_order_payment">
$Installer->getConnection()->dropTable($Installer->getTable('globale_order/payment'));
$GlobaleOrderPayment = $Installer->getConnection()->newTable($Installer->getTable('globale_order/payment'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'auto_increment' => true,
        'unsigned'       => true,
        'nullable'       => false,
        'primary'        => true,
    ),  'id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'       => true,
        'nullable'       => false,
    ), 'order_id')
    ->addColumn('address1', Varien_Db_Ddl_Table::TYPE_INTEGER, 255, array(
        'nullable'       => true
    ),  'address1')
    ->addColumn('address2', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'       => true
    ),  'address2')
    ->addColumn('card_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true
    ),  'card_number')
    ->addColumn('cvv_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, 5, array(
        'nullable'       => true
    ),  'cvv_number')
    ->addColumn('country_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true
    ),  'country_name')
    ->addColumn('country_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 3, array(
        'nullable'       => true
    ),  'country_code')
    ->addColumn('country_code3', Varien_Db_Ddl_Table::TYPE_VARCHAR, 3, array(
        'nullable'       => true
    ),  'country_code3')
    ->addColumn('city', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true
    ),  'city')
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 40, array(
        'nullable'       => true,
    ),  'email')
    ->addColumn('expiration_date', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'       => true,
    ),  'expiration_date')
    ->addColumn('fax', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'       => true
    ),  'fax')
    ->addColumn('owner_first_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'       => true
    ),  'owner_first_name')
    ->addColumn('owner_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        'nullable'       => true
    ),  'owner_name')
    ->addColumn('payment_method_type_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true
    ),  'payment_method_type_code')
    ->addColumn('payment_method_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true
    ),  'payment_method_name')
    ->addColumn('payment_method_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true
    ),  'payment_method_code')
    ->addColumn('phone1', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true
    ),  'phone1')
    ->addColumn('phone2', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true
    ),  'phone2')
    ->addColumn('state_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 10, array(
        'nullable'       => true
    ),  'state_code')
    ->addColumn('state_or_province', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true
    ),  'state_or_province')
    ->addColumn('zip', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true
    ),  'zip');
$Installer->getConnection()->createTable($GlobaleOrderPayment);
//</editor-fold>

/**
 * Create order products table
 * Holds all the products details of the order
 */
//<editor-fold desc="globale_order_products">
$Installer->getConnection()->dropTable($Installer->getTable('globale_order/products'));
$GlobaleOrderProducts = $Installer->getConnection()->newTable($Installer->getTable('globale_order/products'))
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'auto_increment' => true,
        'unsigned'       => true,
        'nullable'       => false,
        'primary'        => true,
    ),  'product_id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'       => true,
        'nullable'       => false,
    ), 'order_id')
    ->addColumn('back_order_date', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'       => true
    ),  'back_order_date')
    ->addColumn('cart_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'       => true,
        'nullable'       => true
    ),  'cart_item_id')
    ->addColumn('cart_item_option_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'       => true,
        'nullable'       => true
    ),  'cart_item_option_id')
    ->addColumn('gift_message', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'       => true
    ),  'gift_message')
    ->addColumn('handling_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 55, array(
        'nullable'       => true
    ),  'handling_code')
    ->addColumn('is_back_ordered', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'       => false,
        'default'        => false
    ),  'is_back_ordered')
    ->addColumn('international_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'       => true
    ),  'international_price')
    ->addColumn('parent_cart_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'       => true,
        'nullable'       => true
    ),  'parent_cart_item_id')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'       => true
    ),  'price')
    ->addColumn('price_before_rounding_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'       => true
    ),  'price_before_rounding_rate')
    ->addColumn('price_before_globale_discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'       => true,
    ),  'price_before_globale_discount')
    ->addColumn('quantity', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'       => true,
    ),  'quantity')
    ->addColumn('rounding_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'       => true
    ),  'rounding_rate')
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
        'nullable'       => true
    ),  'sku')
    ->addColumn('vat_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'       => true
    ),  'vat_rate')
    ->addForeignKey(
        $Installer->getFkName($Installer->getTable('globale_order_products'),
            'order_id',
            $Installer->getTable('globale_orders'),
            'order_id'
        ),
        'order_id',
        $Installer->getTable('globale_orders'),
        'order_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );
$Installer->getConnection()->createTable($GlobaleOrderProducts);
//</editor-fold>

/**
 * Create order discounts table
 * Holds all discounts information of the order
 */
//<editor-fold desc="globale_order_discounts">
$Installer->getConnection()->dropTable($Installer->getTable('globale_order/discounts'));
$GlobaleOrderDiscounts = $Installer->getConnection()->newTable($Installer->getTable('globale_order/discounts'))
    ->addColumn('discount_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'auto_increment' => true,
        'unsigned'       => true,
        'nullable'       => false,
        'primary'        => true,
    ),  'discount_id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'       => true,
        'nullable'       => false,
    ), 'order_id')
    ->addColumn('coupon_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'       => true
    ),  'coupon_code')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'       => true
    ),  'description')
    ->addColumn('discount_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'       => true
    ),  'discount_code')
    ->addColumn('discount_type', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'       => true
    ),  'discount_type')
    ->addColumn('international_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'       => true
    ),  'international_price')
    ->addColumn('local_vat_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'       => true
    ),  'local_vat_rate')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'       => true
    ),  'name')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'       => true
    ),  'price')
    ->addColumn('vat_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'       => true
    ),  'vat_rate')
    ->addForeignKey(
        $Installer->getFkName($Installer->getTable('globale_order_discounts'),
            'order_id',
            $Installer->getTable('globale_orders'),
            'order_id'
        ),
        'order_id',
        $Installer->getTable('globale_orders'),
        'order_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );
$Installer->getConnection()->createTable($GlobaleOrderDiscounts);
//</editor-fold>

$Installer->endSetup();