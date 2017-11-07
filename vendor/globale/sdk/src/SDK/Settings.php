<?php
/**
 * SDK - Main settings
 */
return array(
    'EnvironmentType' => 'prod',
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
    'Base' => array( // TODO: check usage.
        'VatRate'  => null, // 20 for 20%
        'Country'  => null, // 'GB'
        'Currency' => null, // 'GBP'
        'Culture'  => null // 'en-GB'
	),
    'API' => array(
        'BaseUrl'        => 'https://example.com/',
        'ConnectionType' => 'Curl', // Available: Curl, Stub
        'Timeout'        => 10 ,// Max amount of seconds to wait for an answer from the API.
        'BulkSize'       => 50, //usage in getProductCountryS call
        'StubTTL'        => null,
        'AlwaysUseOriginalCultureCode' => false,
        /**
         * Send cart version switch API endpoints Checkout/SendCartV and Checkout/SendCartV2
         * supported versions: 1 and 2
         * by default, if not set, will be used version 1
         */
        'SendCartVersion' => 1
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
        'Enable'         => false,
        'Type'           => 'File',
        'Path'           => '/var/globale/log',
        'MaxFilesAmount' => 100, // Max files logger will save(there is one file per day).
        'Level'          => 100 // Will print all above.
	),
    'Cache' => array(
        'Enable'          => false,
        'Type'            => 'File',
        'Path'            => '/var/globale/cache',
        'FilePermissions' => 0777,
        'DirPermissions'  => 0777
	),
    'Profiler' => array(
        'Enable'     => true,
        'AlwaysShow' => false
	),
    'Validator' => array(
      'Enable' => false
	),
    'Cookies' => array(
        'DefaultName' => 'GlobalE_Data',
        'Expire'      =>  (86400 * 3),
        'Domain'      =>  false,
        'Path'        =>  '/'
	),
    'Frontend' => array(
        'BaseUrl'          => 'https://example.com/',
        'MerchantClient'   => 'Scripts/Merchants/globale.merchant.client.js',
        'WelcomePopup'     => 'Merchant/Script/welcome',
        'ShippingSwitcher' => 'Merchant/ChangeShippingAndCurrency',
        'Checkout'         => 'Merchant/Script/Checkout'
	),
    /**
     * Mapping platform culture code
     * to Global-e culture code
     * plaform code => Global-e code
     */
    'Culture' => array(
        'en_GB' => 'en-GB',
        'en_US' => 'en-GB',
        'ar_AR' => 'ar',
        'zh_CHS'=> 'zh-CHS',
        'de_DE' => 'de',
        'es_ES' => 'es',
        'fr_FR' => 'fr',
        'it_IT' => 'it',
        'ja_JA' => 'ja',
        'pt_PT' => 'pt',
        'ru_RU' => 'ru',
        'he_HE' => 'he',
        'ko_KO' => 'ko-KR'
	),
);