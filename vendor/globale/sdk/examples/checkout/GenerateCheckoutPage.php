<?php

// ### Initialization ###
require '../../vendor/autoload.php';
use GlobalE\SDK\SDK;
$SDK = new SDK(1.1, 'USD');

// ### Settings ###
use GlobalE\SDK\Core\Settings;
echo 'Merchant GUID: ' . Settings::get('MerchantGUID') . '<br />';
echo 'Log will be written into: ' . Settings::get('Log.Path') . '<br />';
echo 'Cache will be written into: ' . Settings::get('Cache.Path') . '<br />';

// ### Log ###
use GlobalE\SDK\Core\Log;
Log::log('Log example.', Log::LEVEL_INFO);

// ### Cache ###
use GlobalE\SDK\Core\Cache;
$value_from_cache = Cache::get('example-cache-key');
if(empty($value_from_cache)){
    Cache::set('example-cache-key', 'example-cache-value');
    echo 'Cache is set. On next page load it should take value from cache';
}
else{
    echo 'Value from cache: '.$value_from_cache;
}

// ### Profiler ###
use GlobalE\SDK\Core\Profiler;
Profiler::startTimer('testProfiler');
Profiler::endTimer('testProfiler');
?>

<br /><br />
Go to <a href="initialization.php?GlobalE_Profiler=1">initialization.php?GlobalE_Profiler=1</a> to see the profiler.


<!-- application -->


<br /><br />
<h3><u>GenerateCheckoutPage Example:</u></h3>

<br />Request: <br />
$SDK->Browsing()->OnPageLoad();<br />
$SDK->Browsing()->LoadClientSDK();<br />
$SDK->Checkout()->GenerateCheckoutPage('c5068760-f662-49a7-9e49-cab98c097fd1');<br />

<hr /><br />Response: <br />
<pre>
    <?php var_dump($SDK->Checkout()->GenerateCheckoutPage('c5068760-f662-49a7-9e49-cab98c097fd1')); ?>
</pre>

<hr /><br />Client Response: <br />

<script type="text/javascript">
    <?php $SDK->Browsing()->OnPageLoad(); ?>
    <?php echo $SDK->Browsing()->LoadClientSDK()->getData(); ?>
    <?php echo $SDK->Checkout()->GenerateCheckoutPage('c5068760-f662-49a7-9e49-cab98c097fd1')->getData(); ?>
</script>

<div id="checkoutContainer" style="width:50%;margin: auto;"></div>
