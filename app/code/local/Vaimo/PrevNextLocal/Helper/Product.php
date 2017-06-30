<?php
/**
 * Copyright(c) 2009 - 2014 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered . The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence . A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_PrevNextLocal
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 * @author      Peter Lembke <peter.lembke@vaimo.com>
 */

class Vaimo_PrevNextLocal_Helper_Product extends Mage_Core_Helper_Abstract
{

    public function getScript($productId = 0)
    {
        $settings = $this->getSettings();
        if ($settings['general_enable'] != true) {
            return '';
        }

        $baseDataJson = json_encode($this->getBaseData($settings));
        $scriptCode = $this->getScriptCode($productId, $baseDataJson);
        return $scriptCode;
    }

    public function getSettings() {
        return Mage::helper('prevnextlocal/settings')->getSettings();
    }

    private function getBaseData($settings) {
        $productTypes = $settings['collection_product_types'];
        $productTypesArray = explode(',', $settings['collection_product_types']);
        $productTypesIndex = array();
        foreach ($productTypesArray as $typeId) {
            $productTypesIndex[$typeId] = 1;
        }

        $baseData = array(
            'version' => $settings['information_version'],
            'base_image_url' => $this->getBaseImageUrl($settings),
            'base_url' => Mage::getBaseUrl(),
            'cache_path' => Mage::getStoreConfig('prevnextlocal/images/cache_path'),
            'product_types' => $productTypes,
            'product_types_index' => $productTypesIndex
        );
        return $baseData;
    }

    private function getBaseImageUrl($settings) {
        $baseImageUrl = '';
        if ($settings['scene7_enable'] == 1) {
            if ($settings['scene7_helper'] != false) {
                $baseImageUrl = Mage::helper($settings['scene7_helper'])->getBaseImageUrl();
            }
        }
        if ($baseImageUrl == '') {
            $baseImageUrl = Mage::getBaseUrl('media');
        }
        return $baseImageUrl;
    }

    private function getScriptCode($productId = 0, $baseDataJson = array()) {
        $baseUrl = Mage::getBaseUrl();

        $scriptCode = '<script type="text/javascript">

            function vaimo_prevnextlocal() {
                var $that = this;
                this.getProductData = function ($in) {
                    "use strict";
                    var $default = {"product_id":0, "loop":false, "spread":0 };
                    $in = _Default($default,$in);

                    $in.base_data = _getBaseData($in);
                    $in.collection = _getCollection($in);
                    $in.collection_index = _getCollectionIndex($in);
                    var $wantedProductData = _getProductData($in);

                    if ($wantedProductData.missing_ids.length > 0 && $in.loop === false) {
                        var $request = jQuery.ajax({url: "' . $baseUrl . 'prevnextlocal/data/get?id=" + $in.product_id + "&ids=" + $wantedProductData.missing_ids.join("-") });
                        $request.done(function( html ) {
                            var $response = JSON.parse(html);
                            _storeProductData($response);
                            $that.getProductData({"product_id":$response.product_id, "loop":true, "spread":$in.spread });
                        });
                        return;
                    }

                    $wantedProductData.base_data = $in.base_data;
                    $wantedProductData.index = _makeIndex($wantedProductData.data_array);
                    $wantedProductData.product_id = $in.product_id
                    $wantedProductData.current_position = $wantedProductData.index[$in.product_id];

                    _triggerEvent($wantedProductData);
                };

                this.fetchProductData = function ($in) {
                    "use strict";
                    var $productData = _fetchProductData({"product_id":$in.product_id, "current_time":_getTime() });
                    $productData.base_data = _getBaseData();
                    return $productData;
                };

                var _getBaseData = function ($in) {
                    "use strict";
                    return JSON.parse(localStorage.getItem("vaimo_prevnextlocal_base"));
                };

                var _getCollection = function ($in) {
                    "use strict";
                    return JSON.parse(localStorage.getItem("vaimo_prevnextlocal_collection"));
                };

                var _getCollectionIndex = function ($in) {
                    "use strict";
                    var $collectionIndex = localStorage.getItem("vaimo_prevnextlocal_collection_index");
                    if ($collectionIndex) {
                        return JSON.parse($collectionIndex);
                    }
                    $collectionIndex = {};
                    if (typeof($in.collection) === "undefined" ) {
                        return $collectionIndex;
                    }
                    var arrayLength = $in.collection.length;
                    for (var $i = 0; $i < arrayLength; $i++) {
                        $collectionIndex[$in.collection[$i]] = $i;
                    }
                    localStorage.setItem("vaimo_prevnextlocal_collection_index", JSON.stringify($collectionIndex));
                    return $collectionIndex;
                };

                var _getProductData = function ($in) {
                    "use strict";
                    var $default = {"product_id":0, "base_data": {}, "collection":[], "collection_index":{}, "spread":0 };
                    $in = _Default($default,$in);

                    var $productData = {},
                        $rightResponse = [], $leftResponse = [], $response = [],
                        $missingIds = [],
                        $answer = "true",
                        $message = "The wanted product data",
                        $currentTime = _getTime(),
                        $endSpread, $step, $productId,
                        $position, $collectionLength = $in.collection.length,
                        $startPosition = $in.collection_index[$in.product_id];

                    $endSpread = $in.spread;
                    for ($step = 0; $step <= $endSpread; $step++) {
                        $position = $startPosition - $step;
                        if ($position < 0) {
                            $position = $position % $collectionLength;
                        }
                        if ($position < 0) {
                            $position = $position + $collectionLength;
                        }
                        $productId = $in.collection[$position];
                        $productData = _fetchProductData({"product_id":$productId, "current_time":$currentTime });

                        if (typeof $productData.type_id === "string") {
                            if (typeof $in.base_data.product_types_index[$productData.type_id] === "undefined") {
                                $endSpread++;
                                continue;
                            }
                        }

                        if ($productData.old === true) {
                            $missingIds.push($productId);
                            if ($missingIds.length <= 5) {
                                $endSpread++;
                            }
                            continue;
                        }
                        $leftResponse.push($productData);
                    }

                    $endSpread = $in.spread;
                    for ($step = 0; $step <= $endSpread; $step++) {
                        $position = $startPosition + $step;
                        if ($position >= $collectionLength) {
                            $position = $position % $collectionLength;
                        }
                        $productId = $in.collection[$position];
                        $productData = _fetchProductData({"product_id":$productId, "current_time":$currentTime });

                        if (typeof $productData.type_id === "string") {
                            if (typeof $in.base_data.product_types_index[$productData.type_id] === "undefined") {
                                $endSpread++;
                                continue;
                            }
                        } else {
                            $productData.old = true;
                        }

                        if ($productData.old === true) {
                            $missingIds.push($productId);
                            if ($missingIds.length <= 10) {
                                $endSpread++;
                            }
                            continue;
                        }
                        $rightResponse.push($productData);
                    }

                    if ($missingIds.length > 0) {
                        $answer = "false";
                        $message = "There are missing data"
                    } else {
                        for ($step = $leftResponse.length-1; $step >= 0; $step--) {
                            $response.push($leftResponse[$step]);
                        }
                        for ($step = 1; $step < $rightResponse.length; $step++) {
                            $response.push($rightResponse[$step]);
                        }
                    }
                    $productData = _fetchProductData({"product_id":$in.product_id, "current_time":$currentTime });
                    return {"answer":$answer, "message":$message, "product_data": $productData, "data_array":$response, "missing_ids":$missingIds };
                };

                var _fetchProductData = function ($in) {
                    "use strict";
                    var $default = {"product_id":0, "current_time":0 };
                    $in = _Default($default,$in);

                    if($in.current_time === 0) {
                        $in.current_time = _getTime();
                    }

                    var $key = "vaimo_prevnextlocal_product_" + $in.product_id,
                        $productData = localStorage.getItem($key);
                    if ($productData) {
                        $productData = JSON.parse($productData);
                        if ($in.current_time < $productData.old_when) {
                            return $productData;
                        }
                    }
                    localStorage.removeItem($key);
                    return {"id":$in.product_id, "old":true };
                };

                var _getTime = function () {
                    return (new Date()).getTime() / 1000;
                };

                var _storeProductData = function ($in) {
                    "use strict";
                    var $default = {"product_id":0, "product_data":{} };
                    $in = _Default($default,$in);

                    var $key, $data;
                    for ($key in $in.product_data) {
                        if ($in.product_data.hasOwnProperty($key)) {
                            $data = JSON.stringify($in.product_data[$key]);
                            localStorage.setItem("vaimo_prevnextlocal_product_" + $key, $data);
                        }
                    }
                    return {"answer":"true", "message":"Stored the product data in local storage" };
                };

                var _makeIndex = function ($data) {
                    $data = _ByVal($data);
                    if (typeof($data) === "string") {
                        $data = $data.split(",");
                    }
                    if (Array.isArray($data) === false) {
                        return [];
                    }
                    var $collectionLength = $data.length,
                        $response = {},
                        $id = 0;
                    for (var $i = 0; $i < $collectionLength; $i++) {
                        $id = $data[$i];
                        if (typeof($id) === "object") {
                            $id = $id.id;
                        }
                        $response[$id]=$i;
                    }
                    return $response;
                };

                var _triggerEvent = function ($in) {
                    "use strict";
                    var $event,
                        $detail,
                        $default = {"data_array":[], "base_data":{}, "index":{}, "product_id":0, "current_position":0 };
                    $in = _Default($default,$in);

                    $detail = {
                        "data_array": $in.data_array,
                        "index": $in.index,
                        "product_id": $in.product_id,
                        "current_position": $in.current_position,
                        "base_data": $in.base_data
                    };
                    $event = new CustomEvent("vaimoPrevNextLocalHaveData", {detail: $detail, bubbles: true, cancelable: true });
                    document.dispatchEvent($event);
                    return {"answer":"true", "message":"Data is available. Triggered the event:vaimoPrevNextLocalHaveData" };
                };

                var _Default = function ($object1, $object2) {
                    "use strict";
                    if (typeof $object1 === "object" && typeof $object2 !== "object") {
                        return _ByVal($object1);
                    }
                    if (typeof $object1 !== "object" && typeof $object2 === "object") {
                        return _ByVal($object2);
                    }
                    var $key, $newObject = {};
                    for ($key in $object1) {
                        $newObject[$key] = $object1[$key];
                        if ($object2.hasOwnProperty($key)) {
                            $newObject[$key] = $object2[$key];
                        }
                    }
                    return _ByVal($newObject);
                };

                var _ByVal = function ($object) {
                    "use strict";
                    if (typeof $object !== "object") {
                        return $object;
                    }
                    return JSON.parse(JSON.stringify($object));
                };

            }

            jQuery( document ).ready(function() {
                if(typeof(Storage) == "undefined") {
                    return;
                }
                localStorage.setItem("vaimo_prevnextlocal_base", \'' . $baseDataJson . '\');
                if ('.$productId.' > 0) {
                    localStorage.setItem("vaimo_prevnextlocal_product_id", \'' . $productId . '\');
                }
                $prevNextLocal = new vaimo_prevnextlocal();
                $event = new CustomEvent("vaimoPrevNextLocalReady", {});
                document.dispatchEvent($event);
            });
        </script>';
        return $scriptCode;
    }

    /**
     * This is an example how to use the data. You can create your own event script.
     * @return string
     */
    public function getEventScript() {
        $scriptCode = '<script type="text/javascript">
            function vaimoPrevNextLocalHaveDataHandler($eventData) {
                var $productsData = $eventData.detail;
                console.log("Got the product data");
                console.dir($productsData);
            }
            document.addEventListener("vaimoPrevNextLocalHaveData", vaimoPrevNextLocalHaveDataHandler, false);
            function vaimoPrevNextLocalReadyHandler($eventData) {
                var $productId = localStorage.getItem("vaimo_prevnextlocal_product_id");
                $prevNextLocal.getProductData({"product_id":$productId, "spread":5 });
            }
            document.addEventListener("vaimoPrevNextLocalReady", vaimoPrevNextLocalReadyHandler, false);
        </script>';
        return $scriptCode;
    }

}
