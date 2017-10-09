<?php
namespace GlobalE\SDK\Core\Validator;

/**
 * Class Rules
 * @package GlobalE\SDK\Core
 */
class Rules
{
    /**
     * This is a list of allowed interfaces, actions in those interfaces, arguments in those actions
     * and properties of commons.
     * @var array
     */
    public static $ValidatorRules = array(

        // "Admin" interface rules.
        'Admin' => array(

            // "SaveProductsList" action rules.
            'SaveProductsList' => array(

                // "Products" argument rules.
                array(
                    'type' => 'GlobalE\SDK\Models\Common\Request\Product',
                    'array' => true,
                    'properties' => array(
                        'ProductCode' => array(
                            'type' => 'string'
                        ),
                        'Name' => array(
                            'type' => 'string'
                        ),
                        'Description' => array(
                            'type' => 'string'
                        ),
                        'ProductGroupCode' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'Keywords' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'URL' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'GenericHSCode' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'Weight' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'NetWeight' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'Height' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'Length' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'Width' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'Volume' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'NetVolume' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'ImageURL' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'ImageWidth' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'ImageHeight' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'OriginCountryCode' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'ListPrice' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'OriginalListPrice' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'SalePrice' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'SalePriceBeforeRounding' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'OriginalSalePrice' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'VATRateType' => array(
                            'type' => 'GlobalE\SDK\Models\Common\VatRateType',
                            'optional' => true,
                            'properties' => array(
                                'VATRateTypeCode' => array(
                                    'type' => 'string'
                                ),
                                'Name' => array(
                                    'type' => 'string'
                                ),
                                'Rate' => array(
                                    'type' => 'number'
                                )
                            )
                        ),
                        'LocalVATRateType' => array(
                            'type' => 'GlobalE\SDK\Models\Common\VatRateType',
                            'optional' => true,
                            'properties' => array(
                                'VATRateTypeCode' => array(
                                    'type' => 'string'
                                ),
                                'Name' => array(
                                    'type' => 'string'
                                ),
                                'Rate' => array(
                                    'type' => 'number'
                                )
                            )
                        ),
                        'Brand' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'Categories' => array(
                            'type' => 'array',
                            'optional' => true
                        ),
                        'OrderedQuantity' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'CartItemId' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'ParentCartItemId' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'CartItemOptionId' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'IsBlockedForGlobalE' => array(
                            'type' => 'boolean',
                            'optional' => true
                        ),
                        'IsBundle' => array(
                            'type' => 'boolean',
                            'optional' => true
                        ),
                        'Attributes' => array(
                            'type' => 'array',
                            'optional' => true
                        ),
                        'IsFixedPrice' => array(
                            'type' => 'boolean',
                            'optional' => true
                        ),
                        'IsVirtual' => array(
                            'type' => 'boolean',
                            'optional' => true
                        ),
                        'ProductCodeSecondary' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'ProductGroupCodeSecondary' => array(
                            'type' => 'string',
                            'optional' => true
                        )
                    )
                )
            ),

            // "UpdateOrderStatus" action rules.
            'UpdateOrderStatus' => array(

                // "OrderStatus" argument rules.
                array(
                    'type' => 'GlobalE\SDK\Models\Common\Request\OrderStatusDetails',
                    'properties' => array(
                        'OrderId' => array(
                            'type' => 'string'
                        ),
                        'OrderStatus' => array(
                            'type' => 'string'
                        ),
                        'OrderStatusReason' => array(
                            'type' => 'GlobalE\SDK\API\Common\OrderStatusReason',
                            'optional' => true,
                            'properties' => array(
                                'OrderStatusReasonCode' => array(
                                    'type' => 'string'
                                ),
                                'Name' => array(
                                    'type' => 'string'
                                )
                            )
                        ),
                        '$OrderComments' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'ConfirmationNumber' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'TrackingServiceName' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'TrackingNumber' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'TrackingURL' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'DeliveryReferenceNumber' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'TrackingServiceSite' => array(
                            'type' => 'string',
                            'optional' => true
                        )
                    )
                )
            ),

            // "UpdateOrderDispatch" action rules.
            'UpdateOrderDispatch' => array(

                // "Products" argument rules.
                array(
                    'type' => 'GlobalE\SDK\Models\Common\Request\Product',
                    'array' => true,
                    'properties' => array(
                        'ProductCode' => array(
                            'type' => 'string'
                        ),
                        'Name' => array(
                            'type' => 'string'
                        ),
                        'Description' => array(
                            'type' => 'string'
                        ),
                        'ProductGroupCode' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'Keywords' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'URL' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'GenericHSCode' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'Weight' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'NetWeight' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'Height' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'Length' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'Width' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'Volume' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'NetVolume' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'ImageURL' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'ImageWidth' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'ImageHeight' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'OriginCountryCode' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'ListPrice' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'OriginalListPrice' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'SalePrice' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'SalePriceBeforeRounding' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'OriginalSalePrice' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'VATRateType' => array(
                            'type' => 'GlobalE\SDK\Models\Common\VatRateType',
                            'optional' => true,
                            'properties' => array(
                                'VATRateTypeCode' => array(
                                    'type' => 'string'
                                ),
                                'Name' => array(
                                    'type' => 'string'
                                ),
                                'Rate' => array(
                                    'type' => 'number'
                                )
                            )
                        ),
                        'LocalVATRateType' => array(
                            'type' => 'GlobalE\SDK\Models\Common\VatRateType',
                            'optional' => true,
                            'properties' => array(
                                'VATRateTypeCode' => array(
                                    'type' => 'string'
                                ),
                                'Name' => array(
                                    'type' => 'string'
                                ),
                                'Rate' => array(
                                    'type' => 'number'
                                )
                            )
                        ),
                        'Brand' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'Categories' => array(
                            'type' => 'array',
                            'optional' => true
                        ),
                        'OrderedQuantity' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'CartItemId' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'ParentCartItemId' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'CartItemOptionId' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'IsBlockedForGlobalE' => array(
                            'type' => 'boolean',
                            'optional' => true
                        ),
                        'IsBundle' => array(
                            'type' => 'boolean',
                            'optional' => true
                        ),
                        'Attributes' => array(
                            'type' => 'array',
                            'optional' => true
                        ),
                        'IsFixedPrice' => array(
                            'type' => 'boolean',
                            'optional' => true
                        ),
                        'IsVirtual' => array(
                            'type' => 'boolean',
                            'optional' => true
                        ),
                        'ProductCodeSecondary' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'ProductGroupCodeSecondary' => array(
                            'type' => 'string',
                            'optional' => true
                        )
                    )
                ),

                // "OrderStatus" argument rules.
                array(
                    'type' => 'GlobalE\SDK\Models\Common\Request\OrderStatusDetails',
                    'properties' => array(
                        'OrderId' => array(
                            'type' => 'string'
                        ),
                        'OrderStatus' => array(
                            'type' => 'string'
                        ),
                        'OrderStatusReason' => array(
                            'type' => 'GlobalE\SDK\API\Common\OrderStatusReason',
                            'optional' => true,
                            'properties' => array(
                                'OrderStatusReasonCode' => array(
                                    'type' => 'string'
                                ),
                                'Name' => array(
                                    'type' => 'string'
                                )
                            )
                        ),
                        '$OrderComments' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'ConfirmationNumber' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'TrackingServiceName' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'TrackingNumber' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'TrackingURL' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'DeliveryReferenceNumber' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'TrackingServiceSite' => array(
                            'type' => 'string',
                            'optional' => true
                        )
                    )
                ),

                // "Parcels" argument rules.
                array(
                    'type' => 'GlobalE\SDK\Models\Common\Request\Parcel',
                    'array' => true,
                    'optional' => true,
                    'properties' => array(
                        'ParcelCode' => array(
                            'type' => 'string'
                        )
                    )
                )
            ),

            // "ClearGECache" action rules.
            'ClearGECache' => array(

            ),

            // "GetBarCode" action rules.
            'GetBarCode' => array(

                // "OrderId" argument rules.
                array(
                    'type' => 'string'
                )
            ),

            // "GetOrderInvoice" action rules.
            'GetOrderInvoice' => array(

                // "OrderIds" argument rules.
                array(
                    'type' => 'string',
                    'array' => true
                )
            )
        ),

        // Browsing Interface Rules
        'Browsing' => array(

            // "LoadClientSDK" action rules.
            'LoadClientSDK' => array(

            ),

            // "Initialize" action rules.
            'Initialize' => array(

                // "vatRate" argument rules.
                array(
                    'type' => 'number',
                    'optional' => true
                ),

                // "baseCurrency" argument rules.
                array(
                    'type' => 'string',
                    'optional' => true
                ),

                // "baseCountry" argument rules.
                array(
                    'type' => 'string',
                    'optional' => true
                ),

                // "baseCulture" argument rules.
                array(
                    'type' => 'string',
                    'optional' => true
                )
            ),

            // "OnPageLoad" action rules.
            'OnPageLoad' => array(

            ),

            // "GetCustomerInformation" action rules.
            'GetCustomerInformation' => array(

            ),

            // "GetCountries" action rules.
            'GetCountries' => array(

            ),

            // "GetCurrencies" action rules.
            'GetCurrencies' => array(

            ),

            // "GetProductsInformation" action rules.
            'GetProductsInformation' => array(

                // "Products" argument rules.
                array(
                    'type' => 'GlobalE\SDK\Models\Common\Request\ProductRequestData',
                    'array' => true,
                    'properties' => array(
                        'ProductCode' => array(
                            'type' => 'string'
                        ),
                        'OriginalListPrice' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'OriginalSalePrice' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'IsFixedPrice' => array(
                            'type' => 'boolean',
                            'optional' => true
                        ),
                        'VATRateType' => array(
                            'type' => 'GlobalE\SDK\Models\Common\VatRateType',
                            'optional' => true,
                            'properties' => array(
                                'VATRateTypeCode' => array(
                                    'type' => 'string'
                                ),
                                'Name' => array(
                                    'type' => 'string'
                                ),
                                'Rate' => array(
                                    'type' => 'number'
                                )
                            )
                        ),
                        'LocalVATRateType' => array(
                            'type' => 'GlobalE\SDK\Models\Common\VatRateType',
                            'optional' => true,
                            'properties' => array(
                                'VATRateTypeCode' => array(
                                    'type' => 'string'
                                ),
                                'Name' => array(
                                    'type' => 'string'
                                ),
                                'Rate' => array(
                                    'type' => 'number'
                                )
                            )
                        )
                    )
                ),

                // "PriceIncludesVAT" argument rules.
                array(
                    'type' => 'boolean',
                    'optional' => true
                )
            ),

            // "GetCalculatedRawPrice" action rules.
            'GetCalculatedRawPrice' => array(

                // "RawPrices" argument rules.
                array(
                    'type' => 'GlobalE\SDK\Models\Common\Request\RawPriceRequestData',
                    'array' => true,
                    'properties' => array(
                        'OriginalListPrice' => array(
                            'type' => 'number',
                            'optional' => true
                        ),
                        'OriginalSalePrice' => array(
                            'type' => 'number'
                        ),
                        'IsFixedPrice' => array(
                            'type' => 'boolean'
                        ),
                        'VATRateType' => array(
                            'type' => 'GlobalE\SDK\Models\Common\VatRateType',
                            'optional' => true,
                            'properties' => array(
                                'VATRateTypeCode' => array(
                                    'type' => 'string'
                                ),
                                'Name' => array(
                                    'type' => 'string'
                                ),
                                'Rate' => array(
                                    'type' => 'number'
                                )
                            )
                        ),
                        'LocalVATRateType' => array(
                            'type' => 'GlobalE\SDK\Models\Common\VatRateType',
                            'optional' => true,
                            'properties' => array(
                                'VATRateTypeCode' => array(
                                    'type' => 'string'
                                ),
                                'Name' => array(
                                    'type' => 'string'
                                ),
                                'Rate' => array(
                                    'type' => 'number'
                                )
                            )
                        )
                    )
                ),

                // "PriceIncludesVAT" argument rules.
                array(
                    'type' => 'boolean',
                    'optional' => true

                ),

                // "UseRounding" argument rules.
                array(
                    'type' => 'boolean',
                    'optional' => true
                ),

                // "IsDiscount" argument rules.
                array(
                    'type' => 'boolean',
                    'optional' => true
                )
            ),

            // "IsCountryOperatedByGlobale" action rules.
            'IsCountryOperatedByGlobale' => array(

                // "countryCode" argument rules.
                array(
                    'type' => 'string'
                )
            ),

            // "IsUserSupportedByGlobale" action rules.
            'IsUserSupportedByGlobale' => array(

            ),

            // "setUserInfo" action rules.
            'setUserInfo' => array(

                // "newCustomerInfo" argument rules.
                array(
                    'type' => 'GlobalE\SDK\Models\Common\CustomerInfo',
                    'properties' => array(
                        'countryISO' => array(
                            'type' => 'string'
                        ),
                        'currencyCode' => array(
                            'type' => 'string'
                        ),
                        'cultureCode' => array(
                            'type' => 'string'
                        )
                    )
                ),

                // "autoFillData" argument rules.
                array(
                    'type' => 'boolean',
                    'optional' => true
                )
            )
        ),

        // "Checkout" interface rules.
        'Checkout' => array(

            // "SendCart" action rules.
            'SendCart' => array(

                // "SendCartRequest" argument rules.
                array(
                    'type' => 'GlobalE\SDK\Models\Common\Request\SendCart',
                    'properties' => array(
                        'ShippingDetails' => array(
                            'type' => 'GlobalE\SDK\API\Common\Address',
                            'optional' => true,
                            'properties' => array(
                                'UserId' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'UserIdNumber' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'UserIdNumberType' => array(
                                    'type' => 'object',
                                    'optional' => true
                                ),
                                'FirstName' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'LastName' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'MiddleName' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Salutation' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Phone1' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Phone2' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Fax' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Email' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Address1' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Address2' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'City' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'StateOrProvince' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Zip' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'CountryCode' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'StateCode' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Company' => array(
                                    'type' => 'string',
                                    'optional' => true
                                )
                            )
                        ),
                        'BillingDetails' => array(
                            'type' => 'GlobalE\SDK\API\Common\Address',
                            'optional' => true,
                            'properties' => array(
                                'UserId' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'UserIdNumber' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'UserIdNumberType' => array(
                                    'type' => 'object',
                                    'optional' => true
                                ),
                                'FirstName' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'LastName' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'MiddleName' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Salutation' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Phone1' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Phone2' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Fax' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Email' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Address1' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Address2' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'City' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'StateOrProvince' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Zip' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'CountryCode' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'StateCode' => array(
                                    'type' => 'string',
                                    'optional' => true
                                ),
                                'Company' => array(
                                    'type' => 'string',
                                    'optional' => true
                                )
                            )
                        ),
                        'ShippingOptionsList' => array(
                            'type' => 'GlobalE\SDK\API\Common\ShippingOption',
                            'optional' => true,
                            'properties' => array(
                                'Carrier' => array(
                                    'type' => 'string'
                                ),
                                'CarrierTitle' => array(
                                    'type' => 'string'
                                ),
                                'CarrierName' => array(
                                    'type' => 'string'
                                ),
                                'Code' => array(
                                    'type' => 'string'
                                ),
                                'Method' => array(
                                    'type' => 'string'
                                ),
                                'MethodTitle' => array(
                                    'type' => 'string'
                                ),
                                'MethodDescription' => array(
                                    'type' => 'string'
                                ),
                                'Price' => array(
                                    'type' => 'number'
                                )
                            )
                        ),
                        'ProductsList' => array(
                            'type' => 'array'
                        ),
                        'OriginalCurrencyCode' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'MerchantCartToken' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'MerchantCartHash' => array(
                            'type' => 'string',
                            'optional' => true
                        ),
                        'DoNotChargeVAT' => array(
                            'type' => 'boolean',
                            'optional' => true
                        )
                    )
                )
            ),

            // "GenerateCheckoutPage" action rules.
            'GenerateCheckoutPage' => array(

                // "cartToken" argument rules.
                array(
                    'type' => 'string',
                    'optional' => true,
                ),

                // "containerId" argument rules.
                array(
                    'type' => 'string',
                    'optional' => true,
                )
            )

        ),

        // "Merchant" interface rules.
        'Merchant' => array(

            // "HandleOrderCreation" action rules.
            'HandleOrderCreation' => array(

                // "Data" argument rules.
                array(
                    'type' => 'string'
                ),

                // "Action" argument rules.
                array(
                    'type' => 'object'
                ),

                // "Output" argument rules.
                array(
                    'type' => 'boolean',
                    'optional' => true
                )
            ),

            // "HandleOrderPayment" action rules.
            'HandleOrderPayment' => array(

                // "Data" argument rules.
                array(
                    'type' => 'string'
                ),

                // "Action" argument rules.
                array(
                    'type' => 'object'
                ),

                // "Output" argument rules.
                array(
                    'type' => 'boolean',
                    'optional' => true
                )
            ),

            // "HandleOrderStatusUpdate" action rules.
            'HandleOrderStatusUpdate' => array(

                // "Data" argument rules.
                array(
                    'type' => 'string'
                ),

                // "Action" argument rules.
                array(
                    'type' => 'object'
                ),

                // "Output" argument rules.
                array(
                    'type' => 'boolean',
                    'optional' => true
                )
            ),

            // "HandleOrderShippingInfo" action rules.
            'HandleOrderShippingInfo' => array(

                // "Data" argument rules.
                array(
                    'type' => 'string'
                ),

                // "Action" argument rules.
                array(
                    'type' => 'object'
                ),

                // "Output" argument rules.
                array(
                    'type' => 'boolean',
                    'optional' => true
                )
            )
        )
    );
}