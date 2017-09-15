<?php

include_once(dirname(__FILE__).'/../SweetTooth.php');

// PLUG YOUR CREDS IN HERE
$apiKey = '';
$apiSecret = '';
$subdomain = '';
$stdout = fopen('php://output', 'w');

if (!$apiKey || !$apiSecret || !$subdomain) {
    $content = "You need to enter the apiKey, apiSecret, and subdomain in transfer.php.
        If you don't have channel keys yet, run channel.php to create one.
    ";
    fwrite($stdout, $content);
    fclose($stdout);
    return;
}

// Instanciate new SweetTooth with account credentials
$st = new SweetTooth($apiKey, $apiSecret, $subdomain);
$transferData = generateTransferData();

$content = "
    <div>
        Creating transfer with data:<br/>
        <pre>" . print_r($transferData, true) . "</pre>
    </div>
    <br/>
";

fwrite($stdout, $content);
try {
    // Create a magento channel for our new account
    $transfer = $st->transfer()->create($transferData);
} catch (Exception $e) {
    $content = 'Error creating your transfer: ' . $e->getMessage();
    fwrite($stdout, $content);
    fclose($stdout);
    return;
}

// Awesome, your account and channel was created!
$content = "
   <div>
       Transfer info:<br/>
       <pre>" . print_r($transfer, true) . "</pre>
   </div>
   <br/>
";

fwrite($stdout, $content);
fclose($stdout);

/*
 * Below are function to generate sample data
 */
function generateTransferUser()
{
    $firstNamesArray = array("John", "Sally", "Herman", "Allison", "Annie", "Jeanne");
    $lastNamesArray = array("Bilkes", "Foo", "Bar", "Carlson", "Kennedy", "Mitchell");
  
    $firstName = strtolower($firstNamesArray[array_rand($firstNamesArray, 1)]);
    $lastName = strtolower($lastNamesArray[array_rand($lastNamesArray, 1)]);
  
    $transferUserData = array(
        "firstName" => $firstName, 
        "lastName" => $lastName,
        "password" => "password1",
        "username" => "sdk" . $firstName . $lastName . rand(1,10000000000),
        "email" => "sdk" . $firstName . $lastName . rand(1,10000000000) . "@example.com",
        "id" => rand(1,100000000000000)
    );
  
    return $transferUserData;
}

function generateTransferData()
{    
    $reference = array(array()); //Server requires an array of references
    $transferUser = generateTransferUser();
  
    $transferData = array(
        "comments" => "SDK Example Data",
        "quantity" => rand(1,1000),
        "channel_transfer_id" => rand(1,1000000000),
        "channel_user_id" => $transferUser['id'],
        "effective_start" => "2012-03-02 11:06:19",
        "expire_date" => "2012-03-02 11:06:20",
        "status" => "5", //Status 4: transfer pending, Status 5: transfer accepted
        "currency_id" => "1",
        "reason_id" => "1",
        "issued_by" => "",
        "last_update_by" => "",
        "reference" => $reference, 
        "user" => $transferUser
    );
  
    return $transferData;
}

