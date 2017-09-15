<?php

class Icommerce_RandMergedCssJsFilename_Model_Design_Package extends Mage_Core_Model_Design_Package
{
    // cache for 12h
    const CACHE_KEY_TIMEOUT = 43200;
    const CACHE_KEY_MERGE_JS  = 'ic_rmcjf_js';
    const CACHE_KEY_MERGE_CSS = 'ic_rmcjf_css';

    protected $_cacheTags = array(Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG, Mage_Core_Model_Config::CACHE_TAG);
    protected $_rmcIsSecure;
    protected $_rmcSkinUrl;
    protected $_rmcMediaUrl;
    protected $_rmcBaseUrl;
    protected $_cacheKeyMergeJs;
    protected $_cacheKeyMergeCss;

    public function __construct()
    {
        if (method_exists(get_parent_class(), '__construct')) {
            parent::__construct();
        }
        $this->_initCacheKeys();
    }

    /**
     * @override
     *
     * @param int|Mage_Core_Model_Store|string $store
     * @return $this
     */
    public function setStore($store)
    {
        parent::setStore($store);

        $secure = null;

        if (is_int($store)) {
            $store = Mage::app()->getStore($store);
        }
        if (is_object($store)) {
            if ($store->isAdmin()) {
                $secure = $store->isAdminUrlSecure();
            } else {
                $secure = $store->isFrontUrlSecure() && Mage::app()->getRequest()->isSecure();
            }
        }
        if (null === $secure) {
            $secure = Mage::app()->getRequest()->isSecure();
        }

        $this->_rmcIsSecure = $secure;
        $this->_rmcSkinUrl  = Mage::getBaseUrl('skin', $this->_rmcIsSecure);
        // we don't use Mage::getBaseUrl('media') as MediaServerArray can affect and we need only single real one as css issues with older IE
        $mainStore = Mage::app()->getStore();
        $this->_rmcMediaUrl = $mainStore ? $mainStore->getBaseUrl('media', $this->_rmcIsSecure) :  Mage::getBaseUrl('media', $this->_rmcIsSecure);
        $this->_rmcBaseUrl = Mage::getBaseUrl('web', $this->_rmcIsSecure);

        /* XXX commented out as older IE's doesn't work without  domain names urls in css
        $this->_rmcSkinUrl  = parse_url($this->_rmcSkinUrl, PHP_URL_PATH);
        $this->_rmcMediaUrl = parse_url($this->_rmcMediaUrl, PHP_URL_PATH);
        $this->_rmcBaseUrl  = parse_url($this->_rmcBaseUrl, PHP_URL_PATH);
        */
        return $this;
    }

    /**
     * Merge specified javascript files and return URL to the merged file on success
     * If cache has been cleared a new random filename is generated.
     *
     * @param $files
     * @return string
     */
    public function getMergedJsUrl($files)
    {
        $this->setStore(Mage::app()->getStore());

        $targetDir = $this->_initMergerDir('js');
        if (!$targetDir) {
            return '';
        }

        $targetFilename = md5(implode(',', $files) . ':' . (int )$this->_rmcIsSecure . ':' . $this->_cacheKeyMergeJs) . '.js';

        // newer Magento has _mergeFiles()
        if (method_exists($this, '_mergeFiles')) {
            $mergeFilesResult = $this->_mergeFiles($files, $targetDir . DS . $targetFilename, false, null, 'js');
        } else {
            $mergeFilesResult = Mage::helper('core')
                    ->mergeFiles($files, $targetDir . DS . $targetFilename, false, null, 'js');
        }

        if ($mergeFilesResult) {
            return Mage::getBaseUrl('media', $this->_rmcIsSecure) . 'js/' . $targetFilename;
        }

        return '';
    }

    /**
     * Merge specified css files and return URL to the merged file on success.
     * If cache has been cleared a new random filename is generated.
     *
     * @param $files
     * @return string
     */
    public function getMergedCssUrl($files)
    {
        $this->setStore(Mage::app()->getStore());

        $mergerDir = $this->_rmcIsSecure ? 'css_secure' : 'css';
        $targetDir = $this->_initMergerDir($mergerDir);
        if (!$targetDir) {
            return '';
        }

        // base hostname & port
        $baseMediaUrl = $this->_rmcMediaUrl;
        $hostname     = parse_url($baseMediaUrl, PHP_URL_HOST);
        $port         = parse_url($baseMediaUrl, PHP_URL_PORT);
        if (false === $port) {
            $port = $this->_rmcIsSecure ? 443 : 80;
        }

        // merge into target file
        $targetFilename = md5(implode(',', $files) . "|{$hostname}|{$port}|" . (int)$this->_rmcIsSecure . ':' . $this->_cacheKeyMergeCss) . '.css';

        // newer Magento has _mergeFiles()
        if (method_exists($this, '_mergeFiles')) {
            $mergeFilesResult = $this->_mergeFiles(
                $files, $targetDir . DS . $targetFilename,
                false,
                array($this, 'beforeMergeCss'),
                'css'
            );
        } else {
            $mergeFilesResult = Mage::helper('core')->mergeFiles(
                $files, $targetDir . DS . $targetFilename,
                false,
                array($this, 'beforeMergeCss'),
                'css'
            );
        }
        if ($mergeFilesResult) {
            return $baseMediaUrl . $mergerDir . '/' . $targetFilename;
        }
        return '';
    }

    protected function _initCacheKeys()
    {
        $app = Mage::app();
        $this->_cacheKeyMergeJs = $app->loadCache(self::CACHE_KEY_MERGE_JS);
        if (!$this->_cacheKeyMergeJs) {
            $this->_cacheKeyMergeJs = (string)microtime(true);
            $app->saveCache($this->_cacheKeyMergeJs, self::CACHE_KEY_MERGE_JS, $this->_cacheTags, self::CACHE_KEY_TIMEOUT);
        }

        $this->_cacheKeyMergeCss = $app->loadCache(self::CACHE_KEY_MERGE_CSS);
        if (!$this->_cacheKeyMergeCss) {
            $this->_cacheKeyMergeCss = (string)microtime(true);
            $app->saveCache($this->_cacheKeyMergeCss, self::CACHE_KEY_MERGE_CSS, $this->_cacheTags, self::CACHE_KEY_TIMEOUT);
        }
    }

    /**
     * @override
     *
     * @param string $uri
     * @return string
     */
    protected function _prepareUrl($uri)
    {
        // check absolute or relative url
        if (!preg_match('/^https?:/i', $uri) && !preg_match('/^\//i', $uri)) {
            $fileDir = '';
            $pathParts = explode(DS, $uri);
            $fileDirParts = explode(DS, $this->_callbackFileDir);

            if ('skin' == $fileDirParts[0]) {
                $baseUrl = $this->_rmcSkinUrl;
                $fileDirParts = array_slice($fileDirParts, 1);
            } elseif ('media' == $fileDirParts[0]) {
                $baseUrl = $this->_rmcMediaUrl;
                $fileDirParts = array_slice($fileDirParts, 1);
            } else {
                $baseUrl = $this->_rmcBaseUrl;
            }

            foreach ($pathParts as $key=>$part) {
                if ($part == '.' || $part == '..') {
                    unset($pathParts[$key]);
                }
                if ($part == '..' && count($fileDirParts)) {
                    $fileDirParts = array_slice($fileDirParts, 0, count($fileDirParts) - 1);
                }
            }

            if (count($fileDirParts)) {
                $fileDir = implode('/', $fileDirParts).'/';
            }

            $uri = $baseUrl . $fileDir . implode('/', $pathParts);
        }
        return $uri;
    }
}
