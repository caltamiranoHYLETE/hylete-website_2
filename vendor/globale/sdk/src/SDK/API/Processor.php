<?php
namespace GlobalE\SDK\API;

use GlobalE\SDK\API;
use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Core;

/**
 * Class Processor
 * @package GlobalE\SDK\API
 * @property string $ObjectResponse
 */
abstract class Processor{

    /**
     * Parameters for sending the API by the URI
     * @var Common\ApiParams
     * @access protected
     */
    protected $ApiParams;
    /**
     * Path to the API service
     * @var string
     * @access protected
     */
    protected $Path;

    /**
     * Full URL to the API service including base and GET parameters
     * @var string
     * @access protected
     */
    protected $Url;

    /**
     * POST parameters for the API service
     * @var array
     * @access protected
     */
    protected $Body;

    /**
     * Flag which indicates if need to use/not use cache
     * @var bool
     * @access protected
     */
    protected $UseCache = true;


    /**
     * The process of API call creation
     * @return bool|mixed|null
     * @access public
     */
    public function processRequest() {

        Core\Profiler::startTimer('API '.$this->getPath());
        Core\Log::log($this->getPath() . ' ' . $this->getUrl() . ' ' . Models\Json::encode($this->getBody()), Core\Log::LEVEL_INFO);
        
        $cacheKey = $this->getCacheKey();
        $response = false;
        if($this->isUseCache()) {
            $response = $this->getFromCache($cacheKey);
        }
        $ExtraLogInfo = ' From Cache ';

        if($response === false) {
            Core\Profiler::startTimer('API, request ' . $this->getPath());
            $response = API\Connector::sendRequest($this->getUrl(),$this->getBody());
            Core\Profiler::endTimer('API, request ' . $this->getPath());
            $ttl = API\Connector::parseHeaders($response);
            $ExtraLogInfo = " With TTL: {$ttl} ";
            $response = $this->decodeResponseData($response);
            if($this->isUseCache()) {
                $this->setToCache($cacheKey, $response, $ttl);
            }
        }

        $this->writeResponseToLog($response, $ExtraLogInfo);
        Core\Profiler::endTimer('API '.$this->getPath());
        return $response;
    }

    /**
     * Log the response from the API service
     * @param mixed $response
     * @param string $ExtraLogInfo
     * @throws \Exception
     * @access protected
     */
    protected function writeResponseToLog($response, $ExtraLogInfo){
        Core\Log::log("Response: " . Models\Json::encode($response).$ExtraLogInfo, Core\Log::LEVEL_DEBUG);
    }

    /**
     * Set all parameters in order to send the API request.
     * @param Common\ApiParams $Params
     * @access protected
     */
    protected function setParams(Common\ApiParams $Params) {

        $this->ApiParams = $Params;
        $this->ApiParams->setPath($this->getPath());
        $this->buildApiUri($Params->getUri());
        $this->buildApiBody($Params->getBody());
    }

    /**
     * Get data from cache
     * @param string $cacheKey
     * @return bool|mixed|null|string
     * @access protected
     */
    protected function getFromCache($cacheKey){
        $response = Core\Cache::get($cacheKey);
        return $response;
    }

    /**
     * Set data from response to cache
     * @param string $cacheKey
     * @param mixed $response
     * @param float $ttl
     * @access protected
     */
    protected function setToCache($cacheKey, $response, $ttl){
        if(!empty($ttl)) {
            Core\Cache::set($cacheKey, $response, $ttl);
        }
    }

    /**
     * Decodes API response to array of commons.
     * @param string $Response
     * @param bool $DecodeBigInt
     * @return object
     * @throws \Exception
     * @access protected
     */
    protected function decodeResponseData($Response,$DecodeBigInt = false){

        // class_exists() works only with full-namespaces.
        // Will throw exception if class won't be found.
        $FullCommonName = "\\GlobalE\\SDK\\API\\Common\\Response\\" . $this->ObjectResponse;
        if(!class_exists($FullCommonName)){
            $Msg = 'Common class ' . $FullCommonName . ' not found.';
            Core\Log::log($Msg, Core\Log::LEVEL_ERROR);
            throw new \Exception($Msg);
        }

        $ObjectResponse = new $FullCommonName;
        $ArrayOfCommons = Models\Json::decodeToCommons($Response,$ObjectResponse,$DecodeBigInt);
        return $ArrayOfCommons;
    }

    /**
     * Build API request URL from baseUrl, Path and GET parameters
     * @param array $Uri
     * @access protected
     */
    protected function buildApiUri(array $Uri) {
        $Url = Core\Settings::get('API.BaseUrl') . $this->getPath() . '?' . $this->formatParameters($Uri);
        $this->setUrl($Url);
    }

    /**
     * Build API request body
     * @param array $Body
     * @access protected
     */
    protected function buildApiBody(array $Body){
        $bodyParams = '';
        if(count($Body)){
            $bodyParams = Models\Json::encode($Body);
        }
        $this->setBody($bodyParams);
    }

    /**
     * Create HTTP query URI, from parameters.
     * @param array $Uri
     * @return string
     * @access protected
     */
    protected function formatParameters(array $Uri){

        $urlString='';
        if(!empty($Uri)){
            foreach($Uri as $key => $value){
                if(is_array($value) || is_object($value)){
                    $Uri[$key] = Models\Json::encode($value);
                }
            }
            $urlString = http_build_query($Uri);
        }
        return $urlString;
    }

    /**
     * Create cache key from all ApiParams
     * @return string
     * @access protected
     */
    protected function getCacheKey(){
        return md5(serialize($this->getApiParams()));
    }

    /**
     * Create HTTP query URI from parameters in unconventional way, using the same GET parameter
     * @param array $Uri
     * @param $FieldName
     * @param bool $JsonEncode
     * @return string
     * @throws \Exception
     * @access protected
     */
    protected function formatUrlArray(array &$Uri, $FieldName, $JsonEncode = false){

        $FieldList = '';
        if(isset($Uri[$FieldName]) && is_array($Uri[$FieldName])){
            $FieldArray =  $Uri[$FieldName];
            foreach ($FieldArray as $Field){
                if($JsonEncode){
                    $Field = Models\Json::encode($Field);
                }
                $FieldList .= "&$FieldName=".urlencode($Field);
            }
            unset($Uri[$FieldName]);
        }
        return $FieldList;
    }

    /**
     * Get Body request parameters array
     * @return array
     * @access protected
     */
    protected function getBody()
    {
        return $this->Body;
    }

    /**
     * Set Body request parameters array
     * @param array $Body
     * @return Processor
     * @access protected
     */
    protected function setBody($Body)
    {
        $this->Body = $Body;
        return $this;
    }

    /**
     * Get URI string
     * @return string
     * @access protected
     */
    protected function getUrl()
    {
        return $this->Url;
    }

    /**
     * Set URI string
     * @param string $Url
     * @return Processor
     * @access protected
     */
    protected function setUrl($Url)
    {
        $this->Url = $Url;
        return $this;
    }


    /**
     * Flag use/not use cache
     * @return boolean
     * @access protected
     */
    public function isUseCache()
    {
        return $this->UseCache;
    }

    /**
     * Set if use/not use cache
     * @param boolean $UseCache
     * @return Processor
     * @access protected
     */
    protected function setUseCache($UseCache)
    {
        $this->UseCache = $UseCache;
        return $this;
    }


    /**
     * Set API parameters
     * @param Common\ApiParams $ApiParams
     * @return Processor
     * @access protected
     */
    protected function setApiParams($ApiParams)
    {
        $this->ApiParams = $ApiParams;
        return $this;
    }

    /**
     * Get API parameters
     * @return Common\ApiParams
     * @access protected
     */
    public function getApiParams()
    {
        return $this->ApiParams;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->Path;
    }

    /**
     * @param string $Path
     */
    protected function setPath($Path)
    {
        $this->Path = $Path;
    }


}