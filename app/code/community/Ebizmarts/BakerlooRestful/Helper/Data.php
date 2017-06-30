<?php

class Ebizmarts_BakerlooRestful_Helper_Data extends Mage_Core_Helper_Abstract {



    private $EncryptationKey              = "gda7asvdsa76gd7a";
    private $EncryptationIV               = "0d6Hs4L1opAqwte8";
    private $ActivationKeyExpirationHours = 24;

    /**
     * Check if a product is associated to a given store id.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $storeId
     * @return bool
     */
    public function productIsInStore(Mage_Catalog_Model_Product $product, $storeId) {
        return in_array($storeId, $product->getStoreIds());
    }

    /**
     * Check if a pin code for admin is unique.
     */
    public function existsPin($pincode, Ebizmarts_BakerlooRestful_Model_Pincode $model) {
        $pins = Mage::getModel('bakerloo_restful/pincode')->getCollection();

        $exists = false;

        foreach ($pins as $pin) {
            if($model->getId() != $pin->getId() and (int)$pin->getPincode() == $pincode) {
                $exists = true;
                break;
            }
        }

        return $exists;
    }

    public function getActivationKeyExpirationHours() {
        return $this->ActivationKeyExpirationHours;
    }

    public function getResizedImageUrl($productId, $storeId, $imagePath, $width, $height, $categoryId = null) {

        $imagesControllerPath = Mage::getModel('bakerloo_restful/api_api')->getImagesPath();

        //Mage_Core_Helper_Data
        $ch = Mage::helper('core/url');

        $params = array(
            'f' => $ch->urlEncode($imagePath),
            'w' => $width,
            'h' => $height
        );

        if(is_null($categoryId)) {
            $params['p'] = $ch->urlEncode($productId);
        }
        else {
            $params['c'] = $ch->urlEncode($categoryId);
        }

        $params['_nosid'] = true;

        $url = Mage::getModel('core/store')->load($storeId)->getUrl($imagesControllerPath, $params);

        return $url;
    }

    public function getStoreIdHeader() {
        return 'B-Store-Id';
    }

    public function getApiKeyHeader() {
        return 'B-Api-Key';
    }

    public function getActivationKeyHeader() {
        return 'B-Activation-Key';
    }

    public function getUsernameHeader() {
        return 'B-Username';
    }

    public function getUsernameAuthHeader() {
        return 'B-Username-Auth';
    }

    public function getDeviceIdHeader() {
        return 'B-Device-Id';
    }

    public function getDeviceNameHeader() {
        return 'B-Device-Name';
    }

    public function getUserAgentHeader() {
        return 'B-User-Agent';
    }

    public function getLatitudeHeader() {
        return 'B-Latitude';
    }

    public function getLongitudeHeader() {
        return 'B-Longitude';
    }

    public function getApiVersionHeader() {
        return 'B-Api-Version';
    }

    public function getMagentoVersionHeader() {
        return 'B-Magento-Version';
    }

    public function getMagentoVersionCode() {
        $modules = (array) Mage::getConfig()->getNode('modules')->children();
        $flavour = (array_key_exists('Enterprise_Enterprise', $modules)) ? 'EE' : 'CE';

        return $flavour;
    }

    public function allPossibleHeaders() {
        return array(
                     'B-Store-Id', 'B-Api-Key', 'B-Username',
                     'B-Username-Auth', 'B-Device-Id', 'B-User-Agent',
                     'B-Latitude', 'B-Longitude', 'B-Api-Version'
        );
    }

    public function getApiModuleVersion() {
        return (string)Mage::getConfig()->getNode('modules/Ebizmarts_BakerlooRestful/version');
    }

    public function getUserAgent() {
        $v = $this->getApiModuleVersion();
        return "Ebizmarts/BakerlooRestful (v{$v})";
    }

    public function getApiKey($storeId = null) {
        return Mage::helper('core')->decrypt($this->config("general/api_key", $storeId));
    }

    public function getActivationKey($websiteId = null) {
        return Mage::helper('core')->decrypt($this->config("general/activation_key", $websiteId));
    }

    public function apiGenUrl() {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/bakerloo/generatekey', array('_secure' => true));
    }

    public function activationGenUrl() {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/bakerloo/generateactivationkey', array('_secure' => true));
    }

    public function config($path, $storeId = null) {
        return Mage::getStoreConfig("bakerloorestful/$path", $storeId);
    }

    public function validateMysqlTimestamp($string) {
        return preg_match('/^\d{4}(-)\d{2}(-)\d{2}\s{1}\d{2}(:)\d{2}(:)\d{2}$/', $string) === 1;
    }

    public function encryptActivationKey($data) {

        $blocksize = 16; // AES-128
        $pad = $blocksize - (strlen($data) % $blocksize);
        $data = $data . str_repeat(chr($pad), $pad);
        return bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->EncryptationKey, $data, MCRYPT_MODE_CBC, $this->EncryptationIV));
    }

    public function decryptActivationKey($data) {

        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->EncryptationKey, pack('H*', $data), MCRYPT_MODE_CBC, $this->EncryptationIV);
        //$block = 16;
        $pad = ord($decrypted[($len = strlen($decrypted)) - 1]);
        $decrypted = substr($decrypted, 0, strlen($decrypted) - $pad);
        return $decrypted;

    }

    /**
     * @param $object
     * @param null $apiResource
     * @return int|null
     * @throws Exception
     */
    public function debug($object, $apiResource = null) {

        if((int)$this->config("general/debug") === 0) {
            return null;
        }

        $debugId = (int)Mage::registry('brest_request_id');

        $debug = Mage::getModel('bakerloo_restful/debug');

        if($debugId)
            $debug->load($debugId);

        if(!Mage::registry('brest_request_time'))
            Mage::register('brest_request_time', microtime(true));

        if($object instanceof Mage_Core_Controller_Response_Http) {

            if($object->getHttpResponseCode())
                $debug->setResponseCode($object->getHttpResponseCode());

            $debug
            ->setResponseHeaders(json_encode($object->getHeaders()))
            ->setResponseBody($object->getBody())//Body is already a json.
            ->save();
        }
        else {

            //Mage_Core_Controller_Request_Http

            if($apiResource)
                $debug->setData('resource', $apiResource);

            $debug->setRemoteAddr( Mage::helper('core/http')->getRemoteAddr(true) );
            $debug->setUserAgent($object->getHeader($this->getUserAgentHeader()));
            $debug->setRequestMethod($object->getMethod());

            //TODO
            //$debug->setCallTime();

            $params = $object->getParams();
            $bodyDecoded = json_decode(strip_tags($object->getRawBody()));

            if(is_object($bodyDecoded)) {
                $bodyDecoded->{"__get_parameters__"} = $params;

                if(isset($bodyDecoded->attachments)) {
                    if(isset($bodyDecoded->attachments[0])) {
                        $bodyDecoded->attachments[0]->content = substr($bodyDecoded->attachments[0]->content, 0, 50) . '...';
                    }
                }
            }
            else
                $bodyDecoded['__get_parameters__'] = $params;

            switch($object->getMethod()) {
                case 'GET':
                case 'DELETE':
                    $debug->setRequestBody(json_encode($params));
                break;
                case 'PUT':
                case 'POST':
                    $debug->setRequestBody(json_encode($bodyDecoded));
                    break;
            }

            $headers = $this->allPossibleHeaders();
            $saveHeaders = array();
            foreach($headers as $_header) {
                $saveHeaders[$_header] = $object->getHeader($_header);
            }
            $debug->setRequestHeaders(json_encode($saveHeaders));

            $debug->save();
        }

        return $debug->getId();

    }

    public function createCustomer($websiteId, $data, $password = null) {

        if(is_null($password)) {
            $password = substr(uniqid(), 0, 8);
        }

        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId($websiteId);

        //Create customer
        $customer->setPassword($password);
        $customer->setConfirmation($password);
        $customer->setId(null);

        if(!isset($data->customer->group_id))
            Mage::throwException($this->__("Please provide Customer Group ID."));
        else
            $customer->setGroupId((int)$data->customer->group_id);

        $customer->setEmail((string)$data->customer->email);
        $customer->setFirstname((string)$data->customer->firstname);
        $customer->setLastname((string)$data->customer->lastname);

        //Subscribe customer to newsletter on creation
        if (isset($data->customer->subscribed_to_newsletter) and ((bool)$data->customer->subscribed_to_newsletter) === true) {
            $customer->setIsSubscribed(1);
        }

        $customer->save();

        //Send welcome email if enabled in config.
        Mage::helper('bakerloo_restful/email')->sendWelcome($customer);

        return $customer;
    }

    public function jsonError($message) {
        return array("error" => array("message" => $message));
    }

    public function encodeResponse($data) {
        return json_encode($data);
    }

    public function isCallAllowed($request, $storeId = null, $apiKeyOverride = null) {
        $allow = true;

        $allowedIps = $this->config("general/allow_ips", $storeId);
        $remoteAddr = Mage::helper('core/http')->getRemoteAddr();
        if (!empty($allowedIps) && !empty($remoteAddr)) {
            $allowedIps = preg_split('#\s*,\s*#', $allowedIps, null, PREG_SPLIT_NO_EMPTY);
            if (array_search($remoteAddr, $allowedIps) === false
                && array_search(Mage::helper('core/http')->getHttpHost(), $allowedIps) === false) {

                Mage::throwException("API access denied: Invalid IP.");

            }
        }

        //Validate API Header if IP validated
        if(true === $allow) {

            if(is_null($apiKeyOverride)) {
                $apiKey = $request->getHeader($this->getApiKeyHeader());
            }
            else {
                $apiKey = $apiKeyOverride;
            }

            if((false === $apiKey) or ($this->getApiKey() != $apiKey)) {
                Mage::throwException("API access denied: Invalid API key.");
            }

        }

        return $allow;
    }

    public function isModuleInstalled($moduleName) {
        return Mage::getConfig()->getNode("modules/{$moduleName}");
    }

    /**
     * Notifications severity options
     */
    public function getSeverityOptions() {
        return array(
                    1 => Mage::helper('bakerloo_restful')->__('CRITICAL'),
                    2 => Mage::helper('bakerloo_restful')->__('MAJOR'),
                    3 => Mage::helper('bakerloo_restful')->__('MINOR'),
                    4 => Mage::helper('bakerloo_restful')->__('NOTICE')
        );
    }

    public function getSeverityOption($id) {
        $options = $this->getSeverityOptions();

        return (isset($options[$id]) ? $options[$id] : null);
    }

    public function getMagentoDomain() {

        //if custom url is set, use it
        $customPosUrl = trim($this->config('general/fixed_url'));

        if(!empty($customPosUrl)) {
            $magentoDomain = $customPosUrl;
        }
        else {
            $useStoreCode = (int)Mage::getStoreConfig('web/url/use_store');

            $redirectToBase    = (int)Mage::getStoreConfig('web/url/redirect_to_base') != 0;
            $useCustomAdminUrl = (int)Mage::getStoreConfig('admin/url/use_custom') === 1;

            $storeId = 0;

            if($useStoreCode or ($useCustomAdminUrl and $redirectToBase)) {
                $websites     = Mage::app()->getWebsites(false);
                $firstWebsite = current($websites);
            }
            else {
                $websites     = Mage::app()->getWebsites(true, 'admin');
                $firstWebsite = $websites['admin'];
            }

            foreach($firstWebsite->getStores() as $_store) {

                if($_store->getIsActive()) {
                    $storeId = $_store->getStoreId();
                    break;
                }

            }

            $magentoDomain = Mage::getModel('core/url')->setStore($storeId)->getUrl("/", array('_nosid' => true, '_secure' => true));

        }

        return $magentoDomain;
    }

    public function getStoreAddress($storeId) {
        $store = Mage::getModel('core/store')->load($storeId);

        $address = array();

        $address['address_street'] = $store->getConfig('general/store_information/address');
        $address['country']        = (string)$store->getConfig('general/country/default');
        $address['postal_code']    = $store->getConfig('general/store_information/postal_code');
        $address['region_id']      = $store->getConfig('general/store_information/region_id');
        $address['telephone']      = $store->getConfig('general/store_information/phone');

        return $address;
    }

    /**
     * Return config value for "Import orders regardless of stock level"
     *
     * @param null $storeId
     * @return bool
     */
    public function dontCheckStock($storeId = null) {
        return ((int)$this->config("checkout/always_in_stock", $storeId) === 1);
    }

    /**
     * Return config value for "Update stock availability on credit memo creation"
     *
     * @param null $storeId
     * @return bool
     */
    public function updateStockAvailability($storeId = null) {
        return ((int)$this->config("checkout/stock_availability", $storeId) === 1);
    }

    /**
     * Return config value for "Import orders regardless of stock level"
     *
     * @param null $storeId
     * @return bool
     */
    public function dontSubtractInventory($storeId = null) {
        return ((int)$this->config("catalog/subtract_inventory", $storeId) === 1);
    }

    public function encryptOrderBackupFile($data) {

        $blocksize = 16; // AES-128
        $pad = $blocksize - (strlen($data) % $blocksize);
        $data = $data . str_repeat(chr($pad), $pad);
        return bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->EncryptationKey, $data, MCRYPT_MODE_CBC, $this->EncryptationIV));
    }

    public function decryptOrderBackupFile($data) {

        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->EncryptationKey, pack('H*', $data), MCRYPT_MODE_CBC, $this->EncryptationIV);
        //$block = 16;
        $pad = ord($decrypted[($len = strlen($decrypted)) - 1]);
        $decrypted = substr($decrypted, 0, strlen($decrypted) - $pad);
        return $decrypted;

    }

    public function getProductBarcode($productId, $storeId = null) {

        $_resource = Mage::getSingleton('catalog/product')->getResource();

        $config = (string)Mage::helper('bakerloo_restful')->config('catalog/product_code', $storeId);

        $attributes = $this->getBarcodeConfig($config);

        $ret = '';

        if( is_array($attributes) and (count($attributes) === 1) ) {
            $ret = $_resource->getAttributeRawValue($productId, $attributes[0], $storeId);
        }
        else {

            $temp = array();

            for ($i = 0; $i < count($attributes); $i++) {
                $tempVal = $_resource->getAttributeRawValue($productId, $attributes[$i], $storeId);

                if( empty($tempVal) )
                    continue;

                array_push($temp, $tempVal);
            }

            $ret = implode(',', $temp);

        }

        return $ret;

    }

    /**
     * @param null $config
     * @return array
     */
    public function getBarcodeConfig($config = null) {

        $ret = array();

        if(empty($config)) {
            return $ret;
        }

        if (strpos($config, ',') === false) {
            array_push($ret, $config);
        }
        else {
            $ret = explode(',', $config);
        }

        return $ret;
    }

    public function getStoreCreditExtension() {
        $extension = false;

        $storeCreditAvailable = Mage::helper('bakerloo_restful')->isModuleInstalled('Enterprise_CustomerBalance');
        if($storeCreditAvailable)
            $storeCreditAvailable = Mage::helper('core')->isModuleEnabled('Enterprise_CustomerBalance');

        //if using Enterprise, don't check for other extensions
        if(!$storeCreditAvailable) {
            $magestoreCreditAvailable = Mage::helper('bakerloo_restful')->isModuleInstalled('Magestore_Customercredit');

            if ($magestoreCreditAvailable)
                $magestoreCreditAvailable = Mage::helper('core')->isModuleEnabled('Magestore_Customercredit');

            if($magestoreCreditAvailable)
                $extension = 'Magestore_Customercredit';
        }
        else
            $extension = 'Enterprise_CustomerBalance';


        return $extension;
    }

    public function customerAttributesValues($customer, $whichAttributes = array()) {

        Varien_Profiler::start('POS::' . __METHOD__);

        $result = array();

        if(!empty($whichAttributes)) {
            $attributes = explode(',', $whichAttributes);

            if(is_array($attributes) && !empty($attributes)) {

                foreach($attributes as $_attributeCode) {

                    if(!strlen($_attributeCode)) {
                        continue;
                    }

                    $_attributeValue = $customer->getData($_attributeCode);
                    if(!$_attributeValue) {

                        $method = 'get' . uc_words($_attributeCode, '');
                        if( is_callable(array($customer, $method)) )
                            $_attributeValue = $customer->$method();


                        if(!$_attributeValue)
                            $_attributeValue = '';

                    }

                    $_attr = $customer->getAttribute($_attributeCode);

                    $result []= array(
                        'name'  => $_attributeCode,
                        'label' => $_attr->getFrontendLabel(),
                        'type'  => $_attr->getFrontendInput(),
                        'value' => $_attributeValue,
                    );

                }

            }
        }

        Varien_Profiler::stop('POS::' . __METHOD__);

        return $result;
    }

    public function updateProductDateByIds(array $productIds) {
        $resource = Mage::getResourceSingleton('catalog/product');
        $write    = $resource->getWriteConnection();
        $now      = Varien_Date::now();
        foreach ($productIds as $id) {
            $data = array(
                'updated_at' => $now,
            );
            $where = $write->quoteInto('entity_id = ?', $id);

            $write->update($resource->getTable('catalog/product'), $data, $where);
        }
    }

    /**
     * Return options selected for sales persons in config.
     * @param int $storeId
     * @return array
     */
    public function getSalespersonsOptions($storeId) {
        $selected = Mage::getStoreConfig('bakerloorestful/checkout/sales_attribution', $storeId);
        $ret = array();

        if(!empty($selected)) {

            if($selected[0] == ',') {
                $selected = substr($selected, 1, strlen($selected));
            }

            $sqlIn = explode(',', $selected);

            $persons = Mage::getResourceModel('admin/user_collection')
                        ->addFieldToFilter('username', array('in' => $sqlIn))
                        ->setOrder('firstname', 'ASC');

            foreach($persons as $_person) {
                array_push($ret, array('value' => $_person->getUsername(), 'label' => $_person->getName()));
            }

        }

        return $ret;
    }

    public function logprofiler($store, $resource, $page) {
        $suiteLogPath = Mage::getBaseDir('var') . DS . 'log' . DS . 'pos';
        $profilerPath = $suiteLogPath . DS . 'profiler';

        if (!is_dir($suiteLogPath))
            mkdir($suiteLogPath, 0755);

        if (!is_dir($profilerPath))
            mkdir($profilerPath, 0755);

        $timers = Varien_Profiler::getTimers();

        $longest = 0;
        $rows = array();
        foreach ($timers as $name => $timer) {

            $sum = Varien_Profiler::fetch($name, 'sum');
            $count = Varien_Profiler::fetch($name, 'count');
            $realmem = Varien_Profiler::fetch($name, 'realmem');
            $emalloc = Varien_Profiler::fetch($name, 'emalloc');
            if ($sum < .0010 && $count < 10 && $emalloc < 10000) {
                continue;
            }

            $rows [] = array((string) $name, (string) number_format($sum, 4), (string) $count, (string) number_format($emalloc), (string) number_format($realmem));
            $thislong = strlen($name);
            if ($thislong > $longest) {
                $longest = $thislong;
            }
        }

        //Create table
        $table = new Zend_Text_Table(array('columnWidths' => array($longest, 10, 6, 12, 12), 'decorator' => 'ascii'));

        //Memory
        $preheader = new Zend_Text_Table_Row();
        $real = memory_get_usage(true);
        $emalloc = memory_get_usage();
        $preheader->appendColumn(new Zend_Text_Table_Column('real Memory usage: ' . $real . ' ' . ceil($real / 1048576) . 'MB', 'center', 1));
        $preheader->appendColumn(new Zend_Text_Table_Column('emalloc Memory usage: ' . $emalloc . ' ' . ceil($emalloc / 1048576) . 'MB', 'center', 4));
        $table->appendRow($preheader);

        //Append Header
        $header = new Zend_Text_Table_Row();
        $header->appendColumn(new Zend_Text_Table_Column('POS', 'center'));
        $header->appendColumn(new Zend_Text_Table_Column('Time', 'center'));
        $header->appendColumn(new Zend_Text_Table_Column('Cnt', 'center'));
        $header->appendColumn(new Zend_Text_Table_Column('Emalloc', 'center'));
        $header->appendColumn(new Zend_Text_Table_Column('RealMem', 'center'));
        $table->appendRow($header);

        foreach ($rows as $row) {
            $table->appendRow($row);
        }

        //SQL profile
        $dbprofile = print_r(Varien_Profiler::getSqlProfiler(Mage::getSingleton('core/resource')->getConnection('core_write')), TRUE);
        $dbprofile = substr($dbprofile, 0, -4);
        $dbprofile = str_replace('<br>', "\n", $dbprofile);

        $preheaderlabel = new Zend_Text_Table_Row();
        $preheaderlabel->appendColumn(new Zend_Text_Table_Column('DATABASE', 'center', 5));
        $table->appendRow($preheaderlabel);
        $preheader = new Zend_Text_Table_Row();
        $preheader->appendColumn(new Zend_Text_Table_Column($dbprofile, 'left', 5));
        $table->appendRow($preheader);

        /*//Request
        $rqlabel = new Zend_Text_Table_Row();
        $rqlabel->appendColumn(new Zend_Text_Table_Column('REQUEST', 'center', 5));
        $table->appendRow($rqlabel);
        $inforqp = new Zend_Text_Table_Row();
        $inforqp->appendColumn(new Zend_Text_Table_Column(print_r($action->getRequest(), TRUE), 'left', 5));
        $table->appendRow($inforqp);*/

        $date = Mage::getModel('core/date')->date('Y-m-d\.H-i-s');

        $file = new SplFileObject($profilerPath . DS . "{$date}_{$store}-{$resource}-{$page}.txt", 'w');
        $file->fwrite($table);
    }


    public function getShiftStates(){
        return array(
            'closed',
            'open'
        );
    }

    public function startprofiler() {
        Mage::getSingleton('core/resource')->getConnection('core_write')->getProfiler()->setEnabled(true);
        Varien_Profiler::enable();
    }

    public function endprofiler() {
        Mage::getSingleton('core/resource')->getConnection('core_write')->getProfiler()->setEnabled(false);
        Varien_Profiler::disable();
    }

}