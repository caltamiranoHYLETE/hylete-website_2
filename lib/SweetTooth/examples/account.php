<?php

include_once(dirname(__FILE__).'/../SweetTooth.php');

// Instanciate the SweetTooth SDK
$st = new SweetTooth();
$accountData = generateAccountData();
$stdout = fopen('php://output', 'w');

$content = "
    <div>
        Creating account with data:<br/>
        <pre>" . print_r($accountData, true) . "</pre>
    </div>
    <br/>
";

fwrite($stdout, $content);
try {
    // This is all it takes to create the account
    $account = $st->account()->create($accountData);
} catch (Exception $e) {
    fwrite($stdout, 'Error creating your account: ' . $e->getMessage());
    fclose($stdout);
    return;
}

// Awesome, your account was created!
// TODO: change the hardcoded url to the base_url that will be returned in the account resource
$content = "
    <div>
       New account created!<br/>
       <pre>" . print_r($account, true) . "</pre>
    </div>
    <br/>
    <b>Next Step: Paste the following into channel.php to connect a channel to this account,</b>
<pre>
\$apiKey = '" . $accountData['username'] . "';
\$apiSecret = '" . $accountData['password'] . "';
\$subdomain = '" . $accountData['username'] . "';
</pre>
";

fwrite($stdout, $content);
fclose($stdout);

/*
 * Below are function to generate sample data
 */

function generateAccountData()
{
    // Create dummy account data
    $username = 'sdkaccount' . rand(1,1000000);
    $accountData = array (
        'username' => $username,
        'email'     => $username . '@example.com',
        'password'  => 'password1' // Just to keep things simple
    );

    return $accountData;
}
