<!DOCTYPE html>
<?php
require '../../vendor/autoload.php';
use GlobalE\SDK\SDK;
use GlobalE\SDK\API\Common;
$sdk = new SDK();
$sdk->Browsing()->OnPageLoad();
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

<h1>Full SDK Flow</h1>

<div>
    <div class="gleContainer">
        <a href="#" class="gleShowSwitcher">Show Shipping Switcher</a>
    </div>
</div>

<?php
$product1 = new Common\Request\ProductRequestData();
$product1->setProductCode("test1");
$product1->setOriginalListPrice(91.32);
$product1->setOriginalSalePrice(91.32);
$product1->setIsFixedPrice(false);
$products[] = $product1;

$product2 = new Common\Request\ProductRequestData();
$product2->setProductCode("test2");
$product2->setOriginalListPrice(13.59);
$product2->setOriginalSalePrice(11.59);
$product2->setIsFixedPrice(false);
$products[] = $product2;

$product3 = new Common\Request\ProductRequestData();
$product3->setProductCode("test3");
$product3->setOriginalListPrice(420.00);
$product3->setOriginalSalePrice(567.00);
$product3->setIsFixedPrice(true);
$products[] = $product3;

$results = $sdk->Browsing()->GetProductsInformation($products,true);

$currencies = $sdk->Browsing()->GetCurrencies();

?>

<ul>
    <?php foreach($results->getData() as $item): ?>
    <li class="item">
        <h3 class="product-name"><a title="Chelsea Tee" href="#"><?php echo $item->getProductCode(); ?></a></h3>
        <a class="product-image" title="Chelsea Tee" href="#">
            <img alt="Chelsea Tee" src="mtk004t.jpg">
        </a>
        <div class="product-info" style="padding-bottom: 88px; min-height: 168px;">
            <div class="price-box">
            <span id="product-price-410-widget-new-grid" class="regular-price">
                <span class="price"><?php echo $currencies->getData()->getShortSymboledAmount($item->getPrice()) ?></span>
            </span>
            </div>
            <div class="actions" style="">
                <button onclick="window.location = 'checkout.php?productCode=<?php echo $item->getProductCode() ?>'" class="button btn-cart" title="Add to Cart & Checkout" type="button"><span><span>Add to Cart & Checkout</span></span></button>
            </div>
        </div>
    </li>
    <?php endforeach; ?>
</ul>


<script type="text/javascript" >
    <?php echo $sdk->Browsing()->LoadClientSDK()->getData(); ?>
</script>

</body>
</html>