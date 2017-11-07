<?php
/**
 * SDK - Test environment settings
 */
return array(
    'EnvironmentType' => 'test',
    'EnableGlobalESDK' => true,
    'MerchantID' => 0,
    'MerchantGUID' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    'EnvDetails' => array(
		'Version'                 => '2.1.0',
		'LastUpdated'             => '02/11/2017',
		'WebStoreCode'            => '',
		'WebStoreInstanceCode'    => ''
    ),
    'Controllers' => array(
        'Browsing' => true,
        'Checkout' => true,
        'Merchant' => true,
        'Admin'    => true,
    ),
    'Base' => array(
        'VatRate'  => 20,
        'Country'  => 'GB',
        'Currency' => 'GBP',
        'Culture'  => 'en-GB'
    ),
    'API' => array(
        'BaseUrl'        => 'https://connect-qa.bglobale.com/',
        'ConnectionType' => 'Stub', // Available: Curl, Stub
        'Timeout'        => 10, // Max amount of seconds to wait for an answer from the API.
        'BulkSize'       => 2, //usage in getProductCountryS call
        'StubTTL'        => 5,
        /**
         * Send cart version switch API endpoints Checkout/SendCartV and Checkout/SendCartV2
         * supported versions: 1 and 2
         * by default, if not set, will be used version 1
         */
        'SendCartVersion' => 1,
    ),
    /*
     * Minimal Threshold Level:
     * DEBUG = 100
     * INFO = 200
     * NOTICE = 250
     * WARNING = 300
     * ERROR = 400
     * CRITICAL = 500
     * ALERT = 550
     * EMERGENCY = 600
     */
    'Log' => array(
        'Enable' => true,
        'Type'           => 'File',
        'Path'           => '/var/globale/unittests/log',
        'MaxFilesAmount' => 100, // Max files logger will save(there is one file per day).
        'Level'          => 100 // Will print all above.
    ),
    'Cache' => array(
        'Enable' => true,
        'Type'            => 'File',
        'Path'            => '/var/globale/unittests/cache',
        'FilePermissions' => 0777,
        'DirPermissions'  => 0777
    ),
    'Profiler' => array(
        'Enable' => true,
        'AlwaysShow' => false
    ),
    'Validator' => array(
        'Enable' => true
    ),
    'Cookies' => array(
        'DefaultName' => 'GlobalE_Data',
        'Expire'      =>  (86400 * 30),
        'Domain'      =>  false,
        'Path'        =>  '/'
    ),
    'Frontend' => array(
        'BaseUrl'          => 'https://qa.bglobale.com/',
        'MerchantClient'   => 'Scripts/Merchants/globale.merchant.client.js',
        'WelcomePopup'     => 'Merchant/Script/welcome',
        'ShippingSwitcher' => 'Merchant/ChangeShippingAndCurrency',
        'Checkout'         => 'Merchant/Script/Checkout'
    ),
);