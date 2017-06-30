<?php
/**
 * Copyright (c) 2009-2016 Vaimo AB
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
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_MultiOptionFilter
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 */

class Vaimo_MultiOptionFilter_Helper_Cache extends Mage_Core_Helper_Abstract
{
    public function getCacheKeyInfo()
    {
        $category = Mage::registry(Vaimo_MultiOptionFilter_Helper_Registry::CATEGORY);
        $request = Mage::app()->getRequest();

        $result = array(
            'Block_Layer_View',
            $category ? $category->getId() : $request->getRequestUri(),
            Mage::app()->getStore()->getId()
        );

        $customer = Mage::helper('customer')->getCustomer();

        if ($customer) {
            $result[] = $customer->getGroupId();
        }

        if ($filters = $request->getParams()) {
            Vaimo_MultiOptionFilter_Helper_Data::makeFilterCanonical($filters);

            foreach ($filters as $attributeCode => $value) {
                // Magento hooks on an object with key __status__ while performing redirects
                if ($attributeCode == '__status__' || ($attributeCode == 'p' && $value == '1')) {
                    continue;
                }

                $result[] = "$attributeCode-$value";
            }
        }

        return $result;
    }
}