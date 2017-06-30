<?php

class Ebizmarts_BakerlooRestful_Model_Api_Api {

    public $parameters           = array();
    public $controllerName       = "";
    public $pageSize             = 50;

    public $defaultSort          = "updated_at";
    public $defaultDir           = "ASC";
    protected $_querySep         = ",";
    protected $_perPageLimit     = 400;
    protected $_storeId          = null; //Not OK to default to 1.
    protected $_model            = "core/config";
    protected $_outputAttributes = array();
    private $_filesMode          = false;
    protected $_router           = "pos";
    protected $_version          = 1;
    protected $_since;
    protected $_filterByDelta    = true;
    private $_resultArray        = array();
    private $_resultArrayIdx     = array();
    protected $_orJoinType       = 'inner';

    protected $_iterator = true;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'pos_api';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'object';

    /**
     * Collection object
     *
     * @var Varien_Data_Collection
     */
    protected $_collection = null;

    public function __construct($params) {
        $this->parameters = $params;

        //Set pageSize via QueryString, default is 50, max is 400
        $limit = $this->_getQueryParameter('limit');
        if(!is_null($limit)) {

            if(((int)$limit) <= $this->_perPageLimit) {
                $this->setPageSize((int)$limit);
            }
            else {
                $this->setPageSize($this->_perPageLimit);
            }

        }

        $dir = $this->_getQueryParameter('dir');
        if($dir && (strtoupper($dir) == "DESC" || strtoupper($dir) == "ASC") ) {
            $this->defaultDir = strtoupper($dir);
        }

        $sort = $this->_getQueryParameter('order');
        if($sort) {
            $this->defaultSort = (string)$sort;
        }
        else if($this->_getQueryParameter('sort')) {
            $this->defaultSort = (string)$this->_getQueryParameter('sort');
        }

        $storeIdH = $this->_getRequestHeader(Mage::helper('bakerloo_restful')->getStoreIdHeader());
        if($storeIdH) {
            $this->setStoreId($storeIdH);

            //Apply StoreId
            $_store = Mage::app()->getStore($this->getStoreId());
            $_store->getId();
            Mage::app()->setCurrentStore($_store->getId());

        }
        else {
            $storeIdP = $this->_getQueryParameter('store_id');
            if($storeIdP) {
                $this->setStoreId($storeIdP);
            }
        }

        $this->_since = $this->_getQueryParameter('since');
        //Return static files data
        if(!is_null($this->_since) && 0 === intval($this->_since)) {

            $filesModeByPass = Mage::helper('bakerloo_restful')->config('general/filesmode_bypass', $this->getStoreId());
            if(0 === (int)$filesModeByPass) {
                $this->setFilesMode(true);
            }
            else {
                //Skip flat catalog when I want to synch.
                Mage::helper('bakerloo_restful/pages')->disableFlatCatalogAndCategory($this->getStoreId());
            }

        }
        else {
            //Skip flat catalog when I want to synch.
            Mage::helper('bakerloo_restful/pages')->disableFlatCatalogAndCategory($this->getStoreId());
        }

    }

    public function getModelName() {
        return __CLASS__;
    }

    /**
     * @param int $pageSize
     */
    public function setPageSize($pageSize) {
        $this->pageSize = $pageSize;
    }

    /**
     * @return int
     */
    public function getPageSize() {
        return $this->pageSize;
    }

    public function getSafePageSize() {
        if(-1 === $this->_since) {
            return $this->pageSize;
        }
        else {
            return (int)Mage::helper('bakerloo_restful')->config('catalog/deltas_pagesize', $this->getStoreId());
        }
    }

    /**
     * Process GET requests.
     *
     * @return array
     * @throws Exception
     */
    public function get() {

        $this->checkGetPermissions();

        $identifier = $this->_getIdentifier();

        if($identifier) { //get item by id

            if(is_numeric($identifier))
                return $this->_createDataObject((int)$identifier);
            else
                throw new Exception('Incorrect request.');
        }
        else {

            //get page
            $page = $this->_getQueryParameter('page');
            if(!$page) {
                $page = 1;
            }

            $filters     = $this->_getQueryParameter('filters');
            $resultArray = $this->_getAllItems($page, $filters);

            return $resultArray;

        }

    }

    public function put() {
        $this->checkPutPermissions();

        return $this;
    }

    public function post() {
        $this->checkPostPermissions();

        return $this;
    }

    public function delete() {
        $this->checkDeletePermissions();

        return $this;
    }

    protected function _getIdentifier($asString = false) {
        $params = $this->parameters;
        $identifier = null;

        if(count($params)) {

            $values     = array_values($params);

            if($asString === true) {
                $identifier = $values[0];
            }
            else {
                $identifier = (int)$values[0];
            }

        }

        return $identifier;
    }

    protected function _getQueryParameter($key) {
        $params = $this->parameters;

        if(array_key_exists($key, $params)){
            return $params[$key];
        }
        else {
            return null;
        }
    }

    /**
     * @param $name
     * @param $customFilters
     * @param bool $returnPosition
     * @return string|null
     */
    public function getFilterByName($name, $customFilters = null, $returnPosition = false) {
        $foundFilter = null;

        $filters = is_null($customFilters) ? $this->_getQueryParameter('filters') : $customFilters;

        $i=0;

        if(!empty($filters)) {
            foreach ($filters as $filter) {
                list($fname, ,) = $this->explodeFilter($filter);

                if($fname == $name) {

                    if($returnPosition === true)
                        $foundFilter = $i;
                    else
                        $foundFilter = $filter;

                    break;
                }

                $i++;
            }
        }

        return $foundFilter;
    }

    public function getFilesMode() {
        return $this->_filesMode;
    }

    public function setFilesMode($mode) {
        $this->_filesMode = $mode;
    }

    public function getStoreId() {
        return $this->_storeId;
    }

    public function setStoreId($id) {
        $this->_storeId = (int)$id;
        return $this;
    }

    public function getUsername() {
        return $this->_getRequestHeader(Mage::helper('bakerloo_restful')->getUsernameHeader());
    }

    public function getUsernameAuth() {
        return $this->_getRequestHeader(Mage::helper('bakerloo_restful')->getUsernameAuthHeader());
    }

    public function getDeviceId() {
        return $this->_getRequestHeader(Mage::helper('bakerloo_restful')->getDeviceIdHeader());
    }

    public function getUserAgent() {
        return $this->_getRequestHeader(Mage::helper('bakerloo_restful')->getUserAgentHeader());
    }

    public function getLatitude() {
        return $this->_getRequestHeader(Mage::helper('bakerloo_restful')->getLatitudeHeader());
    }

    public function getLongitude() {
        return $this->_getRequestHeader(Mage::helper('bakerloo_restful')->getLongitudeHeader());
    }

    protected function _getRequestHeader($header) {
        return $this->getRequest()->getHeader($header);
    }

    public function getRequest() {
        return Mage::app()->getRequest();
    }

    public function getStore() {
        $storeId = $this->getStoreId();
        return Mage::app()->getStore($storeId);
    }

    public function getCollectionSize(){
        if(isset($this->_collection))
            return $this->_collection->getSize();
        return 0;
    }

    protected function _getCollectionPageObject($pageData, $pageNum, $prevPage, $nextPage, $count) {

        //@TODO: Fix $count if $this->_totalPage is lower

        //next page link
        if($nextPage) {
            $nextUrl = $this->_reassembleRequestUrl();
            if(strpos($nextUrl,'?page=' . $pageNum)!==FALSE) {
                $nextUrl = str_replace('?page=' . $pageNum, '?page=' . $nextPage, $nextUrl);
            }elseif(strpos($nextUrl,'&page=' . $pageNum)!==FALSE) {
                $nextUrl = str_replace('&page=' . $pageNum, '?page=' . $nextPage, $nextUrl);
            }
            else {
                if(strpos($nextUrl,'?')===FALSE) {
                    $nextUrl .= "?page=" . $nextPage;
                }
                else {
                    $nextUrl .= "&page=" . $nextPage;
                }
            }
        }
        else {
            $nextUrl = null;
        }

        //prev page link
        if($prevPage) {
            $prevUrl = $this->_reassembleRequestUrl();
            if(strpos($prevUrl,'?page=' . $pageNum)!==FALSE){
                $prevUrl = str_replace('?page=' . $pageNum, '?page=' . $prevPage, $prevUrl);
            }elseif(strpos($prevUrl,'&page=' . $pageNum)!==FALSE){
                $prevUrl = str_replace('&page=' . $pageNum, '?page=' . $prevPage, $prevUrl);
            }
            else {
                if(strpos($prevUrl,'?')===FALSE) {
                    $prevUrl .= "?page=" . $prevPage;
                }
                else {
                    $prevUrl .= "&page=" . $prevPage;
                }
            }
        }
        else {
            $prevUrl = null;
        }

        $thisPageCount = count($pageData);
        $totalPages = !$thisPageCount ? 0 : ceil($count/$this->getPageSize());

        $result = array(
                        'page_count'  => $thisPageCount,
                        'next_page'   => $nextUrl,
                        'prev_page'   => $prevUrl,
                        'total_count' => $count,
                        'total_pages' => $totalPages,
                        'page_data'   => $pageData
                       );

        return $result;

    }

    protected function _getCollection() {
        return Mage::getModel($this->_model)->getCollection();
    }

    protected function _getAllItems($page = 1, $filters = array()) {

        $page = intval($page);

        //Return static files data
        if($this->getFilesMode()) {
            return $this->_paginateCollection(null, $page);
        }

        $this->_collection = $this->_getCollection();

        $this->_collection->setOrder($this->defaultSort, $this->defaultDir);

        $this->_collection->getSelect()->limitPage($page, $this->getPageSize());

        if(is_array($filters) && !empty($filters)) {
            $this->_applyFilters($filters);
        }

        if( $this->_since && ($this->_filterByDelta === true) && ($this->_since != -1) ) {

            //Skip flat catalog when I want to synch.
            Mage::helper('bakerloo_restful/pages')->disableFlatCatalogAndCategory($this->getStoreId());

            if(false === strpos($this->_since, ":")) {
                $this->_since = Mage::getModel('core/date')->date(null, $this->_since);
            }

            if($this->_collection instanceof Mage_Core_Model_Mysql4_Collection_Abstract
                or $this->_collection instanceof Mage_Core_Model_Resource_Db_Collection_Abstract) {
                $this->_collection->addFieldToFilter($this->defaultSort, array("gteq" => $this->_since));
            }
            else {
                $this->_collection->addAttributeToFilter($this->defaultSort, array("gteq" => $this->_since));
            }

        }
        else {
            if($this->_since == -1) {
                //Skip flat catalog when I am generating data to flat files.
                Mage::helper('bakerloo_restful/pages')->disableFlatCatalogAndCategory($this->getStoreId());
            }
        }

        $this->_beforePaginateCollection($this->_collection, $page, $this->_since);

        $collectionSize = $this->getCollectionSize(); //$this->_collection->getSize();

        return $this->_paginateCollection($collectionSize, $page);
    }

    /**
     *
     */
    public function _beforePaginateCollection($collection, $page, $since) {
        return $this;
    }

    /**
     * Applying array of filters to collection
     *
     * @param array $filters
     * @param bool $useOR
     */
    public function _applyFilters($filters, $useOR = false) {

        if($useOR) {
            //if collection table is flat
            if($this->_collection instanceof Mage_Core_Model_Mysql4_Abstract
                or $this->_collection instanceof Mage_Core_Model_Resource_Db_Collection_Abstract){
                //parse filters
                $attrNames = array();
                $conditions = array();
                $legacyFilters = array();

                foreach($filters as $filter) {
                    list($attributeCode, $condition, $value) = $this->explodeFilter($filter);

                    array_push($attrNames, $attributeCode);
                    array_push($conditions, array($condition => $value));

                    if(!isset($legacyFilters[$attributeCode]))
                        $legacyFilters[$attributeCode] = array();

                    array_push($legacyFilters[$attributeCode], array($condition => $value));
                }

                //filter collection
                if($this->shouldUseLegacyOrWhere()) {
                    $cnn = $this->_collection->getConnection();
                    foreach($legacyFilters as $orAttCode => $_orFilter) {
                        $this->_collection
                            ->getSelect()
                            ->orWhere($cnn->prepareSqlCondition($orAttCode, $_orFilter), null, Varien_Db_Select::TYPE_CONDITION);
                    }
                }
                else {
                    $this->_collection->addFieldToFilter($attrNames, $conditions);
                }
            }
            else{
                //parse filters
                $attributeFilters = array();

                foreach($filters as $filter){
                    list($attributeCode, $condition, $value) = $this->explodeFilter($filter);
                    array_push($attributeFilters, array(
                        'attribute' => $attributeCode,
                        $condition => $value
                    ));
                }

                //filter collection
                $this->_collection->addAttributeToFilter($attributeFilters, null, $this->_orJoinType);
            }
        }
        else {

            foreach($filters as $_filter) {
                list($attributeCode, $condition, $value) = $this->explodeFilter($_filter);

                if($this->_collection instanceof Mage_Core_Model_Mysql4_Collection_Abstract
                    or $this->_collection instanceof Mage_Core_Model_Resource_Db_Collection_Abstract) {
                    $this->_collection->addFieldToFilter($attributeCode, array($condition => $value));
                }
                else {
                    $this->_collection->addAttributeToFilter($attributeCode, array($condition => $value));
                }

            }

        }

    }

    /**
     * @param $_filter
     * @return array
     */
    public function explodeFilter($_filter) {
        $ret = explode($this->_querySep, $_filter, 3);

        $ret[1] = strtolower($ret[1]);

        if( isset($ret[1]) and ($ret[1] == 'in') ) {
            if(empty($ret[2]))
                Mage::throwException('Invalid value supplied for filter.');
            else
                $ret[2] = explode(',', $ret[2]);
        }

        return $ret;
    }

    protected function _paginateCollection($count, $page) {

        $resultArray = array();

        if($this->getFilesMode()) {

            $io = $this->_getIo($this->getResourceNameFromUrl());

            //Fetch export config data
            $_configData = $io->read("_pagedata.ser");

            $_config = unserialize($_configData);

            $totalPages = $_config['totalpages'];

            $_staticData = $io->read("page" . str_pad($page, 5, '0', STR_PAD_LEFT) . ".ser");

            if(false === $_staticData) {
                Mage::throwException(Mage::helper('bakerloo_restful')->__('Page #%s not found.', $page));
            }

            $resultArray = unserialize($_staticData);

            $prevPage = null;
            if($page > 1) {
                $prevPage = $page - 1;
            }

            $nextPage = $page + 1;
            if($nextPage > $totalPages) {
                $nextPage = null;
            }

            //Set the items count
            $count = $_config['totalrecords'];

            $this->setPageSize($_config['perpage']);

        }
        else {

            $pageOutOfIndex = false;
            $pageAux        = ceil($count/$this->getPageSize());

            //Without this call Order is not rendered.
            if(!$this->_iterator)
                $this->_collection->load();

            if($pageAux >= $page) {

                if($this->_iterator) {

                    $orderPart = $this->_collection->getSelect()->getPart('order');
                    if(empty($orderPart))
                        $this->_collection->getSelect()->order("{$this->defaultSort} {$this->defaultDir}");

                    Mage::getSingleton('core/resource_iterator')
                        ->walk($this->_collection->getSelect(), array(array($this, 'callBack')));

                    $resultArray = $this->_resultArray;

                }
                else {
                    foreach ($this->_collection as $_item) {
                        $dto = $this->_createDataObject($_item->getId(), $_item);

                        if(!empty($dto)) {
                            $resultArray[] = $dto;
                        }
                    }
                }

            }
            else {
                $pageOutOfIndex = true;
            }

            //Mage::log( (string)$this->_collection->getSelect(), null, 'pos-sql.log', true );

            //prev page
            $prevPage = null;
            if($page > 1) {
                if($pageOutOfIndex) {
                    $prevPage = floor($count/$this->getPageSize());
                }
                else {
                    $prevPage = $page-1;
                }
            }

            //next page
            $nextPage=$page+1;
            if($pageAux < $nextPage) {
                $nextPage = null;
            }

        }

        return $this->_getCollectionPageObject($resultArray, $page, $prevPage, $nextPage, $count);
    }

    public function callBack($args) {
        $indexId = $this->_getIndexId();
        $id = $args['row'][$indexId];
        $dto = $this->_createDataObject($id, null);

        if(!empty($dto) and !in_array($id, $this->_resultArrayIdx)) {
            $this->_resultArray [] = $dto;
            array_push($this->_resultArrayIdx, $id);
        }

    }

    protected function _getIndexId() {
        return 'entity_id';
    }

    protected function _reassembleRequestUrl() {

        //@ToDo: Change this to framework based query string

        $baseUrl = Mage::getUrl('*/*', array('_secure' => true, '_nosid' => true)) . "index/";

        $parametersCount = count($this->parameters);
        if($parametersCount) {
            $keys = array_keys($this->parameters);

            $keysCount = count($keys);

            //sort so page is first, if page is not first self::125 breaks :)
            if(isset($keys[1]) && $keys[1] != "page") {
                for ($i = 1; $i < $keysCount; $i++) {
                    if("page" == $keys[$i]) {
                        $pageN = (int)$this->parameters[$keys[$i]];
                        if($pageN > 0) {
                            $temp = $keys[1];
                            $keys[1] = $keys[$i];
                            $keys[$i] = $temp;
                            break;
                        }
                        else {
                            unset($keys[$i]);
                        }
                    }
                }
            }

            $keysCount = count($keys);

            $name = $keys[0];
            $baseUrl .= $name . "/" . $this->parameters[$keys[0]];
            if($keysCount > 1) {
                for($i=1; $i < $keysCount; $i++) {
                    if($i==1) {
                        $baseUrl .= "?";
                    }
                    else {
                        $baseUrl .= "&";
                    }

                    $_paramValue = $this->parameters[$keys[$i]];
                    if(!is_array($_paramValue)) {
                        $baseUrl .= $keys[$i] . "=" . $_paramValue;
                    }
                    else {

                        foreach($_paramValue as $_param) {
                            $baseUrl .= $keys[$i] . "[]=" . $_param;

                            if(end($_paramValue) != $_param) {
                                $baseUrl .= '&';
                            }
                        }

                    }

                }
            }
        }

        return $baseUrl;
    }

    public function _createDataObject($id = null, $data = null) {
        $result = null;

        if(is_null($data)) {
            $_item = Mage::getModel($this->_model)->load($id);
        }
        else {
            $_item = $data;
        }

        if($_item->getId()) {


            if(empty($this->_outputAttributes)) {
                $result = $_item->toArray();
            }
            else{
                $toAdd = array();

                /*foreach($this->_outputAttributes as $attributeCode => $attributeOutput) {
                    $toAdd[$attributeOutput] = $_item->getData($attributeCode);
                }*/
                foreach($this->_outputAttributes as $attributeCode) {
                    $toAdd[$attributeCode] = $_item->getData($attributeCode);
                }

                $result = $toAdd;
            }

        }

        return $this->returnDataObject($result);
    }

    public function returnDataObject($data) {
        $result = new Varien_Object($data);

        Mage::dispatchEvent($this->_eventPrefix . '_return_before', array($this->_eventObject => $result));

        return $result->getData();
    }

    protected function _getIo($resource) {
        $path = Mage::helper('bakerloo_restful/cli')->getPathToDb($this->getStoreId(), $resource, false);

        try {
            $io = new Varien_Io_File();
            $io->open(array('path' => $path));
        }catch (Exception $ex) {
            Mage::logException($ex);
            return false;
        }

        return $io;
    }

    public function getResourceNameFromUrl() {
        $keys = array_keys($this->parameters);

        return $keys[0];
    }

    /**
     * Return helper instance.
     *
     * @return Ebizmarts_BakerlooRestful_Helper_Data
     */
    public function helper() {
        return Mage::helper('bakerloo_restful');
    }

    public function getApiPath(){
        return $this->_router . "/index/index/";
    }

    public function getImagesPath(){
        return $this->_router . "/catalog/image/";
    }

    public function returnFirstValueForFilter($filters, $filterName) {
        $newFilterValue = null;

        $filter = $this->getFilterByName($filterName, $filters);

        $values = explode($this->_querySep, $filter, 3);

        if(array_key_exists(2, $values)) {
            $valuesAux = explode($this->_querySep, $values[2]);

            $countAux = count($valuesAux);

            if($countAux === 0)
                return null;
            else
                return $valuesAux[0];

        }

        return null;
    }

    /**
     * Validate user permissions for certain action.
     *
     * @param string $username
     * @param string $resource
     * @return boolean
     */
    public function isAllowed($resource, $username = null) {

        if(is_null($username)) {

            if($this->getUsernameAuth()) {
                $username = $this->getUsernameAuth();
            }
            else {
                $username = $this->getUsername();
            }
        }

        return (bool)Mage::helper('bakerloo_restful/acl')->isAllowed($username, $resource);
    }

    public function getJsonPayload() {
        return Mage::helper('bakerloo_restful/http')->getJsonPayload($this->getRequest());
    }

    public function checkPermission(array $perms) {

        $allow = true;

        foreach($perms as $_perm) {
            $isUserAllowed = $this->isAllowed($_perm);

            if(!$isUserAllowed) {
                $allow = false;
                break;
            }
        }

        if(!$allow)
            Mage::throwException("Not enough privileges or user is not active.");

        return $allow;
    }

    public function checkGetPermissions() {
        return $this;
    }

    public function checkPostPermissions() {
        return $this;
    }

    public function checkDeletePermissions() {
        return $this;
    }

    public function checkPutPermissions() {
        return $this;
    }

    public function shouldUseLegacyOrWhere() {
        $object = new Varien_Data_Collection_Db;

        return ( method_exists($object, '_translateCondition') === false );
    }

}
