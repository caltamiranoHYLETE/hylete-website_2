<?php
require '../../vendor/autoload.php';
use GlobalE\SDK\SDK;

include 'HandleCreateStub.php';

$SDK = new SDK(20, 'USD');
$HandleCreateStub = new HandleCreateStub();
?>

<h2><u>Merchant HandleOrderPayment public method Example:</u></h2>

<h3><u>Request:</u></h3>
<pre>
1) $OrderPaymentRequest = '{"PaymentDetails":{"OwnerFirstName":"GlobalE","OwnerLastName":"UK Limited","OwnerName":"Globale UK Limited","CardNumber":"1234","CVVNumber":"**3","PaymentMethodName":"Undefined","PaymentMethodCode":"OT","ExpirationDate":"2040-09-22","CountryName":"United Kingdom","CountryCode":"GB","StateCode":"","StateOrProvince":null,"City":"London","Zip":"EC1N 7TJ","Address1":"45 Leather Lane","Address2":null,"Phone1":"  44 (0)808 258 0300","Phone2":null,"Fax":"  44 (0)203 514 7171","Email":"info@global-e.com","PaymentMethodTypeCode":"globaleintegration"},"PrimaryBilling":{"FirstName":"gfd","LastName":"gfd","MiddleName":null,"Salutation":null,"Company":null,"Address1":"rerwsg","Address2":"","City":"gfdg","StateCode":null,"StateOrProvince":"","Zip":"450908","Email":"gf@gmail.com","Phone1":"6986234","Phone2":"","Fax":"","CountryCode":"IL","CountryName":"Israel"},"SecondaryBilling":{"FirstName":"GlobalE","LastName":"UK Limited","MiddleName":null,"Salutation":null,"Company":"GlobalE","Address1":"45 Leather Lane","Address2":null,"City":"London","StateCode":null,"StateOrProvince":null,"Zip":"EC1N 7TJ","Email":"info@global-e.com","Phone1":"  44 (0)808 258 0300","Phone2":null,"Fax":"  44 (0)203 514 7171","CountryCode":"GB","CountryName":"United Kingdom"},"OrderId":"GE92754214GB","StatusCode":"N/A","MerchantGUID":"32a02d79-91fe-4108-8aff-dfc73721fd6d","CartId":"321603","MerchantOrderId":"78238","PriceCoefficientRate":1.000000,"CartHash":"","InternationalDetails":{"CurrencyCode":"ILS","TotalPrice":718.53,"TransactionCurrencyCode":"ILS","TransactionTotalPrice":718.5300,"TotalShippingPrice":119.5300,"SameDayDispatchCost":0.0000,"TotalCCFPrice":0.0000,"TotalDutiesPrice":0.0000,"ShippingMethodCode":"205","ShippingMethodName":"Skynet","PaymentMethodCode":"1","PaymentMethodName":"Visa","DutiesGuaranteed":false,"OrderTrackingNumber":null,"OrderTrackingUrl":"https://ws01.ffdx.net/v4/etrack_blank.ASpx?stid=skynet&pageid=0002&cn=","OrderWaybillNumber":null,"ShippingMethodTypeCode":"Express","ShippingMethodTypeName":"Express Courier (Air)","DeliveryDaysFrom":3,"DeliveryDaysTo":4,"ConsignmentFee":0.0000,"SizeOverchargeValue":0.0000,"RemoteAreaSurcharge":0.0000,"ShippingMethodStatusCode":"Undefined","ShippingMethodStatusName":"undefined","ShipmentStatusUpdateTime":null,"ShipmentLocation":null}}';

2) $SDK->Merchant()->HandleOrderPayment($OrderPaymentRequest, $HandleCreateStub);
</pre>

<h3><u>Response:</u></h3>
<pre>
<?php $OrderPaymentRequest = getOrderCreateRequest(); ?>
<?php $response = $SDK->Merchant()->HandleOrderPayment($OrderPaymentRequest, $HandleCreateStub); ?>
</pre>

<?php
/* get Json Request for API call send cart */
function getOrderCreateRequest(){
    return file_get_contents('OrderPaymentRequest.json');
}
?>
