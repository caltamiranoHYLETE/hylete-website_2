<?php
/**
 * Subscribe Pro - Subscriptions Management Extension
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of SUBSCRIBE PRO INC.
 *
 * @category  SubscribePro
 * @package   SubscribePro_Autoship
 * @author    Subscribe Pro Support <support@subscribepro.com>
 * @copyright 2009-2020 SUBSCRIBE PRO INC. All Rights Reserved.
 * @license   http://www.subscribepro.com/terms-of-service/ Subscribe Pro Terms of Service
 * @link      http://www.subscribepro.com/
 *
 */

class SubscribePro_Autoship_Helper_Api extends Mage_Core_Helper_Abstract
{

    // Cache Type Tags
    const CACHE_TYPE_CONFIG         = 'SP_CONFIG';
    const CACHE_TYPE_PRODUCTS       = 'SP_PRODUCTS';

    const CACHE_TOKEN_TAG           = 'autoship_api_access_token';

    /** @var Mage_Core_Model_Store|null */
    private $store = null;
    private $accountConfig = array();


    /**
     * Set the primary key id of the store to use for all configuration scope
     *
     * @param int|Mage_Core_Model_Store $store Primary key id of the store to use
     */
    public function setConfigStore($store)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $this->store = $store;
        }
        else {
            $this->store = Mage::app()->getStore($store);
        }
    }

    /**
     * Return the store to use for pulling configuration settings
     *
     * @return Mage_Core_Model_Store
     */
    public function getConfigStore()
    {
        if ($this->store == null) {
            $this->store = Mage::app()->getStore();
        }
        return $this->store;
    }

    /**
     * @return string
     */
    public function getApiBaseUrl()
    {
        // Build url
        $baseUrl =
            Mage::getStoreConfig('autoship_general/developer/api_protocol') .
            '://' .
            Mage::getStoreConfig('autoship_general/platform_api/platform_host', $this->getConfigStore())
        ;

        return $baseUrl;
    }

    /**
     * @param string $timeoutType - Either 'api' or 'payments_api'
     * @return \SubscribePro\Sdk
     */
    public function getSdk($timeoutType = 'api')
    {
        /** @var Mage_Core_Helper_Data $coreHelper */
        $coreHelper = Mage::helper('core');

        // Lookup credentials
        $clientId = $coreHelper->decrypt(Mage::getStoreConfig('autoship_general/platform_api/client_id', $this->getConfigStore()));
        $clientSecret = $coreHelper->decrypt(Mage::getStoreConfig('autoship_general/platform_api/client_secret', $this->getConfigStore()));
        // Build url
        $baseUrl = $this->getApiBaseUrl();
        // Get config setting re: logging request
        $bLogRequest = Mage::getStoreConfig('autoship_general/platform_api/log_request', $this->getConfigStore()) == '1';
        // Get full path to log file
        $logFilePath = Mage::getBaseDir('var') . DS . 'log' . DS . SubscribePro_Autoship_Helper_Data::API_LOG_FILE;
        // Log message format
        $messageFormat = "SUBSCRIBE PRO REST API Call: {method} - {uri}\nRequest body: {req_body}\n{code} {phrase}\nResponse body: {res_body}\n{error}\n";
        // Get request timeout
        if ($timeoutType == 'payments_api') {
            $requestTimeout = Mage::getStoreConfig('autoship_general/advanced/payments_api_request_timeout', $this->getConfigStore());
        }
        else {
            $requestTimeout = Mage::getStoreConfig('autoship_general/advanced/api_request_timeout', $this->getConfigStore());
        }

        // Create SDK object
        // Setup with Platform API base url and credentials from Magento config
        $sdk = new \SubscribePro\Sdk(array(
            'base_url' => $baseUrl,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'logging_enable' => $bLogRequest,
            'logging_file_name' => $logFilePath,
            'logging_message_format' => $messageFormat,
            'api_request_timeout' => $requestTimeout,
        ));

        return $sdk;
    }

    /**
     * @return bool|array
     */
    public function getAccountConfig()
    {
        /** @var Mage_Core_Helper_Data $coreHelper */
        $coreHelper = Mage::helper('core');

        // Lookup client ID for configured SP account
        $clientId = $coreHelper->decrypt(Mage::getStoreConfig('autoship_general/platform_api/client_id', $this->getConfigStore()));

        // Save config in member, so it is only fetched once per request, even if cache disabled
        if (isset($this->accountConfig[$clientId])) {
            return $this->accountConfig[$clientId];
        }
        else {
            // Attempt cache load
            $accountConfig = $this->getCacheHelper()->loadCache(
                'account_config_' . $clientId,
                SubscribePro_Autoship_Helper_Cache::CACHE_TYPE_CONFIG);

            // Check if product found in cache
            if ($accountConfig !== false) {
                // Found in cache, decode
                $accountConfig = unserialize($accountConfig);
            } else {
                // Config not found in cache
                // Request from API
                $accountConfig = $this->getSdk()->getConfigTool()->load();
                // Save product in cache
                $this->getCacheHelper()->saveCache(
                    serialize($accountConfig),
                    'account_config_' . $clientId,
                    SubscribePro_Autoship_Helper_Cache::CACHE_TYPE_CONFIG);
            }

            // Save in member
            $this->accountConfig[$clientId] = $accountConfig;

            // Return config
            return $accountConfig;
        }
    }

    /**
     * @return SubscribePro_Autoship_Helper_Cache
     */
    protected function getCacheHelper()
    {
        /** @var SubscribePro_Autoship_Helper_Cache $helper */
        $helper = Mage::helper('autoship/cache');

        return $helper;
    }

    /**
     * @param $jsonString
     * @return mixed
     */
    protected function sanitizeJsonString($jsonString)
    {
        $jsonString = preg_replace('/"creditcard_number"\s*\:\s"[0-9]+"/', '"creditcard_number":"XXXXXXXXXXXXXXXX"', $jsonString);
        $jsonString = preg_replace('/"creditcard_verification_value"\s*\:\s*"[0-9]+"/', '"creditcard_verification_value":"XXX"', $jsonString);
        return $jsonString;
    }

    /**
     * Format a flat JSON string to make it more human-readable
     * This function is taken from: https://github.com/GerHobbelt/nicejson-php/blob/master/nicejson.php
     *
     * @param string $json The original JSON string to process
     *        When the input is not a string it is assumed the input is RAW
     *        and should be converted to JSON first of all.
     * @return string Indented version of the original JSON string
     */
    protected function json_format($json) {
        if (!is_string($json)) {
            if (phpversion() && phpversion() >= 5.4) {
                return json_encode($json, JSON_PRETTY_PRINT);
            }
            $json = json_encode($json);
        }
        $result      = '';
        $pos         = 0;               // indentation level
        $strLen      = strlen($json);
        $indentStr   = "\t";
        $newLine     = "\n";
        $prevChar    = '';
        $outOfQuotes = true;

        for ($i = 0; $i < $strLen; $i++) {
            // Grab the next character in the string
            $char = substr($json, $i, 1);

            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;
            }
            // If this character is the end of an element,
            // output a new line and indent the next line
            else if (($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos--;
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }
            // eat all non-essential whitespace in the input as we do our own here and it would only mess up our process
            else if ($outOfQuotes && false !== strpos(" \t\r\n", $char)) {
                continue;
            }

            // Add the character to the result string
            $result .= $char;
            // always add a space after a field colon:
            if ($char == ':' && $outOfQuotes) {
                $result .= ' ';
            }

            // If the last character was the beginning of an element,
            // output a new line and indent the next line
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos++;
                }
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }
            $prevChar = $char;
        }

        return $this->sanitizeJsonString($result);
    }

}
