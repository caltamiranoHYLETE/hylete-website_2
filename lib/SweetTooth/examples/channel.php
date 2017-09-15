<?php

include_once(dirname(__FILE__).'/../SweetTooth.php');

// PLUG YOUR ACCOUNT INFO IN HERE
$apiKey = '';
$apiSecret = '';
$subdomain = '';
$stdout = fopen('php://output', 'w');

if (!$apiKey || !$apiSecret || !$subdomain) {
    $content = "You need to enter the apiKey, apiSecret, and subdomain in channel.php.
        If you don't have an account yet, run account.php to create one.
    ";
    
    fwrite($stdout, $content);
    fclose($stdout);
    return;
}

// Instanciate new SweetTooth with account credentials
$st = new SweetTooth($apiKey, $apiSecret, $subdomain);

$channelData = array (
    'channel_type' => 'magento'
);

$content = "
    <div>
        Creating channel with data:<br/>
        <pre>" . print_r($channelData, true) . "</pre>
    </div>
    <br/>
";

fwrite($stdout, $content);
try {
    // Create a magento channel for our new account
    $channel = $st->channel()->create($channelData);
} catch (Exception $e) {
    fwrite($stdout, 'Error creating your channel: ' . $e->getMessage());
    fclose($stdout);
    return;
}

// Awesome, your account and channel was created!
$content = "
    <div>
       Channel info:<br/>
       <pre>" . print_r($channel, true) . "</pre>
    </div>
    <br/>
    <b>Next Step: Paste the following into transfer.php to make calls on behalf of this channel,</b>
<pre>
\$apiKey = '" . $channel['api_key'] . "';
\$apiSecret = '" . $channel['api_secret'] . "';
\$subdomain = '" . $subdomain . "';
</pre>
";

fwrite($stdout, $content);
fclose($stdout);

