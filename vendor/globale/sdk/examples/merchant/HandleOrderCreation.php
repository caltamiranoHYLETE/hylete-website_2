<?php
require '../../vendor/autoload.php';
include 'HandleCreateStub.php';
use GlobalE\SDK\SDK;
use GlobalE\SDK\API\Common;
use GlobalE\SDK\Core\Profiler;

$SDK = new SDK(20, 'USD');
?>

<h2><u>HandleOrderCreation Example:</u></h2>

<h3><u>Flow</u></h3>
<pre>
1) $SDK->Browsing()->OnPageLoad();      // Load customer info and initialize relevant SDK settings

<?php Profiler::startTimer('HandleOrderCreation Public Method'); ?>

<u>Response:</u>
<?php
// load customer info and initialize relevant SDK settings
var_dump($SDK->Browsing()->OnPageLoad());
?>
<hr />

2) $sendCartRequest = '{"shippingDetails":{"UserId":"45787","UserIdNumber":"","UserIdNumberType":{"UserIdNumberTypeId":"","Name":""},"FirstName":"Ehud","LastName":"Bichman","Company":"","MiddleName":"","Salutation":"","Email":"ehud.bichman@global-e.com","Phone1":"0000000000","Phone2":"","Fax":"","Address1":"548 Market St.","Address2":"","City":"San-Francisco","StateOrProvince":"California","Zip":"94115","CountryCode":"US"},"billingDetails":{"UserId":"45787","UserIdNumber":"","UserIdNumberType":{"UserIdNumberTypeId":"","Name":""},"FirstName":"Ehud","LastName":"Bichman","Company":"","MiddleName":"","Salutation":"","Email":"ehud.bichman@global-e.com","Phone1":"0000000000","Phone2":"","Fax":"","Address1":"548 Market St.","Address2":"","City":"San-Francisco","StateOrProvince":"California","Zip":"94115","CountryCode":"US"},"priceCoefficientRate":1,"includeVAT":6,"MerchantCartToken":"085666e3569dc616806760825a30c2ec","originalCurrencyCode":"GBP","preferedCultureCode":"en-GB","inputDataCultureCode":"en-GB","shippingOptionsList":{"Carrier":"globaleintegration","CarrierTitle":"globaleintegration","CarrierName":"globaleintegration","Code":"globaleintegration_standard","Method":"standard","MethodTitle":"Standard","MethodDescription":"","Price":0},"MerchantCartHash":"085666e3569dc616806760825a30c2ec","doNotChargeVAT":0,"ProductsList":[{"IsBlockedForGlobalE":false,"ProductCode":"LR023964.C8","Name":"LR023964","ProductGroupCode":null,"Description":null,"IsBundle":false,"URL":"","GenericHSCode":"","OriginCountryCode":"GB","Weight":"3.87","Width":"0.00","Height":"0.00","Length":"320.00","Volume":"","ImageURL":"","ImageHeight":"","ImageWidth":"","Categories":null,"Brand":"","ListPrice":389.28,"OriginalListPrice":299.88,"SalePrice":389.28,"OriginalSalePrice":299.88,"LocalVATRateType":{"VATRateTypeCode":"2","Name":"VATworld","Rate":"20.000"},"VATRateType":{"VATRateTypeCode":"2","Name":"VATworld","Rate":"20.000"},"OrderedQuantity":1}]}';
   $SDK->Checkout()->SendCart($sendCartRequest);    // Load Json request with customer details and call the API reqeust send cart

Response:
<?php
// call send cart with Json request object
$sendCartRequest = getSentCartRequest();
$response = $SDK->Checkout()->SendCart($sendCartRequest);
var_dump($response);
?>
<hr />

3) $SDK->Browsing()->LoadClientSDK()->getData();    // Load client settings for use in the checkout page

4) $SDK->Checkout()->GenerateCheckoutPage($response->getData()->CartToken)->getData();  // Load client checkout page with the given cart token from the API call send cart

<hr />

<div class="gleContainer">
    <a href="#" class="gleShowSwitcher">Show Shipping Switcher</a>
</div>

<hr />

<h3><u>Generate Checkout Page from the send cart request</u></h3>

<!-- Generate client checkout page -->
<script type="text/javascript">
    <?php // load client settings for use in the checkout page ?>
    <?php echo $SDK->Browsing()->LoadClientSDK()->getData(); ?>
    <?php echo $SDK->Checkout()->GenerateCheckoutPage($response->getData()->CartToken)->getData(); ?>
</script>
<!-- div for generate checkout page -->
<div id="checkoutContainer" style="width:50%;margin: auto;"></div>

<hr />

<h3><u>Order Creation - call handle order creation public method</u></h3>

   // Load order create json request
5) $OrderCreateRequest = '{"AllowMailsFromMerchant":false,"ClearCart":true,"UserId":"45787","CurrencyCode":"GBP","Products":[{"Sku":"LR023964.C8","Price":299.66,"PriceBeforeRoundingRate":299.88,"PriceBeforeGlobalEDiscount":299.66,"Quantity":1,"VATRate":20.0,"InternationalPrice":389.28,"CartItemId":"GE148783","ParentCartItemId":null,"CartItemOptionId":null,"HandlingCode":"","GiftMessage":"","RoundingRate":0.76978010686395393,"IsBackOrdered":false,"BackOrderDate":null}],"Customer":{"EmailAddress":"nick@proswimwear.co.uk","IsEndCustomerPrimary":true,"SendConfirmation":false},"PrimaryShipping":{"FirstName":"Ehud","LastName":"Bichman","MiddleName":null,"Salutation":null,"Company":"","Address1":"bla","Address2":"","City":"bla","StateCode":null,"StateOrProvince":null,"Zip":"00000","Email":"ehud.bichman%40global-e.com","Phone1":"0000000000","Phone2":"","Fax":"","CountryCode":"IL","CountryName":"Israel"},"SecondaryShipping":{"FirstName":"GlobalE","LastName":"ProSwimwear","MiddleName":null,"Salutation":null,"Company":"","Address1":"Unit 1 Plum Lane","Address2":"Dunwear\r\nGE92799319GB","City":"Bridgwater","StateCode":"NN","StateOrProvince":null,"Zip":"TA6 5HL","Email":"nick@proswimwear.co.uk","Phone1":"08444411999","Phone2":null,"Fax":null,"CountryCode":"GB","CountryName":"United Kingdom"},"ShippingMethodCode":"globaleintegration_standard","Discounts":[{"Name":"Shipping discount for fixed price","Description":"Shipping discount provided from fixed price range 19703","Price":25.72,"DiscountType":2,"VATRate":0.0,"LocalVATRate":0.0,"CouponCode":null,"InternationalPrice":149.73,"DiscountCode":"","ProductCartItemId":null,"LoyaltyVoucherCode":null}],"Markups":[],"LoyaltyPointsSpent":0.0,"LoyaltyPointsEarned":0.0,"SameDayDispatch":false,"SameDayDispatchCost":0.0,"DoNotChargeVAT":false,"CustomerComments":null,"IsFreeShipping":false,"FreeShippingCouponCode":"","ShipToStoreCode":null,"RoundingRate":0.770345,"UrlParameters":null,"OriginalMerchantTotalProductsDiscountedPrice":299.88,"LoyaltyCode":"","OTVoucherCode":null,"OTVoucherAmount":null,"OTVoucherCurrencyCode":null,"IsSplitOrder":false,"PaymentDetails":null,"PrimaryBilling":{"FirstName":"Ehud","LastName":"Bichman","MiddleName":null,"Salutation":null,"Company":null,"Address1":"548+Market+St.","Address2":"","City":"San-Francisco","StateCode":"CA","StateOrProvince":"California","Zip":"94115","Email":"ehud.bichman%40global-e.com","Phone1":"0000000000","Phone2":"","Fax":"","CountryCode":"US","CountryName":"United+States"},"SecondaryBilling":{"FirstName":"GlobalE","LastName":"UK Limited","MiddleName":null,"Salutation":null,"Company":"GlobalE","Address1":"45 Leather Lane","Address2":null,"City":"London","StateCode":null,"StateOrProvince":null,"Zip":"EC1N 7TJ","Email":"info@global-e.com","Phone1":"+ 44 (0)808 258 0300","Phone2":null,"Fax":"+ 44 (0)203 514 7171","CountryCode":"GB","CountryName":"United Kingdom"},"OrderId":"GE92799319GB","StatusCode":"N/A","MerchantGUID":"7fdb743c-3b97-4ac2-9afc-f8e7feb2a8c1","CartId":"085666e3569dc616806760825a30c2ec","MerchantOrderId":null,"PriceCoefficientRate":1.0,"CartHash":"085666e3569dc616806760825a30c2ec","InternationalDetails":{"CurrencyCode":"ILS","TotalPrice":462.45,"TransactionCurrencyCode":"ILS","TransactionTotalPrice":462.45,"TotalShippingPrice":178.79,"SameDayDispatchCost":0.0,"TotalCCFPrice":29.11,"TotalDutiesPrice":44.11,"ShippingMethodCode":"172","ShippingMethodName":"DHL Express Worldwide","PaymentMethodCode":"1","PaymentMethodName":"Visa","DutiesGuaranteed":true,"OrderTrackingNumber":null,"OrderTrackingUrl":"http%3a%2f%2fwww.dhl.com%2fcontent%2fg0%2fen%2fexpress%2ftracking.shtml%3fbrand%3dDHL%26AWB%3d","OrderWaybillNumber":null,"ShippingMethodTypeCode":"Express","ShippingMethodTypeName":"Express Courier (Air)","DeliveryDaysFrom":3,"DeliveryDaysTo":3,"ConsignmentFee":0.0,"SizeOverchargeValue":0.0,"RemoteAreaSurcharge":0.0,"ShippingMethodStatusCode":"Undefined","ShippingMethodStatusName":"undefined","ShipmentStatusUpdateTime":null,"ShipmentLocation":null}}';

   $HandleCreateStub = new HandleCreateStub();      // Create handle action function

   $response = $SDK->Merchant()->HandleOrderCreation($OrderCreateRequest, $HandleCreateStub);   // call handle order creation public method

<h4><u>Response:</u></h4>

<?php
$OrderCreateRequest = getOrderCreateRequest();
// get OrderId from request
$OrderCreateRequestObject = json_decode($OrderCreateRequest);
$OrderId = $OrderCreateRequestObject->OrderId;
// create handle Stub
$HandleCreateStub = new HandleCreateStub($OrderId);
$response = $SDK->Merchant()->HandleOrderCreation($OrderCreateRequest, $HandleCreateStub);
?>
</pre>
<br /><br /><br /><br /><hr />

<?php Profiler::endTimer('HandleOrderCreation Public Method'); ?>





<?php
// Helper Functions

/* get Json Request for API call send cart */
function getSentCartRequest(){
    return json_decode(file_get_contents('SendCartRequest.json'),true);
}

/* collect all information from Json Request */
function buildRequest($SendCartRequest) {

    // collect data from Json request
    $ShippingDetails = new Common\Request\Address();
    $BillingDetails = new Common\Request\Address();
    $ShippingOptionsList = new Common\Request\ShippingOption();
    $Product = new Common\Request\Product();

    // shipping details
    foreach ($SendCartRequest['shippingDetails'] as $key => $value) {
        $ShippingDetails->$key = $value;
    }
    // billing details
    foreach ($SendCartRequest['billingDetails'] as $key => $value) {
        $BillingDetails->$key = $value;
    }
    // shipping options list
    foreach ($SendCartRequest['shippingOptionsList'] as $key => $value) {
        $ShippingOptionsList->$key = $value;
    }
    // products list
    foreach ($SendCartRequest['ProductsList'][0] as $key => $value) {
        $Product->$key = $value;
    }

    $SendCartRequest['shippingDetails'] = $ShippingDetails;
    $SendCartRequest['billingDetails'] = $BillingDetails;
    $SendCartRequest['shippingOptionsList'] = $ShippingOptionsList;
    $SendCartRequest['ProductsList'] = array($Product);
    return $SendCartRequest;
}


/* get order create from the API Json Request */
function getOrderCreateRequest(){
    return file_get_contents('OrderCreateRequest.json');
}
?>