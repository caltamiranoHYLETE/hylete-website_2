<?php
/**
 * Copyright (c) 2009-2013 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
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
 * @package     Vaimo_IntegrationBase
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Allan Paiste <allan.paiste@vaimo.com>
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 *
 * @method int getRowStatus()
 * @method Vaimo_IntegrationBase_Model_Abstract setRowStatus(int $value)
 * @method string getCreatedAt()
 * @method Vaimo_IntegrationBase_Model_Abstract setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Vaimo_IntegrationBase_Model_Abstract setUpdatedAt(string $value)
 */

abstract class Vaimo_IntegrationBase_Model_Abstract extends Mage_Core_Model_Abstract
{
    protected function _beforeSave()
    {
        parent::_beforeSave();

        $currentDate = Mage::getSingleton('core/date')->gmtDate();

        if (!$this->getCreatedAt()) {
            $this->setCreatedAt($currentDate);
        }

        $this->setUpdatedAt($currentDate);
        return $this;
    }

    /**
     * Store array of data into a single entity attribute in serialized form
     *
     * @param string|array $key
     * @param array $data
     * @return Varien_Object
     */
    protected function _setComplexData($key, array $data)
    {
        return $this->setData($key, serialize($data));
    }

    /**
     * Add array of data into a single entity attribute in serialized form
     *
     * @param string|array $key
     * @param array $data
     * @return Varien_Object
     */
    protected function _addComplexData($key, array $data)
    {
        return $this->_setComplexData($key, array_merge($this->_getComplexData($key), $data));
    }

    /**
     * Get stored array from a single entity attribute
     *
     * @param $key
     * @param null|string $valueKey Gives the oportunity to fetch only single value from the serialized data
     * @return array|mixed|string
     */
    protected function _getComplexData($key, $index = null)
    {
        $data = $this->getData($key) ? unserialize($this->getData($key)) : array();

        if ($index) {
            return isset($data[$index]) ? $data[$index] : '';
        } else {
            return $data;
        }
    }

    /**
     * Find differences in arrays by going through each dimension recursively. Note that detecting differences for arrays with
     * mixed keys can lead to difficulties if integer-based index and string-based index have same value.
     *
     * @param   array   $array1    Multidimensional or flat array
     * @param   array   $array2    Multidimensional or flat array
     * @param   bool    $skipStringBasedKeys    Skip changes to string based keys (usefult when hunting only for integer based indexes)
     * @return  array   Differences between the two input arrays
     */
    protected function _arrayDiffRecursive($array1, $array2, $skipStringBasedKeys = false)
    {
        $arrayResult = array();

        if (is_array($array2) xor is_array($array1)) {
            $arrayResult = $array1;
            return $arrayResult;
        }

        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (isset($array2[$key])) {
                    $recursiveDifference = $this->_arrayDiffRecursive($value, $array2[$key], $skipStringBasedKeys);
                } else {
                    $recursiveDifference = $value;
                }

                if (count($recursiveDifference) || !isset($array2[$key])) {
                    $arrayResult[$key] = $recursiveDifference;
                }
            } else {
                if (!$skipStringBasedKeys || !isset($array2[$key]) || is_numeric($key)) {
                    if (!is_array($array2)) {
                        $arrayResult[$key] = $array2;
                    } else {
                        if (is_numeric($key)) {
                            $keyInArray2 = array_search($value, $array2, true);
                            if ($keyInArray2 === false) {
                                $arrayResult[$key] = $value;
                            }
                        } else {
                            if (!isset($array2[$key]) || (isset($array2[$key]) && $value != $array2[$key])) {
                                $arrayResult[$key] = $value;
                            }
                        }
                    }
                }
            }
        }
        return $arrayResult;
    }


    /**
     * Compare old and new raw data and update changed_data. Note that the changed data does not refer if something was
     * added or removed. Note that this works only with pure numeric or pure kvp arrays. mixed arrays will result in
     * incorrect behaviour
     *
     * @param   array   $newRaw New raw data that will be serialized and stored in the database
     * @return  array   Differences between the old and the new raw data
     */
    public function updateRawData($newRaw)
    {
        $oldRaw = $this->getRawData();
        $this->setRawData($newRaw);

        // Calculate changes and make a distinction between added/removed
        $added = $this->_arrayDiffRecursive($newRaw, $oldRaw);
        $removed = $this->_arrayDiffRecursive($oldRaw, $newRaw, true);

        $this->setRawDataAdded($added);
        $this->setRawDataRemoved($removed);

        return $this;
    }

    /**
     * Get raw import data from integrationbase import model
     *
     * @param null $valueKey
     * @return array|mixed|string
     */
    public function getRawData($valueKey = null)
    {
        return $this->_getComplexData('raw_data', $valueKey);
    }

    /**
     * Store raw import data to integrationbase import model
     *
     * @param array $data
     * @return Varien_Object
     */
    public function setRawData(array $data)
    {
        return $this->_setComplexData('raw_data', $data);
    }
}