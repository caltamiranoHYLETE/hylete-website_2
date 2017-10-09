<?php
require '../../vendor/autoload.php';
use GlobalE\SDK\SDK;

include 'HandleCreateStub.php';

$SDK = new SDK(20, 'USD');
$RequestBody = $_POST['data'];
$HandleCreateStub = new HandleCreateStub();
?>

<h2><u>Merchant HandleOrderShippingInfo public method Example:</u></h2>
<h3><u>Request:</u></h3>
<pre>
1)  $OrderCreateRequest = '{"AllowMailsFromMerchant":false,"ClearCart":true,"UserId":"45787","CurrencyCode":"GBP","Products":[{"Sku":"LR023964.C8","Price":299.66,"PriceBeforeRoundingRate":299.88,"PriceBeforeGlobalEDiscount":299.66,"Quantity":1,"VATRate":20.0,"InternationalPrice":389.28,"CartItemId":"GE148783","ParentCartItemId":null,"CartItemOptionId":null,"HandlingCode":"","GiftMessage":"","RoundingRate":0.76978010686395393,"IsBackOrdered":false,"BackOrderDate":null}],"Customer":{"EmailAddress":"nick@proswimwear.co.uk","IsEndCustomerPrimary":true,"SendConfirmation":false},"PrimaryShipping":{"FirstName":"Ehud","LastName":"Bichman","MiddleName":null,"Salutation":null,"Company":"","Address1":"bla","Address2":"","City":"bla","StateCode":null,"StateOrProvince":null,"Zip":"00000","Email":"ehud.bichman%40global-e.com","Phone1":"0000000000","Phone2":"","Fax":"","CountryCode":"IL","CountryName":"Israel"},"SecondaryShipping":{"FirstName":"GlobalE","LastName":"ProSwimwear","MiddleName":null,"Salutation":null,"Company":"","Address1":"Unit 1 Plum Lane","Address2":"Dunwear\r\nGE92799319GB","City":"Bridgwater","StateCode":"NN","StateOrProvince":null,"Zip":"TA6 5HL","Email":"nick@proswimwear.co.uk","Phone1":"08444411999","Phone2":null,"Fax":null,"CountryCode":"GB","CountryName":"United Kingdom"},"ShippingMethodCode":"globaleintegration_standard","Discounts":[{"Name":"Shipping discount for fixed price","Description":"Shipping discount provided from fixed price range 19703","Price":25.72,"DiscountType":2,"VATRate":0.0,"LocalVATRate":0.0,"CouponCode":null,"InternationalPrice":149.73,"DiscountCode":"","ProductCartItemId":null,"LoyaltyVoucherCode":null}],"Markups":[],"LoyaltyPointsSpent":0.0,"LoyaltyPointsEarned":0.0,"SameDayDispatch":false,"SameDayDispatchCost":0.0,"DoNotChargeVAT":false,"CustomerComments":null,"IsFreeShipping":false,"FreeShippingCouponCode":"","ShipToStoreCode":null,"RoundingRate":0.770345,"UrlParameters":null,"OriginalMerchantTotalProductsDiscountedPrice":299.88,"LoyaltyCode":"","OTVoucherCode":null,"OTVoucherAmount":null,"OTVoucherCurrencyCode":null,"IsSplitOrder":false,"PaymentDetails":null,"PrimaryBilling":{"FirstName":"Ehud","LastName":"Bichman","MiddleName":null,"Salutation":null,"Company":null,"Address1":"548+Market+St.","Address2":"","City":"San-Francisco","StateCode":"CA","StateOrProvince":"California","Zip":"94115","Email":"ehud.bichman%40global-e.com","Phone1":"0000000000","Phone2":"","Fax":"","CountryCode":"US","CountryName":"United+States"},"SecondaryBilling":{"FirstName":"GlobalE","LastName":"UK Limited","MiddleName":null,"Salutation":null,"Company":"GlobalE","Address1":"45 Leather Lane","Address2":null,"City":"London","StateCode":null,"StateOrProvince":null,"Zip":"EC1N 7TJ","Email":"info@global-e.com","Phone1":"+ 44 (0)808 258 0300","Phone2":null,"Fax":"+ 44 (0)203 514 7171","CountryCode":"GB","CountryName":"United Kingdom"},"OrderId":"GE92799319GB","StatusCode":"N/A","MerchantGUID":"7fdb743c-3b97-4ac2-9afc-f8e7feb2a8c1","CartId":"085666e3569dc616806760825a30c2ec","MerchantOrderId":null,"PriceCoefficientRate":1.0,"CartHash":"085666e3569dc616806760825a30c2ec","InternationalDetails":{"CurrencyCode":"ILS","TotalPrice":462.45,"TransactionCurrencyCode":"ILS","TransactionTotalPrice":462.45,"TotalShippingPrice":178.79,"SameDayDispatchCost":0.0,"TotalCCFPrice":29.11,"TotalDutiesPrice":44.11,"ShippingMethodCode":"172","ShippingMethodName":"DHL Express Worldwide","PaymentMethodCode":"1","PaymentMethodName":"Visa","DutiesGuaranteed":true,"OrderTrackingNumber":null,"OrderTrackingUrl":"http%3a%2f%2fwww.dhl.com%2fcontent%2fg0%2fen%2fexpress%2ftracking.shtml%3fbrand%3dDHL%26AWB%3d","OrderWaybillNumber":null,"ShippingMethodTypeCode":"Express","ShippingMethodTypeName":"Express Courier (Air)","DeliveryDaysFrom":3,"DeliveryDaysTo":3,"ConsignmentFee":0.0,"SizeOverchargeValue":0.0,"RemoteAreaSurcharge":0.0,"ShippingMethodStatusCode":"Undefined","ShippingMethodStatusName":"undefined","ShipmentStatusUpdateTime":null,"ShipmentLocation":null}}';

2)  $SDK->Merchant()->HandleOrderShippingInfo($OrderCreateRequest, $HandleCreateStub)
</pre>

<h3><u>Response:</u></h3>
<pre>
<?php $OrderCreateRequest = getOrderCreateRequest(); ?>
<?php $response = $SDK->Merchant()->HandleOrderShippingInfo($OrderCreateRequest, $HandleCreateStub); ?>
</pre>

<?php
/* get Json Request for API call send cart */
function getOrderCreateRequest(){
    return file_get_contents('OrderCreateRequest.json');
}
?>
