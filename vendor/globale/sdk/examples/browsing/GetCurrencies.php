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
<h3><u>GetCurrencies Example:</u></h3>

<br />Request: <br />
$SDK->Browsing()->OnPageLoad();<br />
1) $SDK->Browsing()->GetCurrencies(); <br /><br />
2) $SDK->Browsing()->GetCurrencies()->getShortSymboledAmount(20); <br /><br />
3) $SDK->Browsing()->GetCurrencies()->getLongSymboledAmount(20); <br /><br />

<hr /><br />Response: <br />
<pre>
    <?php
    $SDK->Browsing()->OnPageLoad();
    $SDK->Browsing()->Initialize(1.1, 'EUR', 'IT', 'en-GB');
    var_dump($SDK->Browsing()->GetCurrencies());
    ?><hr />
    <?php
    var_dump($SDK->Browsing()->GetCurrencies()->getData()->getShortSymboledAmount(20));
    ?><hr />
    <?php
    var_dump($SDK->Browsing()->GetCurrencies()->getData()->getLongSymboledAmount(20));
    ?>
</pre>
