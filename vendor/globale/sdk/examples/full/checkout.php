<!DOCTYPE html>
<?php
require '../../vendor/autoload.php';
include '../Merchant/HandleCreateStub.php';

use GlobalE\SDK\SDK;
use GlobalE\SDK\Models;
use GlobalE\SDK\API\Common;
$sdk = new SDK();
$sdk->Browsing()->OnPageLoad();

$_SERVER['REMOTE_ADDR']= '150.217.16.251'; //italy
?>
<html>
<head>
    <title>SDK Flow</title>
    <meta charset="UTF-8">
    <style type="text/css">
        .item{float: left;}
    </style>
</head>
<body>

<h1>Full SDK Flow - Checkout</h1>

<div>
    <div class="gleContainer">
        <a href="#" class="gleShowSwitcher">Show Shipping Switcher</a>
    </div>
</div>

<?php
// product 1
$product1 = new Common\Request\ProductRequestData();
$product1->setProductCode("test1");
$product1->setOriginalListPrice(91.32);
$product1->setOriginalSalePrice(91.32);
$product1->setIsFixedPrice(false);

// product 2
$product2 = new Common\Request\ProductRequestData();
$product2->setProductCode("test2");
$product2->setOriginalListPrice(11.59);
$product2->setOriginalSalePrice(6.59);
$product2->setIsFixedPrice(false);

// product 3
$product3 = new Common\Request\ProductRequestData();
$product3->setProductCode("test3");
$product3->setOriginalListPrice(415.00);
$product3->setOriginalSalePrice(567.00);
$product3->setIsFixedPrice(true);

// Choose product
if(!empty($_GET['productCode'])) {
    switch ($_GET['productCode']) {
        case "test1":
            $products[] = $product1;
            break;
        case "test2":
            $products[] = $product2;
            break;
        case "test3":
            $products[] = $product3;
            break;
        default:
            $_GET['productCode'] = 'test1';
            $products[] = $product1;
            break;

    }
}else{
    $_GET['productCode'] = 'test1';
    $products[] = $product1;
}

// get product information
$results = $sdk->Browsing()->GetProductsInformation($products,true)->getData();


// get and update the test product and send it to send cart
$sendCartRequest = getSentCartRequest();
$sendCartRequest = json_decode($sendCartRequest,true);
$sendCartRequest['ProductsList'][0]['ProductCode'] = $results[$_GET['productCode']]->getProductCode();
$sendCartRequest['ProductsList'][0]['ListPrice'] = $results[$_GET['productCode']]->getPrice();
$sendCartRequest['ProductsList'][0]['OriginalListPrice'] = $products[0]->getPrice();
$sendCartRequest['ProductsList'][0]['SalePrice'] = $results[$_GET['productCode']]->getPrice();
$sendCartRequest['ProductsList'][0]['OriginalSalePrice'] = $products[0]->getPrice();

// get customer info (For send cart)
$customer = Models\Customer::getSingleton();
$customerDetails = $customer->getInfo();
$sendCartRequest['shippingDetails']['CountryCode'] = $customerDetails->getCountryISO();
$sendCartRequest['billingDetails']['CountryCode'] = $customerDetails->getCountryISO();

// call send cart
$response = $sdk->Checkout()->SendCart($sendCartRequest);


/* get order create from the API Json Request */
function getSentCartRequest(){
    return file_get_contents('SendCartProductExample.json');
}
?>

<script type="text/javascript" >
    <?php echo $sdk->Browsing()->LoadClientSDK()->getData(); ?>
    <?php echo $sdk->Checkout()->GenerateCheckoutPage()->getData(); ?>
</script>

<div id="checkoutContainer" style="width:50%;margin: auto;"></div>

</body>
</html>