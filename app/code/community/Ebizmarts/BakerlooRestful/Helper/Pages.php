<?php
/**
 * Static pages HELPER.
 */

class Ebizmarts_BakerlooRestful_Helper_Pages {

    const MATCH_PAGE_NAME = "/(page)(\\d{5})(\\.)(ser)/is";

    const INSERT_PRODUCTS             = "INSERT INTO Product(product_id, sku, name, price, special_price, last_update, type, visibility, json, barcode, status) VALUES (:product_id, :sku, :name, :price, :special_price, :last_update, :type, :visibility, :json, :barcode, :status)";
    const INSERT_PRODUCT_CATEGORY     = "INSERT INTO ProductCategory(product_id, category_id, position) VALUES (:product_id, :category_id, :position)";
    const INSERT_PRODUCT_CONFIG_CHILD = "INSERT INTO ProductConfigurableChild(product_id, child_id) VALUES (:product_id, :child_id)";
    const INSERT_PRODUCT_BARCODES     = "INSERT INTO ProductBarcodes(barcode, product_id) VALUES (:barcode, :product_id)";
    const INSERT_PRODUCT_INVENTORY    = "INSERT INTO ProductInventory(product_id, json, updated_at, manage_stock, qty, is_in_stock, backorders) VALUES (:product_id, :json, :updated_at, :manage_stock, :qty, :is_in_stock, :backorders)";
    const INSERT_CUSTOMERS            = "INSERT INTO Customer(customer_id, firstname, lastname, email, group_id, json, updated_at) VALUES (:customer_id, :firstname, :lastname, :email, :group_id, :json, :updated_at)";
    const INSERT_CUSTOMERS_ATTRIBS    = "INSERT INTO CustomerAttributes(customer_id, name, label, type, value) VALUES
     (:customer_id, :name, :label, :type, :value)";
    const INSERT_CUSTOMERS_ADDRESSES = "INSERT INTO CustomerAddress(customer_id, customer_address_id, firstname, lastname, company, telephone, fax, street,
street1, city, postcode, country_id, region_id, region, is_shipping_address, is_billing_address, updated_at) VALUES
(:customer_id, :customer_address_id, :firstname, :lastname, :company, :telephone, :fax, :street,
:street1, :city, :postcode, :country_id, :region_id, :region, :is_shipping_address, :is_billing_address, :updated_at)";

    private $_io = null;

    public function getExportConfig($data) {

        if(!isset($data['total_count'])) {
            $data['total_count'] = 1;
            $data['total_pages'] = 1;
        }

        $_config = array(
            'perpage'      => $data['page_count'],
            'totalrecords' => $data['total_count'],
            'totalpages'   => $data['total_pages'],
        );

        return serialize($_config);
    }

    public function getData($resource, $since, $limit, $page = 1, $storeId = 0) {
        $params = array(
            'resource' => null,
            'since'    => $since,
            'limit'    => $limit,
            'page'     => $page,
            'store_id' => $storeId
        );

        $obj = Mage::getModel('bakerloo_restful/api_' . $resource, $params);

        return $obj->get();
    }

    public function storeData($resource, array $data, $storeId, $pageNumber) {

        //Path to file: For example, /path/to/magento/var/pos/1/products
        $reset = false;

        if(1 === $pageNumber) {
            $reset = true;
        }

        $ioAdapter = $this->getIo($storeId, $resource, $reset);

        $_data = !isset($data['page_data']) ? $data : $data['page_data'];

        //Save export global information
        if(1 === $pageNumber) {
            $ioAdapter->write("_pagedata.ser", $this->getExportConfig($data));
        }

        $ioAdapter->write("page" . str_pad($pageNumber, 5, '0', STR_PAD_LEFT) . ".ser", serialize($_data));

        unset($data);
    }

    public function getIo($storeId, $resource, $reset) {

        if(is_null($this->_io)) {
            $path = Mage::helper('bakerloo_restful/cli')->getPathToDb($storeId, $resource, $reset);
            $ioAdapter = new Varien_Io_File();
            $ioAdapter->open(array('path' => $path));
            $this->_io = $ioAdapter;
        }

        return $this->_io;
    }

    public function getZippedPages(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, $resource = null, $generate = true) {

        $reset = false;
        $storeId = (int)$request->getHeader(Mage::helper('bakerloo_restful')->getStoreIdHeader());

        if(is_null($resource)) {
            $params = $request->getParams();
            if(is_array($params) and !empty($params)) {
                if(count($params)) {
                    $keys = array_keys($params);
                    $resource = $keys[0];
                }
            }
        }

        $path = Mage::helper('bakerloo_restful/cli')->getPathToDb($storeId, $resource, $reset);

        try {

            $zipiName = 'pages.zip';
            $zipName  = $path . DS . $zipiName;

            $zipObj = new SplFileInfo($zipName);
            if($zipObj->isFile()) {
                $this->returnBinary($zipName, $response, $zipiName, 'application/zip');
                return;
            }
            else {
                if(!$generate)
                    Mage::throwException('Cache does not exist, pre-generate via admin please.');
            }

            $pages = array();

            $io       = $this->getIo($storeId, $resource, $reset);
            $pageData = unserialize($io->read('_pagedata.ser'));

            if(false === $pageData)
                Mage::throwException('No pages found to ZIP. Resource: ' . $resource);

            $iterator = new DirectoryIterator($path);
            foreach ($iterator as $fileInfo) {

                $fileName = $fileInfo->getFilename();

                if(preg_match(self::MATCH_PAGE_NAME, $fileName) !== 1) {
                    continue;
                }

                array_push($pages, $fileName);
            }

            usort($pages, array($this, "sort_tree"));

            $zip = new ZipArchive;
            $zipRS = $zip->open($zipName, ZipArchive::CREATE);

            if($zipRS !== true)
                Mage::throwException("Could not open zip file for write.");

            foreach($pages as $_page) {
                $fileData = unserialize($io->read($_page));

                $toStore = $pageData;
                $toStore['page_data'] = $fileData;

                $okZip = $zip->addFromString(str_replace('.ser', '.json', $_page), json_encode($toStore));
                if(false === $okZip) {
                    Mage::throwException("Could not compress: " . $fileName);
                }
            }

            $zip->close();

            $this->returnBinary($zipName, $response, $zipiName, 'application/zip');

        }catch(Exception $ex) {
            //Directory is empty or something.
            $response->setHttpResponseCode(500)
                ->setBody($ex->getMessage());
        }
    }

    public function returnBinary($filename, Zend_Controller_Response_Abstract $response, $name, $type) {

        $response
        ->setHttpResponseCode(200)
        ->setHeader('Content-Type', $type, true)
        ->setHeader('Content-Disposition', 'attachment; filename=' . $name, true)
        ->setHeader('Content-Length', filesize($filename))
        ->setHeader('Pragma', 'no-cache', true)
        ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->clearBody();
        $response->sendHeaders();

        readfile($filename);

    }

    public function returnList($list, Zend_Controller_Response_Abstract $response){
        $response
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'no-cache', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->clearBody();
        $response->setBody($list);
        $response->sendHeaders();
    }

    public function getDB(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, $compressed = true, $resource = null, $generate = true) {
        $reset   = false;
        $storeId = (int)$request->getHeader(Mage::helper('bakerloo_restful')->getStoreIdHeader());

        if(is_null($resource))
            $resource = $this->getResourceFromParams($request->getParams());

        $cacheable = $this->dbCacheResources();

        if(!array_key_exists($resource, $cacheable))
            Mage::throwException("Invalid resource provided.");

        try {
            $dbTableName = $cacheable[$resource]['tableName'];

            $path = Mage::helper('bakerloo_restful/cli')->getPathToDb($storeId, $resource, $reset);

            $sqliteName = "{$dbTableName}.sqlite";
            $zipiName   = "{$dbTableName}.zip";

            $basePath = $path . DS;
            $dbName   = $basePath . $sqliteName;
            $zipName  = $basePath . $zipiName;

            $dbNameObj = new SplFileInfo($dbName);
            if($dbNameObj->isFile() and !$compressed) {
                $this->returnBinary($dbName, $response, $sqliteName, 'application/x-sqlite3');
                return;
            }
            else {
                $zipNameObj = new SplFileInfo($zipName);
                if($zipNameObj->isFile()) {
                    $this->returnBinary($zipName, $response, $zipiName, 'application/zip');
                    return;
                }
            }

            if( !$dbNameObj->isFile() ) {

                if( !$generate )
                    Mage::throwException('Cache does not exist, pre-generate via admin please.');

                $db = new SQLite3($dbName);

                $createTableSQL = $cacheable[$resource]['create'];

                $this->createCacheTables($createTableSQL, $db);

                $recordsCount = 0;

                //Read static pages.
                $io = $this->getIo($storeId, $resource, $reset);
                $iterator = new DirectoryIterator($path);
                foreach ($iterator as $fileInfo) {

                    $fileName = $fileInfo->getFilename();

                    if (preg_match(self::MATCH_PAGE_NAME, $fileName) !== 1)
                        continue;

                    $fileData = unserialize($io->read($fileName));

                    if ($fileData === false) {
                        $pageError = "Could not decode page: " . $fileName;

                        Mage::log($pageError, null, 'POS-SQLite.log', true);

                        $db->close();

                        $this->_rmFileIfExists($dbName);

                        Mage::throwException($pageError);
                    }

                    if ('products' == $resource)
                        $recordsCount += $this->populateProducts($fileData, $db, $storeId);

                    if ('inventory' == $resource)
                        $recordsCount += $this->populateProductsInventory($fileData, $db);

                    if ('customers' == $resource)
                        $recordsCount += $this->populateCustomers($fileData, $db);

                    unset($fileData);

                }

                if ($recordsCount == 0) {
                    $this->_rmFileIfExists($dbName);

                    Mage::throwException('No pages found to create DB. Resource: ' . $resource);
                }

                $count   = $this->countRows($dbName, $dbTableName);
                if (((int)$count) !== $recordsCount) {
                    $this->_rmFileIfExists($dbName);

                    Mage::log("Record count dont match #{$count} vs #{$recordsCount} \n Resource: {$resource}.", null, 'POS-SQLite.log', true);

                    Mage::throwException("Corrupted database, please try again.");
                }

            }

            if($compressed) {
                $zip    = new ZipArchive;
                $okOpen = $zip->open($zipName, ZipArchive::CREATE);
                if($okOpen !== true)
                    Mage::throwException("Could not open: " . $zipName);

                $okZip = $zip->addFile($dbName, $sqliteName);
                if(false === $okZip)
                    Mage::throwException("Could not compress: " . $zipName);

                $zip->close();

                $this->returnBinary($zipName, $response, $zipiName, 'application/zip');
            }
            else
                $this->returnBinary($dbName, $response, $sqliteName, 'application/x-sqlite3');

        } catch(Exception $ex) {
            //Directory is empty or something.
            $response->setHttpResponseCode(500)->setBody($ex->getMessage());
        }

    }

    public function insertPrepared($helper, $stmt, $db, &$count = null) {
        $result = $helper->execPrepared($stmt, $db);

        if(!$result) {

            $lastErrorCode = $db->lastErrorCode();
            $lastErrorMsg  = $db->lastErrorMsg();

            Mage::log("EXEC FAILED: {$stmt}", null, 'POS-SQLite.log', true);
            Mage::log("EXEC FAILED, error: #{$lastErrorCode}-{$lastErrorMsg}", null, 'POS-SQLite.log', true);
        }
        else
            if(!is_null($count)) $count++;

        return $result;
    }

    public function insert($stmt, $db, &$count = null) {
        $result = @$db->exec($stmt);

        if(!$result) {

            $lastErrorCode = $db->lastErrorCode();
            $lastErrorMsg  = $db->lastErrorMsg();

            Mage::log("EXEC FAILED: {$stmt}", null, 'POS-SQLite.log', true);
            Mage::log("EXEC FAILED, error: #{$lastErrorCode}-{$lastErrorMsg}", null, 'POS-SQLite.log', true);
        }
        else
            if(!is_null($count)) $count++;

        return $result;
    }

    public function populateCustomers($data, $db) {
        $inserted = 0;

        if (is_array($data) and !empty($data)) {

            $sqliteHelper = new Ebizmarts_BakerlooRestful_Helper_Sqlite;
            $stmtCustomer = $sqliteHelper->prepareStmt(self::INSERT_CUSTOMERS, $db);
            $stmtCustomerAttribs = $sqliteHelper->prepareStmt(self::INSERT_CUSTOMERS_ATTRIBS, $db);
            $stmtCustomerAddress = $sqliteHelper->prepareStmt(self::INSERT_CUSTOMERS_ADDRESSES, $db);

            foreach ($data as $_item) {

                $sqliteHelper->addParameter('customer_id', $_item['customer_id'], SQLITE3_INTEGER);
                $sqliteHelper->addParameter('firstname', $_item['firstname'], SQLITE3_TEXT);
                $sqliteHelper->addParameter('lastname', $_item['lastname'], SQLITE3_TEXT);
                $sqliteHelper->addParameter('email', $_item['email'], SQLITE3_TEXT);
                $sqliteHelper->addParameter('group_id', $_item['group_id'], SQLITE3_INTEGER);
                $sqliteHelper->addParameter('json', json_encode($_item), SQLITE3_TEXT);
                $sqliteHelper->addParameter('updated_at', $_item['updated_at']);

                $this->insertPrepared($sqliteHelper, $stmtCustomer, $db, $inserted);
                $stmtCustomer->clear();

                if(is_array($_item['additional_attributes']) and !empty($_item['additional_attributes'])) {
                    foreach($_item['additional_attributes'] as $_bc) {

                        $sqliteHelper->addParameter('customer_id', $_item['customer_id'], SQLITE3_INTEGER);
                        $sqliteHelper->addParameter('name', $_bc['name'], SQLITE3_TEXT);
                        $sqliteHelper->addParameter('label', $_bc['label'], SQLITE3_TEXT);
                        $sqliteHelper->addParameter('type', $_bc['type'], SQLITE3_TEXT);
                        $sqliteHelper->addParameter('value', $_bc['value'], SQLITE3_TEXT);

                        $this->insertPrepared($sqliteHelper, $stmtCustomerAttribs, $db);
                        $stmtCustomerAttribs->clear();

                    }
                }

                $addresses = $_item['address'];
                foreach($addresses as $address) {

                    $sqliteHelper->addParameter('customer_id', $_item['customer_id'], SQLITE3_INTEGER);
                    $sqliteHelper->addParameter('customer_address_id', $address['customer_address_id'], SQLITE3_INTEGER);
                    $sqliteHelper->addParameter('firstname', $address['firstname'], SQLITE3_TEXT);
                    $sqliteHelper->addParameter('lastname', $address['lastname'], SQLITE3_TEXT);
                    $sqliteHelper->addParameter('company', $address['company'], SQLITE3_TEXT);
                    $sqliteHelper->addParameter('telephone', $address['telephone'], SQLITE3_TEXT);
                    $sqliteHelper->addParameter('fax', $address['fax'], SQLITE3_TEXT);
                    $sqliteHelper->addParameter('street', $address['street'], SQLITE3_TEXT);
                    $sqliteHelper->addParameter('street1', $address['street2'], SQLITE3_TEXT);
                    $sqliteHelper->addParameter('city', $address['city'], SQLITE3_TEXT);
                    $sqliteHelper->addParameter('postcode', strtoupper($address['postcode']), SQLITE3_TEXT);
                    $sqliteHelper->addParameter('country_id', $address['country_id'], SQLITE3_TEXT);
                    $sqliteHelper->addParameter('region_id', $address['region_id'], SQLITE3_TEXT);
                    $sqliteHelper->addParameter('region', $address['region'], SQLITE3_TEXT);
                    $sqliteHelper->addParameter('is_shipping_address', $address['is_shipping_address'], SQLITE3_INTEGER);
                    $sqliteHelper->addParameter('is_billing_address', $address['is_billing_address'], SQLITE3_INTEGER);
                    $sqliteHelper->addParameter('updated_at', $_item['updated_at']);

                    $this->insertPrepared($sqliteHelper, $stmtCustomerAddress, $db);
                    $stmtCustomerAddress->clear();

                }
            }
        }

        return $inserted;
    }

    public function populateProductsInventory($data, $db) {
        $inserted = 0;

        if (is_array($data) and !empty($data)) {

            $sqliteHelper = new Ebizmarts_BakerlooRestful_Helper_Sqlite;
            $stmt = $sqliteHelper->prepareStmt(self::INSERT_PRODUCT_INVENTORY, $db);

            foreach ($data as $_item) {

                $sqliteHelper->addParameter('product_id', $_item['product_id'], SQLITE3_INTEGER);
                $sqliteHelper->addParameter('json', json_encode($_item), SQLITE3_TEXT);
                $sqliteHelper->addParameter('updated_at', $_item['updated_at']);
                $sqliteHelper->addParameter('manage_stock', $_item['manage_stock'], SQLITE3_INTEGER);
                $sqliteHelper->addParameter('qty', $_item['qty']);
                $sqliteHelper->addParameter('is_in_stock', $_item['is_in_stock'], SQLITE3_INTEGER);
                $sqliteHelper->addParameter('backorders', $_item['backorders'], SQLITE3_INTEGER);

                $this->insertPrepared($sqliteHelper, $stmt, $db, $inserted);
                $stmt->clear();

            }

        }

        return $inserted;
    }

    /**
     * @param $data
     * @param $db
     * @param $storeId
     * @return int Total inserted.
     */
    public function populateProducts($data, $db, $storeId = null) {
        $inserted = 0;

        if (is_array($data) and !empty($data)) {

            $sqliteHelper = new Ebizmarts_BakerlooRestful_Helper_Sqlite;

            $stmt = $sqliteHelper->prepareStmt(self::INSERT_PRODUCTS, $db);
            $stmtProductCategory = $sqliteHelper->prepareStmt(self::INSERT_PRODUCT_CATEGORY, $db);
            $stmtProductChild = $sqliteHelper->prepareStmt(self::INSERT_PRODUCT_CONFIG_CHILD, $db);
            $stmtProductBarcode = $sqliteHelper->prepareStmt(self::INSERT_PRODUCT_BARCODES, $db);

            foreach ($data as $_item) {

                if(!is_array($_item))
                    continue;

                $sqliteHelper->addParameter('product_id', $_item['product_id'], SQLITE3_INTEGER);
                $sqliteHelper->addParameter('sku', strtoupper($_item['sku']), SQLITE3_TEXT);
                $sqliteHelper->addParameter('name', $_item["name"], SQLITE3_TEXT);
                $sqliteHelper->addParameter('price', $_item['price'], SQLITE3_FLOAT);
                $sqliteHelper->addParameter('special_price', $_item['special_price'], SQLITE3_FLOAT);
                $sqliteHelper->addParameter('last_update', $_item['last_update']);
                $sqliteHelper->addParameter('type', $_item['type'], SQLITE3_TEXT);
                $sqliteHelper->addParameter('visibility', $_item['visibility'], SQLITE3_INTEGER);
                $sqliteHelper->addParameter('json', json_encode($_item), SQLITE3_TEXT);
                $sqliteHelper->addParameter('barcode', $_item['barcode'], SQLITE3_TEXT);
                $sqliteHelper->addParameter('status', $_item['status'], SQLITE3_INTEGER);

                $result = $this->insertPrepared($sqliteHelper, $stmt, $db, $inserted);
                $stmt->clear();

                if(!$result)
                    continue;

                if (isset($_item['categories']) and is_array($_item['categories']) and !empty($_item['categories'])) {

                    foreach ($_item['categories'] as $_cat) {
                        $sqliteHelper->addParameter('product_id', $_item['product_id'], SQLITE3_INTEGER);
                        $sqliteHelper->addParameter('category_id', $_cat['category_id'], SQLITE3_INTEGER);
                        $sqliteHelper->addParameter('position', $_cat['position'], SQLITE3_INTEGER);
                        $this->insertPrepared($sqliteHelper, $stmtProductCategory, $db);
                        $stmtProductCategory->clear();
                    }

                }

                if ($_item['type'] == 'configurable') {
                    if ( !empty($_item['attributes']) ) {

                        $addedChilds = array();
                        foreach ($_item['attributes'] as $attr) {
                            if ( !empty($attr['config']) ) {
                                foreach ($attr['config'] as $conf) {
                                    if (!empty($conf)) {
                                        foreach ($conf['products'] as $pchild) {
                                                if (!in_array($pchild, $addedChilds)) {

                                                    $sqliteHelper->addParameter('product_id', $_item['product_id'], SQLITE3_INTEGER);
                                                    $sqliteHelper->addParameter('child_id', $pchild, SQLITE3_INTEGER);
                                                    $this->insertPrepared($sqliteHelper, $stmtProductChild, $db);
                                                    $stmtProductChild->clear();

                                                    $addedChilds[] = $pchild;
                                                }
                                        }

                                    }
                                }
                            }
                        }
                    }
                }

                //populate barcodes
                $barcode = Mage::helper('bakerloo_restful')->getProductBarcode($_item['product_id'], $storeId);
                $_barcode = explode(',', $barcode);
                if (is_array($_barcode) and !empty($_barcode)) {

                    foreach ($_barcode as $_bc) {
                        if(empty($_bc))
                            continue;

                        $sqliteHelper->addParameter('barcode', $_bc, SQLITE3_TEXT);
                        $sqliteHelper->addParameter('product_id', $_item['product_id'], SQLITE3_INTEGER);

                        $this->insertPrepared($sqliteHelper, $stmtProductBarcode, $db);
                        $stmtProductBarcode->clear();

                    }
                }

            }
        }

        return $inserted;
    }

    public function createCacheTables($createTableSQL, $db) {
        if (is_array($createTableSQL)) {
            for ($i = 0; $i < count($createTableSQL); $i++)
                $this->insert($createTableSQL[$i], $db);
        }
        else
            $this->insert($createTableSQL, $db);

    }

    public function getResourceFromParams($params) {
        $resource = null;

        if(is_array($params) and !empty($params)) {
            if (count($params)) {
                $keys = array_keys($params);
                $resource = $keys[0];
            }
        }

        return $resource;
    }

    public function dbCacheResources() {
        return array(
            'products' => array(
                'tableName' => 'Product',
                'create' => array('CREATE TABLE [Product] (
                           [product_id] INTEGER NOT NULL PRIMARY KEY,
                           [sku] TEXT NULL,
                           [name] TEXT  NULL,
                           [price] REAL  NULL,
                           [special_price] REAL  NULL,
                           [last_update] TIMESTAMP  NULL,
                           [type] TEXT  NULL,
                           [visibility] INTEGER NULL,
                           [barcode] TEXT NULL,
                           [json] TEXT  NULL,
                           [status] INTEGER NULL DEFAULT 1);',
                    'CREATE TABLE [ProductCategory] (
                      [product_id] INTEGER NULL,
                      [category_id] INTEGER NULL,
                      [position] INTEGER default 1,
                      PRIMARY KEY ([product_id],[category_id])
                      );',
                    'CREATE TABLE [ProductBarcodes] (
                [barcode] TEXT,
                [product_id] not null,
                primary key(barcode,product_id)
                );',
                'CREATE TABLE [ProductConfigurableChild] (
                    [product_id] integer,
                    [child_id] integer not null,
                    primary key(product_id,child_id)
                );'
                ),
            ),
            'inventory' => array(
                'tableName' => 'ProductInventory',
                'create' => 'CREATE TABLE [ProductInventory] (
                           [product_id] INTEGER NOT NULL PRIMARY KEY,
                           [json] TEXT NULL,
                           [updated_at] TIMESTAMP NULL,
                           [manage_stock] INTEGER,
                           [qty] REAL,
                           [is_in_stock] INTEGER,
                           [backorders] INTEGER);',
            ),
            'customers' => array(
                'tableName' => 'Customer',
                'create' => array('CREATE TABLE [Customer] (
                           [customer_id] INTEGER NOT NULL PRIMARY KEY,
                           [firstname] TEXT NULL,
                           [lastname] TEXT NULL,
                           [email] TEXT NULL,
                           [group_id] INTEGER NULL,
                           [json] TEXT NULL,
                           [updated_at] TIMESTAMP NULL);',
                    'CREATE TABLE [CustomerAttributes] (
                    [customer_id] INTEGER,
                    [name] TEXT,
                    [label] TEXT,
                    [type] TEXT,
                    [value] TEXT
              );',
                    'CREATE TABLE [CustomerAddress] (
                    [customer_id] INTEGER  NOT NULL,
                    [customer_address_id] INTEGER NOT NULL PRIMARY KEY,
                    [firstname] TEXT  NULL,
                    [lastname] TEXT  NULL,
                    [company] TEXT NULL,
                    [telephone] TEXT NULL,
                    [fax] TEXT NULL,
                    [street] TEXT NULL,
                    [street1] TEXT NULL,
                    [city] TEXT NULL,
                    [postcode] TEXT NULL,
                    [country_id] TEXT NULL,
                    [region_id] TEXT NULL,
                    [region] TEXT NULL,
                    [is_shipping_address] INTEGER NULL,
                    [is_billing_address] INTEGER NULL,
                    [updated_at] TIMESTAMP NULL
                    );'
                )
            )
        );
    }

    public function sort_tree($a, $b) {
        return strcasecmp($a, $b);
    }

    public function getOrdersBackup(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, $resource = null) {

        $reset = false;
        $storeId = (int)$request->getHeader(Mage::helper('bakerloo_restful')->getStoreIdHeader());

        if(is_null($resource)) {
            $params = $request->getParams();
            if(is_array($params) and !empty($params)) {
                if(count($params)) {
                    $keys = array_keys($params);
                    $resource = $keys[0];
                }
            }
        }

        $path = Mage::helper('bakerloo_restful/cli')->getPathToDb($storeId, $resource, $reset);

        try {

            $zipiName = 'pages.zip';
            $zipName = $path . DS . $zipiName;

            $zipNameObj = new SplFileInfo($zipName);
            if($zipNameObj->isFile()) {
                $this->returnBinary($zipName, $response, $zipiName, 'application/zip');
                return;
            }

            $pages = array();

            $io = $this->getIo($storeId, $resource, $reset);
            $pageData = unserialize($io->read('_pagedata.ser'));

            if(false === $pageData) {
                Mage::throwException('No pages found to ZIP. Resource: ' . $resource);
            }

            $iterator = new DirectoryIterator($path);
            foreach ($iterator as $fileInfo) {

                $fileName = $fileInfo->getFilename();

                if(preg_match(self::MATCH_PAGE_NAME, $fileName) !== 1) {
                    continue;
                }

                array_push($pages, $fileName);
            }

            usort($pages, array($this, "sort_tree"));

            $zip = new ZipArchive;
            $zip->open($zipName, ZipArchive::OVERWRITE);

            foreach($pages as $_page) {
                $fileData = unserialize($io->read($_page));

                $toStore = $pageData;
                $toStore['page_data'] = $fileData;

                $okZip = $zip->addFromString(str_replace('.ser', '.json', $_page), json_encode($toStore));
                if(false === $okZip) {
                    Mage::throwException("Could not compress: " . $fileName);
                }
            }

            $zip->close();

            $this->returnBinary($zipName, $response, $zipiName, 'application/zip');

        }catch(Exception $ex) {
            //Directory is empty or something.
            $response->setHttpResponseCode(500)
                ->setBody($ex->getMessage());
        }
    }

    private function _rmFileIfExists($filename) {
        $io = new Varien_Io_File;
        if ($io->fileExists($filename, true))
            $io->rm($filename);
    }

    /**
     * Disable flat catalog and flat category for a given store id **in memory only**.
     *
     * @param int $storeId
     * @return void
     */
    public function disableFlatCatalogAndCategory($storeId) {
        Mage::app()->getStore($storeId)
            ->setConfig(Mage_Catalog_Helper_Category_Flat::XML_PATH_IS_ENABLED_FLAT_CATALOG_CATEGORY, 0)
            ->setConfig(Mage_Catalog_Helper_Product_Flat::XML_PATH_USE_PRODUCT_FLAT, 0);
    }

    /**
     * Compare filenames for sorting.
     *
     * @param $a
     * @param $b
     * @return int
     */
    public function cmpfilenames($a, $b) {
        return strcmp($a->getFilename(), $b->getFilename());
    }

    /**
     * Return sorted pages.
     *
     * @param $path
     * @return array
     */
    public function getPagesToProcess($path) {
        $files = array();
        $iterator = new DirectoryIterator($path);
        foreach ($iterator as $fileInfo) {

            if((preg_match(self::MATCH_PAGE_NAME, $fileInfo->getFilename()) !== 1)) {
                if($fileInfo->getFilename() != "_pagedata.ser")
                    continue;
            }

            $files []= clone $fileInfo;
        }

        usort($files, array($this, 'cmpfilenames'));

        return $files;
    }

    public function countRows($dbName, $dbTableName, $dbColumn = '*') {

        $dbcheck = new SQLite3($dbName);
        $count   = $dbcheck->querySingle('SELECT COUNT(' . $dbColumn . ') FROM ' . $dbTableName . ';');

        return $count;
    }

}