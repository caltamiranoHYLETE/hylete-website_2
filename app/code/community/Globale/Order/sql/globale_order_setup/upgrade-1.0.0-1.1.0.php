<?php
/**
 * Upgrade script for the Global-e orders data
 */

/** @var Mage_Core_Model_Resource_Setup $Installer */
$Installer = $this;
$Installer->startSetup();

//<editor-fold desc="globale_order_products">

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/products'),
    'back_order_date',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_DATETIME,
        'comment' => 'Product.BackOrderDate'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/products'),
    'cart_item_id',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment' => 'Product.CartItemId',
        'unsigned'       => true
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/products'),
    'cart_item_option_id',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment' => 'Product.CartItemOptionId',
        'unsigned'       => true
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/products'),
    'gift_message',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'comment' => 'Product.GiftMessage'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/products'),
    'handling_code',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 55,
        'comment' => 'Product.HandlingCode'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/products'),
    'is_back_ordered',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'comment' => 'Product.IsBackOrdered',
        'nullable'       => false,
        'default'        => false
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/products'),
    'international_price',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'  => '12,4',
        'comment' => 'Product.InternationalPrice'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/products'),
    'parent_cart_item_id',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment' => 'Product.ParentCartItemId',
        'unsigned'       => true
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/products'),
    'price',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'  => '12,4',
        'comment' => 'Product.Price'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/products'),
    'price_before_rounding_rate',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'  => '12,4',
        'comment' => 'Product.PriceBeforeRoundingRate'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/products'),
    'price_before_globale_discount',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'  => '12,4',
        'comment' => 'Product.PriceBeforeGlobalEDiscount'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/products'),
    'quantity',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment' => 'Product.Quantity'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/products'),
    'rounding_rate',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'  => '12,4',
        'comment' => 'Product.RoundingRate'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/products'),
    'sku',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 64,
        'comment' => 'Product.Sku'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/products'),
    'vat_rate',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'  => '12,4',
        'comment' => 'Product.VATRate'
    )
);

//</editor-fold>

//<editor-fold desc="globale_order_details">

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'user_id',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment'  => 'UserId',
        'unsigned' => true,
        'nullable' => false
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'quote_id',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment'  => 'CartId',
        'unsigned' => true,
        'nullable' => false
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'allow_mails_from_merchant',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'comment'  => 'AllowMailsFromMerchant',
        'default'  => false,
        'nullable' => false
    )
);
$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'cart_hash',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 50,
        'comment'  => 'CartHash'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'clear_cart',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'comment'  => 'ClearCart',
        'nullable' => false,
        'default'  => false
    )
);

$Installer->getConnection()->changeColumn(
    $Installer->getTable('globale_order/details'),
    'currency_code',
    'base_currency_code',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 3,
        'comment'  => 'CurrencyCode'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'customer_comments',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'comment'  => 'CustomerComments'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'do_not_charge_vat',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'comment'  => 'DoNotChargeVAT',
        'nullable' => false,
        'default'  => false
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'email_address',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 100,
        'comment'  => 'Customer.EmailAddress'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'free_shipping_coupon_code',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 100,
        'comment'  => 'FreeShippingCouponCode'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'is_end_customer_primary',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'comment'  => 'Customer.IsEndCustomerPrimary',
        'nullable' => false,
        'default'  => false
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'is_split_order',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'comment'  => 'IsSplitOrder',
        'nullable' => false,
        'default'  => false
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'is_free_shipping',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'comment'  => 'IsFreeShipping',
        'nullable' => false,
        'default'  => false
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'rounding_rate',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'comment'  => 'RoundingRate',
        'default'  => false,
        'nullable' => false
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'same_day_dispatch',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'comment'  => 'SameDayDispatch',
        'nullable' => false,
        'default'  => false
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'send_confirmation',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'comment'  => 'Customer.SendConfirmation',
        'nullable' => false,
        'default'  => false
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'ship_to_store_code',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 255,
        'comment'  => 'ShipToStoreCode'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'total_price',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'comment'  => 'InternationalDetails.TotalPrice',
        'default'  => false,
        'nullable' => false
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'transaction_total_price',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'comment'  => 'InternationalDetails.TransactionTotalPrice',
        'default'  => false,
        'nullable' => false
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'transaction_currency_code',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 3,
        'comment'  => 'InternationalDetails.TransactionCurrencyCode'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/details'),
    'url_parameters',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 255,
        'comment'  => 'UrlParameters'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'loyalty_points_spent',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'comment'  => 'LoyaltyPointsSpent',
        'default'  => 0,
        'nullable' => false
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'loyalty_points_earned',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'comment'  => 'LoyaltyPointsEarned',
        'default'  => 0,
        'nullable' => false
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'loyalty_code',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 100,
        'comment'  => 'LoyaltyCode'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'free_shipping_coupon_code',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 100,
        'comment'  => 'FreeShippingCouponCode'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'original_merchant_total_products_discounted_price',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'comment'  => 'OriginalMerchantTotalProductsDiscountedPrice',
        'default'  => 0,
        'nullable' => false
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'ot_voucher_code',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 100,
        'comment'  => 'OTVoucherCode'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'ot_voucher_amount',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 100,
        'comment'  => 'OTVoucherAmount'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'ot_voucher_currency_code',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 100,
        'comment'  => 'OTVoucherCurrencyCode'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'status_code',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 100,
        'comment'  => 'StatusCode'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'price_coefficient_rate',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'comment'  => 'PriceCoefficientRate',
        'default'  => 0,
        'nullable' => false
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'web_store_code',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 100,
        'comment'  => 'WebStoreCode'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'discounted_shipping_price',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'comment'  => 'DiscountedShippingPrice',
        'default'  => 0,
        'nullable' => false
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'customer_currency_code',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 100,
        'comment'  => 'InternationalDetails.CurrencyCode'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'same_day_dispatch_cost',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'comment'  => 'InternationalDetails.SameDayDispatchCost',
        'default'  => 0,
        'nullable' => false
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'total_ccf_price',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'comment'  => 'InternationalDetails.TotalCCFPrice',
        'default'  => 0,
        'nullable' => false
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'duties_guaranteed',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'comment'  => 'InternationalDetails.DutiesGuaranteed',
        'nullable' => false,
        'default'  => false
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'delivery_days_from',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'length'   => 3,
        'comment'  => 'InternationalDetails.DeliveryDaysFrom',
        'unsigned' => true
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'delivery_days_to',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'length'   => 3,
        'comment'  => 'InternationalDetails.DeliveryDaysTo',
        'unsigned' => true
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'consignment_fee',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'comment'  => 'InternationalDetails.ConsignmentFee',
        'default'  => 0,
        'nullable' => false
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'size_overcharge_value',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'comment'  => 'InternationalDetails.SizeOverchargeValue',
        'default'  => 0,
        'nullable' => false
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'remote_area_surcharge',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'comment'  => 'InternationalDetails.RemoteAreaSurcharge',
        'default'  => 0,
        'nullable' => false
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/details'),
    'total_duties_price',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'comment'  => 'InternationalDetails.TotalDutiesPrice',
        'default'  => 0,
        'nullable' => false
    )
);

$Installer->getConnection()->addForeignKey(
    $Installer->getFkName(
        $Installer->getTable('globale_order/details'),
        'order_id',
        $Installer->getTable('globale_order/orders'),
        'order_id'
    ),
    $Installer->getTable('globale_order/details'),
    'order_id',
    $Installer->getTable('globale_order/orders'),
    'order_id'
);

//</editor-fold>

//<editor-fold desc="globale_order_addresses">

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'type',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 100,
        'comment'  => 'The type of address (Billing , Shipping)',
        'unsigned' => true,
        'nullable' => false,
    )
);

$Installer->getConnection()->changeColumn(
    $Installer->getTable('globale_order/addresses'),
    'is_primery',
    'is_primary',
    array(
		'type'    => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    	'comment'  => 'is this the primary address type',
        'nullable' => false,
        'default'  => false
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'address1',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Address.Address1',

    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'address2',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Address.Address2'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'company',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Address.Company'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'country_code',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 2,
        'comment' => 'Address.CountryCode'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/addresses'),
    'country_code3',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 3,
        'after'     => 'country_code',
        'comment'   => 'CountryCode3'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'country_name',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Address.CountryName'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'city',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Address.City'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'email',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Address.Email'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'fax',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Address.Fax'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'first_name',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Address.FirstName'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'last_name',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Address.LastName'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'middle_name',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Address.MiddleName'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'salutation',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Address.Salutation'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'phone1',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Address.Phone1'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'phone2',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Address.Phone2'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'state_code',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Address.StateCode'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'state_or_province',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Address.StateOrProvince'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/addresses'),
    'zip',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Address.Zip'
    )
);

//</editor-fold>

//<editor-fold desc="globale_order_shipping">

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/shipping'),
    'order_tracking_number',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 32,
        'comment' => 'InternationalDetails.OrderTrackingNumber'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/shipping'),
    'order_tracking_url',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'InternationalDetails.OrderTrackingUrl'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/shipping'),
    'order_waybill_number',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 32,
        'comment' => 'InternationalDetails.OrderWaybillNumber'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/shipping'),
    'shipping_method_type_code',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'InternationalDetails.ShippingMethodTypeCode'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/shipping'),
    'shipping_method_type_name',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 55,
        'comment' => 'InternationalDetails.ShippingMethodTypeName'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/shipping'),
    'shipping_method_status_name',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'InternationalDetails.ShippingMethodTypeCode'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/shipping'),
    'shipping_method_type_code',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'InternationalDetails.ShippingMethodStatusName'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/shipping'),
    'shipping_method_status_code',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'InternationalDetails.ShippingMethodStatusCode'
    )
);

$Installer->getConnection()->changeColumn(
    $Installer->getTable('globale_order/shipping'),
    'shipping_method_code',
    'globale_shipping_method_code',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 255,
        'comment'  => 'InternationalDetails.ShippingMethodCode'
    )
);

$Installer->getConnection()->dropColumn(
    $Installer->getTable('globale_order/shipping'),
    'total_duties_price'
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/shipping'),
    'total_price',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'  => '12,4',
        'comment' => 'InternationalDetails.TotalShippingPrice'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/shipping'),
    'transaction_currency_code',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 3,
        'comment' => 'InternationalDetails.TransactionCurrencyCode'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/shipping'),
    'customer_shipping_method_code',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 255,
        'comment'  => 'InternationalDetails.ShippingMethodCode'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/shipping'),
    'customer_shipping_method_name',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 255,
        'comment'  => 'InternationalDetails.ShippingMethodName'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/shipping'),
    'discounted_shipping_price',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'comment'  => 'InternationalDetails.DiscountedShippingPrice'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/shipping'),
    'shipment_status_update_time',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 255,
        'comment'  => 'InternationalDetails.ShipmentStatusUpdateTime'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/shipping'),
    'shipment_location',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 255,
        'comment'  => 'InternationalDetails.ShipmentLocation'
    )
);

$Installer->getConnection()->addForeignKey(
    $Installer->getFkName(
        $Installer->getTable('globale_order/shipping'),
        'order_id',
        $Installer->getTable('globale_order/orders'),
        'order_id'
    ),
    $Installer->getTable('globale_order/shipping'),
    'order_id',
    $Installer->getTable('globale_order/orders'),
    'order_id'
);

//</editor-fold>

//<editor-fold desc="globale_order_payment">

$Installer->getConnection()->dropColumn(
    $Installer->getTable('globale_order/payment'),
    'address1'
);

$Installer->getConnection()->dropColumn(
    $Installer->getTable('globale_order/payment'),
    'address2'
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/payment'),
    'card_number',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'PaymentDetails.CardNumber'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/payment'),
    'cvv_number',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'PaymentDetails.CVVNumber'
    )
);

$Installer->getConnection()->dropColumn(
    $Installer->getTable('globale_order/payment'),
    'country_name'
);

$Installer->getConnection()->dropColumn(
    $Installer->getTable('globale_order/payment'),
    'country_code'
);

$Installer->getConnection()->dropColumn(
    $Installer->getTable('globale_order/payment'),
    'country_code3'
);

$Installer->getConnection()->dropColumn(
    $Installer->getTable('globale_order/payment'),
    'city'
);

$Installer->getConnection()->dropColumn(
    $Installer->getTable('globale_order/payment'),
    'email'
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/payment'),
    'expiration_date',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 255,
        'comment' => 'PaymentDetails.ExpirationDate'
    )
);

$Installer->getConnection()->dropColumn(
    $Installer->getTable('globale_order/payment'),
    'fax'
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/payment'),
    'owner_first_name',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'PaymentDetails.OwnerFirstName'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/payment'),
    'owner_last_name',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 255,
        'comment' => 'PaymentDetails.OwnerLastName'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/payment'),
    'owner_name',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'PaymentDetails.OwnerName'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/payment'),
    'payment_method_type_code',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'PaymentDetails.PaymentMethodTypeCode'
    )
);

$Installer->getConnection()->changeColumn(
    $Installer->getTable('globale_order/payment'),
    'payment_method_name',
    'customer_payment_method_name',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 255,
        'comment' => 'InternationalDetails.PaymentMethodName'
    )
);

$Installer->getConnection()->changeColumn(
    $Installer->getTable('globale_order/payment'),
    'payment_method_code',
    'customer_payment_method_code',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 255,
        'comment' => 'InternationalDetails.PaymentMethodCode'
    )
);

$Installer->getConnection()->dropColumn(
    $Installer->getTable('globale_order/payment'),
    'phone1'
);

$Installer->getConnection()->dropColumn(
    $Installer->getTable('globale_order/payment'),
    'phone2'
);

$Installer->getConnection()->dropColumn(
    $Installer->getTable('globale_order/payment'),
    'state_code'
);

$Installer->getConnection()->dropColumn(
    $Installer->getTable('globale_order/payment'),
    'state_or_province'
);

$Installer->getConnection()->dropColumn(
    $Installer->getTable('globale_order/payment'),
    'zip'
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/payment'),
    'payment_method_name',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 255,
        'comment' => 'PaymentDetails.PaymentMethodName'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/payment'),
    'payment_method_code',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 255,
        'comment' => 'PaymentDetails.PaymentMethodCode'
    )
);

$Installer->getConnection()->addForeignKey(
    $Installer->getFkName(
        $Installer->getTable('globale_order/payment'),
        'order_id',
        $Installer->getTable('globale_order/orders'),
        'order_id'
    ),
    $Installer->getTable('globale_order/payment'),
    'order_id',
    $Installer->getTable('globale_order/orders'),
    'order_id'
);

//</editor-fold>

//<editor-fold desc="globale_order_discounts">

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/discounts'),
    'coupon_code',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Discounts/Markups.CouponCode'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/discounts'),
    'description',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Discounts/Markups.Description'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/discounts'),
    'discount_code',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Discounts/Markups.DiscountCode'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/discounts'),
    'discount_type',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'length'  => 1,
        'comment' => 'Discounts/Markups.DiscountType'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/discounts'),
    'international_price',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'  => '12,4',
        'comment' => 'Discounts/Markups.InternationalPrice'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/discounts'),
    'local_vat_rate',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'  => '12,4',
        'comment' => 'Discounts/Markups.LocalVATRate'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/discounts'),
    'name',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Discounts/Markups.Name'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/discounts'),
    'price',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'  => '12,4',
        'comment' => 'Discounts/Markups.Price'
    )
);

$Installer->getConnection()->modifyColumn(
    $Installer->getTable('globale_order/discounts'),
    'vat_rate',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'  => '12,4',
        'comment' => 'Discounts/Markups.VATRate'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/discounts'),
    'product_cart_item_id',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment' => 'Discounts/Markups.ProductCartItemId'
    )
);

$Installer->getConnection()->addColumn(
    $Installer->getTable('globale_order/discounts'),
    'loyalty_voucher_code',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 100,
        'comment' => 'Discounts/Markups.LoyaltyVoucherCode'
    )
);

//</editor-fold>

$Installer->endSetup();