<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
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
 * @package     Vaimo_Hylete
 * @file        Guide.php
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Vitali Rassolov <vitali.rassolov@vaimo.com>
 */

class Vaimo_Hylete_Model_Attribute_Source_Size_Guide extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $_options;

    public function getAllOptions()
    {
        if (!$this->_options) {
            $collectionArray = Mage::getResourceModel('cms/block_collection')
                ->addFieldToFilter('identifier', array('like' => 'product_size_guide_%'))
                ->toArray();

            $values = array_map(function ($e) {
                return array('value' => $e['block_id'], 'label' => $e['identifier']);
            }, $collectionArray['items']);

            $this->_options = $values;

            array_unshift($this->_options, array('value' => 0, 'label' => '-- Please Select --'));
        }

        return $this->_options;
    }
}